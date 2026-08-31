<?php
declare(strict_types=1);

namespace App\Controllers;

class ShopController extends Controller
{
    /* ---------------- public ---------------- */
    public function index(): void
    {
        $q = str_input('q', '', 120);
        $citySlug = str_input('city', '', 140);
        $conds = ['s.status="approved"', 's.deleted_at IS NULL']; $params = [];
        if ($q !== '') { $conds[] = 's.name LIKE ?'; $params[] = "%$q%"; }
        if ($citySlug) { $c = q1('SELECT id FROM cities WHERE slug=?', [$citySlug]); if ($c) { $conds[] = 's.city_id=?'; $params[] = $c['id']; } }
        $perPage = per_page(); $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM shops s WHERE ' . implode(' AND ', $conds), $params);
        $shops = qa('SELECT s.*, c.name AS city_name FROM shops s LEFT JOIN cities c ON c.id=s.city_id
                     WHERE ' . implode(' AND ', $conds) . ' ORDER BY s.is_featured DESC, s.followers_count DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
        $uid = user_id();
        foreach ($shops as &$s) {
            $s['following'] = $uid && qv('SELECT COUNT(*) FROM shop_followers WHERE shop_id=? AND user_id=?', [$s['id'], $uid]) > 0;
        }
        $this->view('shops/index', ['shops' => $shops, 'total' => $total, 'perPage' => $perPage,
            'seo' => ['title' => 'Online Shops in Kamalia — ' . setting('site_name', 'eKamalia')]]);
    }

    public function show(array $params): void
    {
        $shop = q1('SELECT s.*, c.name AS city_name FROM shops s LEFT JOIN cities c ON c.id=s.city_id
                    WHERE s.slug=? AND s.status="approved" AND s.deleted_at IS NULL', [$params['slug']]);
        if (!$shop) not_found();
        q('UPDATE shops SET views=views+1 WHERE id=?', [$shop['id']]);
        $me = auth();
        $conds = ['p.shop_id=?', 'p.status="published"', 'p.deleted_at IS NULL']; $params2 = [$shop['id']];
        if ($q = str_input('q', '', 100)) { $conds[] = 'p.name LIKE ?'; $params2[] = "%$q%"; }
        if ($catId = int_input('cat')) { $conds[] = '(p.category_id=? OR p.subcategory_id=?)'; $params2[] = $catId; $params2[] = $catId; }
        $sort = str_input('sort', 'newest');
        $order = match ($sort) { 'price_low' => 'COALESCE(NULLIF(p.sale_price,0),p.price) ASC', 'price_high' => 'COALESCE(NULLIF(p.sale_price,0),p.price) DESC', 'popular' => 'p.views DESC', default => 'p.is_featured DESC, p.created_at DESC' };
        $perPage = 20; $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM products p WHERE ' . implode(' AND ', $conds), $params2);
        $products = qa('SELECT p.*, (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image, ct.name AS city_name
                        FROM products p JOIN shops s2 ON s2.id=p.shop_id LEFT JOIN cities ct ON ct.id=s2.city_id
                        WHERE ' . implode(' AND ', $conds) . " ORDER BY $order LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params2);
        $shopCats = qa('SELECT DISTINCT c.id, c.name, c.slug FROM products p JOIN categories c ON c.id IN (p.category_id, p.subcategory_id)
                        WHERE p.shop_id=? AND p.status="published" AND c.id IS NOT NULL', [$shop['id']]);
        $reviews = qa('SELECT r.*, u.name AS user_name, u.avatar FROM reviews r JOIN users u ON u.id=r.user_id
                       WHERE r.item_type="shop" AND r.item_id=? AND r.status="approved" ORDER BY r.created_at DESC LIMIT 12', [$shop['id']]);
        $coupons = qa('SELECT * FROM coupons WHERE shop_id=? AND status="active" AND (expires_at IS NULL OR expires_at>NOW())', [$shop['id']]);
        $following = $me && qv('SELECT COUNT(*) FROM shop_followers WHERE shop_id=? AND user_id=?', [$shop['id'], $me['id']]) > 0;
        $isOwner = $me && $me['id'] === $shop['user_id'];

        $this->view('shops/show', [
            'shop' => $shop, 'products' => $products, 'total' => $total, 'perPage' => $perPage, 'shopCats' => $shopCats,
            'reviews' => $reviews, 'coupons' => $coupons, 'following' => $following, 'isOwner' => $isOwner,
            'reportModal' => true, 'pageType' => 'shop', 'pageId' => $shop['id'],
            'seo' => ['title' => $shop['name'] . ' — Online Shop in Kamalia | eKamalia', 'description' => mb_substr(strip_tags((string)$shop['description']), 0, 160)],
        ]);
    }

    /* ---------------- registration ---------------- */
    public function create(): void
    {
        require_login();
        $shop = q1('SELECT * FROM shops WHERE user_id=? AND deleted_at IS NULL ORDER BY id DESC LIMIT 1', [user_id()]);
        $cats = qa('SELECT * FROM categories WHERE type IN ("business","both") AND parent_id IS NULL AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $packages = qa('SELECT * FROM packages WHERE status="active" ORDER BY sort_order');
        $this->view('shops/create', ['shop' => $shop, 'cats' => $cats, 'cities' => $cities, 'packages' => $packages,
            'seo' => ['title' => 'Create Your Free Shop — eKamalia']]);
    }

    public function store(): void
    {
        require_login();
        if (q1('SELECT id FROM shops WHERE user_id=? AND deleted_at IS NULL', [user_id()])) {
            flash('info', 'You already have a shop.');
            redirect('/seller');
        }
        $name = str_input('name', '', 160);
        if (mb_strlen($name) < 3) { stash_old(); flash('danger', 'Please enter your shop name.'); back('/shops/create'); }
        if (rate_limited('shop_reg', client_ip(), 5, 3600)) { flash('danger', 'Too many attempts.'); back('/shops/create'); }
        rate_hit('shop_reg', client_ip());
        $slug = unique_slug('shops', $name);
        q('INSERT INTO shops (user_id,name,slug,owner_name,email,phone,whatsapp,cnic,address,city_id,area,description,logo,cover,category_id,business_hours,delivery_available,delivery_fee,bank_info,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            user_id(), $name, $slug, str_input('owner_name', '', 140), strtolower(str_input('email', '', 190)) ?: null,
            preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null,
            preg_replace('/\D/', '', str_input('whatsapp', '', 20)) ?: null,
            str_input('cnic', '', 20) ?: null, str_input('address', '', 255), int_input('city_id') ?: null,
            str_input('area', '', 140), str_input('description', '', 4000),
            $this->tryUpload('logo', 'shops'), $this->tryUpload('cover', 'shops'),
            int_input('category_id') ?: null, str_input('business_hours', '', 190),
            input('delivery_available') ? 1 : 0, float_input('delivery_fee'),
            str_input('bank_info', '', 1000), setting('shops_require_approval') === '1' ? 'pending' : 'approved', now(),
        ]);
        $shopId = last_id();
        if (setting('shops_require_approval') === '1') {
            notify_admins('New shop pending approval', $name . ' submitted for approval.', 'shop', '/admin/shops?status=pending');
            flash('success', 'Shop submitted! You will be notified once it is approved (usually within 24 hours).');
        } else {
            q('UPDATE users SET last_activity=? WHERE id=?', [now(), user_id()]);
            notify(user_id(), 'Shop is live! 🎉', $name . ' has been approved and is now live on eKamalia.', 'shop', '/seller');
            flash('success', 'Your shop is live! Start adding products.');
        }
        audit_log('shop.created', 'shop', $shopId, $name);
        clear_old();
        redirect($shopId && setting('shops_require_approval') !== '1' ? '/seller' : '/shops/create');
    }

    private function tryUpload(string $field, string $dir): ?string
    {
        try { return upload_image($field, $dir, (int)setting('max_upload_mb', 5)); }
        catch (\RuntimeException $e) { flash('warning', $field . ': ' . $e->getMessage()); return null; }
    }

    /* ---------------- chat entry ---------------- */
    public function startChat(array $params): void
    {
        $shop = q1('SELECT * FROM shops WHERE id=? AND status="approved"', [(int)$params['id']]);
        if (!$shop) not_found();
        $me = require_login();
        $thread = q1('SELECT * FROM message_threads WHERE buyer_id=? AND seller_id=? AND shop_id=? ORDER BY id DESC LIMIT 1', [$me['id'], $shop['user_id'], $shop['id']]);
        if (!$thread) {
            q('INSERT INTO message_threads (buyer_id,seller_id,shop_id,created_at) VALUES (?,?,?,?)', [$me['id'], $shop['user_id'], $shop['id'], now()]);
            $threadId = last_id();
        } else $threadId = $thread['id'];
        redirect('/chat/' . $threadId);
    }
}
