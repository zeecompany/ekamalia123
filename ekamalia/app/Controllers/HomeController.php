<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController extends Controller
{
    public function index(): void
    {
        $uid = user_id();
        $sections = qa('SELECT * FROM homepage_sections WHERE is_enabled=1 ORDER BY sort_order');
        $sliders = qa('SELECT * FROM sliders WHERE status="active" AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) ORDER BY sort_order LIMIT 8');
        $cats = qa('SELECT c.*, (SELECT COUNT(*) FROM ads a WHERE a.category_id=c.id AND a.status="active") AS ads_count FROM categories c WHERE c.parent_id IS NULL AND c.type IN ("both","ad") AND c.status="active" ORDER BY c.sort_order LIMIT 24');

        $wlSub = $uid ? ' AND (SELECT COUNT(*) FROM wishlists w WHERE w.user_id=' . (int)$uid . ' AND w.item_type="ad" AND w.item_id=a.id) > 0' : '';
        $ads = $this->adsWithExtras(qa(
            'SELECT a.*, c.name AS city_name FROM ads a LEFT JOIN cities c ON c.id=a.city_id
             WHERE a.status="active" AND a.deleted_at IS NULL ' . ($uid ? '' : '') . '
             ORDER BY a.is_featured DESC, a.is_urgent DESC, a.created_at DESC LIMIT 20', []), 'ad');

        $products = $this->adsWithExtras(qa(
            'SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, s.is_verified AS shop_verified, c.name AS city_name,
                    (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS image
             FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN cities c ON c.id=s.city_id
             WHERE p.status="published" AND p.deleted_at IS NULL AND s.status="approved"
             ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 20'), 'product');

        $shops = qa('SELECT s.*, c.name AS city_name FROM shops s LEFT JOIN cities c ON c.id=s.city_id WHERE s.status="approved" AND s.deleted_at IS NULL ORDER BY s.is_featured DESC, s.followers_count DESC LIMIT 12');

        $bizCats = qa('SELECT * FROM categories WHERE type="business" AND status="active" ORDER BY sort_order LIMIT 12');
        $businesses = qa('SELECT b.*, c.name AS cat_name, ct.name AS city_name FROM businesses b
                          LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN cities ct ON ct.id=b.city_id
                          WHERE b.status="approved" ORDER BY b.is_featured DESC, b.views DESC LIMIT 8');

        // bijli summary: current status per feeder (latest unresolved-ish update)
        $feeders = qa('SELECT f.*, c.name AS city_name,
              (SELECT fu.status FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS status,
              (SELECT fu.started_at FROM feeder_updates fu WHERE fu.feeder_id=f.id AND fu.is_resolved=0 ORDER BY fu.created_at DESC LIMIT 1) AS started_at,
              (SELECT fu.expected_at FROM feeder_updates fu WHERE fu.feeder_id=f.id AND fu.is_resolved=0 ORDER BY fu.created_at DESC LIMIT 1) AS expected_at,
              (SELECT fu.message FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS message,
              (SELECT fu.created_at FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS last_update
            FROM feeders f JOIN cities c ON c.id=f.city_id WHERE f.is_active=1 ORDER BY f.sort_order LIMIT 8');

        $deals = $this->adsWithExtras(qa(
            'SELECT p.*, s.name AS shop_name, s.slug AS shop_slug, s.is_verified AS shop_verified, c.name AS city_name,
                    (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS image
             FROM products p JOIN shops s ON s.id=p.shop_id LEFT JOIN cities c ON c.id=s.city_id
             WHERE p.status="published" AND p.sale_price IS NOT NULL AND p.sale_price>0 AND p.sale_price<p.price
             ORDER BY (p.price-p.sale_price)/p.price DESC LIMIT 12'), 'product');

        $news = qa('SELECT * FROM news WHERE status="published" ORDER BY published_at DESC LIMIT 3');
        $testimonials = qa('SELECT * FROM testimonials WHERE status="active" ORDER BY sort_order LIMIT 6');
        $banner = qa('SELECT * FROM advertisements WHERE position="banner" AND status="active" AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) ORDER BY priority DESC LIMIT 1');

        $stats = [
            'ads' => (int)qv('SELECT COUNT(*) FROM ads WHERE status="active"'),
            'products' => (int)qv('SELECT COUNT(*) FROM products WHERE status="published"'),
            'shops' => (int)qv('SELECT COUNT(*) FROM shops WHERE status="approved"'),
            'users' => (int)qv('SELECT COUNT(*) FROM users WHERE status="active"'),
        ];

        $this->view('home/index', compact('sections', 'sliders', 'cats', 'ads', 'products', 'shops', 'bizCats', 'businesses', 'feeders', 'deals', 'news', 'testimonials', 'banner', 'stats') + [
            'seo' => ['title' => setting('seo_title'), 'description' => setting('seo_description')],
        ]);
    }

    /** attach wishlist flags */
    private function adsWithExtras(array $rows, string $type): array
    {
        $uid = user_id();
        if ($uid && $rows) {
            $ids = array_column($rows, 'id');
            $in = implode(',', array_map('intval', $ids));
            $wl = qa("SELECT item_id FROM wishlists WHERE user_id=? AND item_type='$type' AND item_id IN ($in)", [$uid]);
            $wlIds = array_column($wl, 'item_id');
            foreach ($rows as &$r) $r['wishlisted'] = in_array($r['id'], $wlIds);
        }
        return $rows;
    }

    /* ---------- explore kamalia city page ---------- */
    public function explore(): void
    {
        $kamalia = q1('SELECT * FROM cities WHERE slug="kamalia"') ?: q1('SELECT * FROM cities LIMIT 1');
        $businesses = qa('SELECT b.*, c.name AS cat_name FROM businesses b LEFT JOIN categories c ON c.id=b.category_id WHERE b.status="approved" AND b.city_id=? ORDER BY b.is_featured DESC LIMIT 12', [$kamalia['id']]);
        $ads = qa('SELECT a.*, c.name AS city_name FROM ads a LEFT JOIN cities c ON c.id=a.city_id WHERE a.status="active" AND a.city_id=? ORDER BY a.created_at DESC LIMIT 8', [$kamalia['id']]);
        $shops = qa('SELECT s.*, c.name AS city_name FROM shops s LEFT JOIN cities c ON c.id=s.city_id WHERE s.status="approved" AND s.city_id=? LIMIT 6', [$kamalia['id']]);
        $news = qa('SELECT * FROM news WHERE status="published" ORDER BY published_at DESC LIMIT 4');
        $feeders = qa('SELECT f.*, c.name AS city_name,
              (SELECT fu.status FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS status
            FROM feeders f JOIN cities c ON c.id=f.city_id WHERE f.is_active=1 ORDER BY f.sort_order');
        $offCount = count(array_filter($feeders, fn($f) => $f['status'] === 'off'));
        $aboutPage = q1('SELECT * FROM pages WHERE slug="about-us"');
        $this->view('kamalia/index', compact('kamalia', 'businesses', 'ads', 'shops', 'news', 'feeders', 'offCount', 'aboutPage') + [
            'seo' => ['title' => 'Explore Kamalia — City Guide, Businesses & Bijli Updates | eKamalia'],
        ]);
    }

    /* ---------- cities list ---------- */
    public function cities(): void
    {
        $cities = qa('SELECT c.*,
            (SELECT COUNT(*) FROM ads a WHERE a.city_id=c.id AND a.status="active") AS ads_count,
            (SELECT COUNT(*) FROM shops s WHERE s.city_id=c.id AND s.status="approved") AS shops_count,
            (SELECT COUNT(*) FROM businesses b WHERE b.city_id=c.id AND b.status="approved") AS biz_count
            FROM cities c WHERE c.is_active=1 ORDER BY c.is_primary DESC, c.sort_order');
        $this->view('pages/cities', compact('cities') + ['seo' => ['title' => 'All Cities — eKamalia Pakistan']]);
    }

    /* ---------- language switch ---------- */
    public function setLang(): void
    {
        $l = str_input('lang') === 'ur' ? 'ur' : 'en';
        $_SESSION['lang'] = $l;
        if (is_ajax()) json_ok(['lang' => $l]);
        back('/');
    }

    /* ---------- ad click tracking ---------- */
    public function adClick(array $params): void
    {
        $id = (int)$params['id'];
        $ad = q1('SELECT url FROM advertisements WHERE id=? AND status="active"', [$id]);
        if (!$ad) not_found();
        q('UPDATE advertisements SET clicks=clicks+1 WHERE id=?', [$id]);
        redirect($ad['url'] ?: url('/'));
    }
}
