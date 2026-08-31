<?php
declare(strict_types=1);

namespace App\Controllers;

/** Small JSON endpoints used by frontend JS */
class ApiController extends Controller
{
    public function suggest(): void
    {
        $term = str_input('q', '', 80);
        if (mb_strlen($term) < 2) json_ok(['results' => []]);
        $like = "%$term%";
        $out = [];
        foreach (qa('SELECT name,slug,(SELECT image FROM product_images pi WHERE pi.product_id=p.id LIMIT 1) AS image FROM products p WHERE p.status="published" AND p.deleted_at IS NULL AND name LIKE ? LIMIT 5', [$like]) as $r) {
            $out[] = ['title' => $r['name'], 'subtitle' => 'Product', 'image' => img_or($r['image']), 'url' => url('/product/' . $r['slug']), 'type' => 'Product'];
        }
        foreach (qa('SELECT title,slug FROM ads WHERE status="active" AND deleted_at IS NULL AND title LIKE ? LIMIT 5', [$like]) as $r) {
            $out[] = ['title' => $r['title'], 'subtitle' => 'Classified Ad', 'image' => asset('img/placeholder.svg'), 'url' => url('/ad/' . $r['slug']), 'type' => 'Ad'];
        }
        foreach (qa('SELECT name,slug,logo FROM shops WHERE status="approved" AND deleted_at IS NULL AND name LIKE ? LIMIT 3', [$like]) as $r) {
            $out[] = ['title' => $r['name'], 'subtitle' => 'Shop', 'image' => img_or($r['logo'], 'assets/img/shop-logo.svg'), 'url' => url('/shop/' . $r['slug']), 'type' => 'Shop'];
        }
        foreach (qa('SELECT name,slug,logo FROM businesses WHERE status="approved" AND name LIKE ? LIMIT 3', [$like]) as $r) {
            $out[] = ['title' => $r['name'], 'subtitle' => 'Business', 'image' => img_or($r['logo'], 'assets/img/biz-logo.svg'), 'url' => url('/business/' . $r['slug']), 'type' => 'Business'];
        }
        json_ok(['results' => $out]);
    }

    public function subcategories(): void
    {
        $cat = int_input('category_id');
        $subs = qa('SELECT id,name FROM categories WHERE parent_id=? AND status="active" ORDER BY sort_order', [$cat]);
        json_ok(['subcategories' => $subs]);
    }

    public function areas(): void
    {
        $city = int_input('city_id');
        json_ok(['areas' => qa('SELECT name FROM areas WHERE city_id=? ORDER BY name', [$city])]);
    }
}
