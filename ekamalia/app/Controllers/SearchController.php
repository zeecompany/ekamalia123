<?php
declare(strict_types=1);

namespace App\Controllers;

/** Global search across products, ads, shops, businesses */
class SearchController extends Controller
{
    public function index(): void
    {
        $q = str_input('q', '', 120);
        $type = str_input('type', 'all'); // all|products|ads|shops|businesses
        $citySlug = str_input('city', '', 140);
        $min = input('min_price'); $max = input('max_price');
        $catId = int_input('cat') ?: null;
        $sort = str_input('sort', 'newest');
        $cityId = null;
        if ($citySlug) { $c = q1('SELECT * FROM cities WHERE slug=?', [$citySlug]); $cityId = $c['id'] ?? null; }

        $products = []; $ads = []; $shops = []; $businesses = []; $total = 0;
        if (mb_strlen($q) >= 1 || $cityId || $catId || $min || $max) {
            $like = "%$q%";
            if (in_array($type, ['all', 'products'], true)) {
                $conds = ['p.status="published"', 'p.deleted_at IS NULL', 's.status="approved"', '(p.name LIKE ? OR p.short_description LIKE ? OR p.tags LIKE ?)'];
                $params = [$like, $like, $like];
                if ($cityId) { $conds[] = 's.city_id=?'; $params[] = $cityId; }
                if ($catId) { $conds[] = '(p.category_id=? OR p.subcategory_id=?)'; $params[] = $catId; $params[] = $catId; }
                if ($min) { $conds[] = 'COALESCE(NULLIF(p.sale_price,0),p.price)>=?'; $params[] = (float)$min; }
                if ($max) { $conds[] = 'COALESCE(NULLIF(p.sale_price,0),p.price)<=?'; $params[] = (float)$max; }
                $products = qa('SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, s.is_verified AS shop_verified, c.name AS city_name,
                                (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                                FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN cities c ON c.id=s.city_id
                                WHERE ' . implode(' AND ', $conds) . ' ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 40', $params);
                $total += count($products);
            }
            if (in_array($type, ['all', 'ads'], true)) {
                $conds = ['a.status="active"', 'a.deleted_at IS NULL', '(a.title LIKE ? OR a.description LIKE ? OR a.tags LIKE ?)'];
                $params = [$like, $like, $like];
                if ($cityId) { $conds[] = 'a.city_id=?'; $params[] = $cityId; }
                if ($catId) { $conds[] = '(a.category_id=? OR a.subcategory_id=?)'; $params[] = $catId; $params[] = $catId; }
                if ($min) { $conds[] = 'a.price>=?'; $params[] = (float)$min; }
                if ($max) { $conds[] = 'a.price<=?'; $params[] = (float)$max; }
                $ads = qa('SELECT a.*, c.name AS city_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id ORDER BY sort_order LIMIT 1) AS image
                           FROM ads a LEFT JOIN cities c ON c.id=a.city_id
                           WHERE ' . implode(' AND ', $conds) . ' ORDER BY a.is_featured DESC, a.created_at DESC LIMIT 40', $params);
                $total += count($ads);
            }
            if (in_array($type, ['all', 'shops'], true)) {
                $conds = ['s.status="approved"', 's.deleted_at IS NULL', '(s.name LIKE ? OR s.description LIKE ?)'];
                $params = [$like, $like];
                if ($cityId) { $conds[] = 's.city_id=?'; $params[] = $cityId; }
                $shops = qa('SELECT s.*, c.name AS city_name FROM shops s LEFT JOIN cities c ON c.id=s.city_id WHERE ' . implode(' AND ', $conds) . ' LIMIT 12', $params);
                $total += count($shops);
            }
            if (in_array($type, ['all', 'businesses'], true)) {
                $conds = ['b.status="approved"', 'b.deleted_at IS NULL', '(b.name LIKE ? OR b.description LIKE ?)'];
                $params = [$like, $like];
                if ($cityId) { $conds[] = 'b.city_id=?'; $params[] = $cityId; }
                $businesses = qa('SELECT b.*, c.name AS cat_name, ct.name AS city_name FROM businesses b LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN cities ct ON ct.id=b.city_id WHERE ' . implode(' AND ', $conds) . ' LIMIT 12', $params);
                $total += count($businesses);
            }
            log_search($q, $total);
        }
        $cats = qa('SELECT * FROM categories WHERE parent_id IS NULL AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $this->view('search/index', ['q' => $q, 'type' => $type, 'products' => $products, 'ads' => $ads, 'shops' => $shops, 'businesses' => $businesses,
            'cats' => $cats, 'cities' => $cities, 'total' => $total,
            'seo' => ['title' => ($q ? "Search: $q" : 'Search') . ' — eKamalia']]);
    }
}
