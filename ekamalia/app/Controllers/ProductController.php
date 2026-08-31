<?php
declare(strict_types=1);

namespace App\Controllers;

class ProductController extends Controller
{
    public function index(): void
    {
        $this->listing([]);
    }

    public function byCategory(array $params): void
    {
        $cat = q1('SELECT * FROM categories WHERE slug=? AND status="active"', [$params['slug']]);
        if (!$cat) not_found();
        $catIds = [$cat['id']];
        foreach (qa('SELECT id FROM categories WHERE parent_id=?', [$cat['id']]) as $s) $catIds[] = (int)$s['id'];
        $in = implode(',', $catIds);
        $this->listing(["p.category_id IN ($in)" => []], $cat);
    }

    public function deals(): void
    {
        $this->listing(['p.sale_price IS NOT NULL' => [], 'p.sale_price > 0' => []], null, true);
    }

    private function listing(array $where, ?array $cat = null, bool $dealsOnly = false): void
    {
        $conds = ['p.status="published"', 'p.deleted_at IS NULL', 's.status="approved"'];
        $params = [];
        foreach ($where as $w => $p) { $conds[] = $w; }
        if ($q = str_input('q', '', 120)) { $conds[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.sku=?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = $q; }
        if ($citySlug = str_input('city', '', 140)) {
            $c = q1('SELECT id FROM cities WHERE slug=?', [$citySlug]);
            if ($c) { $conds[] = 's.city_id=?'; $params[] = $c['id']; }
        }
        if ($brand = int_input('brand')) { $conds[] = 'p.brand_id=?'; $params[] = $brand; }
        if ($shop = int_input('shop')) { $conds[] = 'p.shop_id=?'; $params[] = $shop; }
        if ($min = input('min_price')) { $conds[] = 'COALESCE(NULLIF(p.sale_price,0),p.price)>=?'; $params[] = (float)$min; }
        if ($max = input('max_price')) { $conds[] = 'COALESCE(NULLIF(p.sale_price,0),p.price)<=?'; $params[] = (float)$max; }
        if (input('delivery')) $conds[] = '1=1';
        if ($dealsOnly) $conds[] = 'p.sale_price < p.price';

        $sort = str_input('sort', 'newest');
        $order = match ($sort) {
            'price_low' => 'COALESCE(NULLIF(p.sale_price,0),p.price) ASC',
            'price_high' => 'COALESCE(NULLIF(p.sale_price,0),p.price) DESC',
            'popular' => 'p.views DESC',
            'rating' => 'p.rating_avg DESC, p.rating_count DESC',
            default => 'p.is_featured DESC, p.created_at DESC',
        };
        $perPage = per_page();
        $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM products p JOIN shops s ON s.id=p.shop_id WHERE ' . implode(' AND ', $conds), $params);
        $products = qa('SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, s.is_verified AS shop_verified, c.name AS city_name, b.name AS brand_name,
                        (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                      FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN cities c ON c.id=s.city_id LEFT JOIN brands b ON b.id=p.brand_id
                      WHERE ' . implode(' AND ', $conds) . " ORDER BY $order LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);
        if ($q !== '') log_search($q, $total);
        $brands = qa('SELECT * FROM brands WHERE status="active" ORDER BY name');
        $subcats = $cat ? qa('SELECT c.*, (SELECT COUNT(*) FROM products pp WHERE pp.category_id=c.id AND pp.status="published") AS cnt FROM categories c WHERE c.parent_id=?', [$cat['id']]) : [];
        $this->view('products/index', ['products' => $products, 'total' => $total, 'perPage' => $perPage, 'cat' => $cat, 'subcats' => $subcats, 'brands' => $brands, 'dealsOnly' => $dealsOnly,
            'seo' => ['title' => ($dealsOnly ? 'Deals & Offers' : ($cat ? $cat['name'] : 'Products') . ' in Kamalia') . ' — eKamalia']]);
    }

    public function show(array $params): void
    {
        $p = q1('SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, s.is_verified AS shop_verified, s.delivery_fee, s.id AS sid,
                        s.owner_name, s.phone AS shop_phone, s.whatsapp AS shop_whatsapp,
                        c.name AS city_name, b.name AS brand_name
                 FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN cities c ON c.id=s.city_id LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.slug=? AND p.deleted_at IS NULL', [$params['slug']]);
        if (!$p || !in_array($p['status'], ['published', 'out_of_stock'], true)) not_found();
        $me = auth();
        if (!$me || $me['id'] !== $p['sid']) q('UPDATE products SET views=views+1 WHERE id=?', [$p['id']]);

        $images = qa('SELECT image FROM product_images WHERE product_id=? ORDER BY sort_order', [$p['id']]);
        $variants = qa('SELECT * FROM product_variants WHERE product_id=?', [$p['id']]);
        $reviews = qa('SELECT r.*, u.name AS user_name, u.avatar FROM reviews r JOIN users u ON u.id=r.user_id
                       WHERE r.item_type="product" AND r.item_id=? AND r.status="approved" ORDER BY r.created_at DESC LIMIT 20', [$p['id']]);
        $shopReviews = qa('SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id=r.user_id
                       WHERE r.item_type="shop" AND r.item_id=? AND r.status="approved" ORDER BY r.created_at DESC LIMIT 5', [$p['sid']]);
        $related = qa('SELECT p2.*, s.name AS shop_name, s.slug AS shop_slug, (SELECT image FROM product_images pi WHERE pi.product_id=p2.id ORDER BY sort_order LIMIT 1) AS image
                       FROM products p2 JOIN shops s ON s.id=p2.shop_id
                       WHERE p2.status="published" AND p2.id<>? AND (p2.category_id=? OR p2.shop_id=?) ORDER BY p2.created_at DESC LIMIT 10', [$p['id'], $p['category_id'], $p['sid']]);
        $wishlisted = $me && qv('SELECT COUNT(*) FROM wishlists WHERE user_id=? AND item_type="product" AND item_id=?', [$me['id'], $p['id']]) > 0;
        $liked = $me && qv('SELECT COUNT(*) FROM likes WHERE user_id=? AND item_type="product" AND item_id=?', [$me['id'], $p['id']]) > 0;
        $comments = (new AdController())->commentsFor('product', $p['id']);
        $compareList = $_SESSION['compare'] ?? [];
        $inCompare = in_array($p['id'], $compareList);

        $this->view('products/show', [
            'p' => $p, 'images' => $images, 'variants' => $variants, 'reviews' => $reviews, 'shopReviews' => $shopReviews,
            'related' => $related, 'wishlisted' => $wishlisted, 'liked' => $liked, 'comments' => $comments, 'inCompare' => $inCompare,
            'reportModal' => true, 'pageType' => 'product', 'pageId' => $p['id'],
            'seo' => [
                'title' => $p['name'] . ' — ' . money($p['sale_price'] ?: $p['price']) . ' | eKamalia',
                'description' => mb_substr(strip_tags((string)($p['short_description'] ?: $p['description'])), 0, 160),
                'og_image' => $images ? upload_url($images[0]['image']) : asset('img/og-image.jpg'),
            ],
        ]);
    }

    /* ---------------- compare ---------------- */
    public function compareAdd(): void
    {
        $pid = int_input('product_id');
        if (!$pid) json_fail('Missing product');
        $_SESSION['compare'] ??= [];
        if (!in_array($pid, $_SESSION['compare'])) $_SESSION['compare'][] = $pid;
        $_SESSION['compare'] = array_slice($_SESSION['compare'], -4);
        json_ok(['count' => count($_SESSION['compare']), 'message' => 'Added to comparison']);
    }

    public function compare(): void
    {
        $ids = array_map('intval', $_SESSION['compare'] ?? []);
        $products = [];
        if ($ids) {
            $in = implode(',', $ids);
            $products = qa("SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, b.name AS brand_name, c.name AS cat_name,
                            (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                            FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN brands b ON b.id=p.brand_id LEFT JOIN categories c ON c.id=p.category_id
                            WHERE p.id IN ($in) AND p.status='published'");
        }
        $this->view('products/compare', ['products' => $products, 'seo' => ['title' => 'Compare Products — eKamalia']]);
    }

    public function compareRemove(): void
    {
        $pid = int_input('product_id');
        $_SESSION['compare'] = array_values(array_diff($_SESSION['compare'] ?? [], [$pid]));
        if (is_ajax()) json_ok(['message' => 'Removed']);
        back('/compare');
    }
}
