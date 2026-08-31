<?php
declare(strict_types=1);

namespace App\Controllers;

class BusinessController extends Controller
{
    public function index(): void
    {
        $q = str_input('q', '', 120);
        $citySlug = str_input('city', '', 140);
        $catSlug = str_input('cat', '', 140);
        $conds = ['b.status="approved"', 'b.deleted_at IS NULL']; $params = [];
        if ($q !== '') { $conds[] = '(b.name LIKE ? OR b.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if ($citySlug) { $c = q1('SELECT id FROM cities WHERE slug=?', [$citySlug]); if ($c) { $conds[] = 'b.city_id=?'; $params[] = $c['id']; } }
        if ($catSlug) { $c = q1('SELECT id FROM categories WHERE slug=?', [$catSlug]); if ($c) { $conds[] = 'b.category_id=?'; $params[] = $c['id']; } }
        $perPage = per_page(); $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM businesses b WHERE ' . implode(' AND ', $conds), $params);
        $businesses = qa('SELECT b.*, c.name AS cat_name, ct.name AS city_name FROM businesses b
                          LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN cities ct ON ct.id=b.city_id
                          WHERE ' . implode(' AND ', $conds) . ' ORDER BY b.is_featured DESC, b.name LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
        $cats = qa('SELECT c.*, (SELECT COUNT(*) FROM businesses b WHERE b.category_id=c.id AND b.status="approved") AS cnt
                    FROM categories c WHERE c.type="business" AND c.status="active" ORDER BY c.sort_order');
        $this->view('businesses/index', ['businesses' => $businesses, 'cats' => $cats, 'total' => $total, 'perPage' => $perPage,
            'seo' => ['title' => 'Kamalia Business Directory — Restaurants, Shops, Services | eKamalia']]);
    }

    public function show(array $params): void
    {
        $b = q1('SELECT b.*, c.name AS cat_name, ct.name AS city_name FROM businesses b
                 LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN cities ct ON ct.id=b.city_id
                 WHERE b.slug=? AND b.status="approved" AND b.deleted_at IS NULL', [$params['slug']]);
        if (!$b) not_found();
        q('UPDATE businesses SET views=views+1 WHERE id=?', [$b['id']]);
        $photos = qa('SELECT image FROM business_photos WHERE business_id=?', [$b['id']]);
        $reviews = qa('SELECT r.*, u.name AS user_name, u.avatar FROM reviews r JOIN users u ON u.id=r.user_id
                       WHERE r.item_type="business" AND r.item_id=? AND r.status="approved" ORDER BY r.created_at DESC LIMIT 30', [$b['id']]);
        $similar = qa('SELECT b.*, c.name AS cat_name FROM businesses b LEFT JOIN categories c ON c.id=b.category_id
                       WHERE b.status="approved" AND b.id<>? AND (b.category_id=? OR b.city_id=?) LIMIT 6', [$b['id'], $b['category_id'], $b['city_id']]);
        $this->view('businesses/show', ['b' => $b, 'photos' => $photos, 'reviews' => $reviews, 'similar' => $similar,
            'reportModal' => true,
            'seo' => ['title' => $b['name'] . ' — ' . ($b['cat_name'] ?: 'Business') . ' in Kamalia | eKamalia', 'description' => mb_substr(strip_tags((string)$b['description']), 0, 160)],
            'jsonLd' => '<script type="application/ld+json">' . json_encode([
                '@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => $b['name'],
                'telephone' => $b['phone'], 'address' => ['@type' => 'PostalAddress', 'streetAddress' => $b['address'], 'addressLocality' => $b['city_name'] ?? 'Kamalia', 'addressCountry' => 'PK'],
                'aggregateRating' => $b['rating_count'] > 0 ? ['@type' => 'AggregateRating', 'ratingValue' => (float)$b['rating_avg'], 'reviewCount' => (int)$b['rating_count']] : null,
            ], JSON_UNESCAPED_SLASHES) . '</script>']);
    }

    public function create(): void
    {
        require_login();
        $cats = qa('SELECT * FROM categories WHERE type="business" AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $mine = qa('SELECT * FROM businesses WHERE user_id=? AND deleted_at IS NULL ORDER BY id DESC', [user_id()]);
        $this->view('businesses/create', ['cats' => $cats, 'cities' => $cities, 'mine' => $mine,
            'seo' => ['title' => 'List Your Business Free — eKamalia Directory']]);
    }

    public function store(): void
    {
        require_login();
        $name = str_input('name', '', 190);
        if (mb_strlen($name) < 3) { stash_old(); flash('danger', 'Please enter the business name.'); back('/businesses/create'); }
        if (rate_limited('biz_post', client_ip(), 8, 3600)) { flash('danger', 'Too many submissions. Please wait.'); back('/businesses/create'); }
        try {
            $logo = upload_image('logo', 'businesses', 5);
            $cover = upload_image('cover', 'businesses', 5);
        } catch (\RuntimeException $e) { stash_old(); flash('danger', $e->getMessage()); back('/businesses/create'); }
        q('INSERT INTO businesses (user_id,name,slug,category_id,description,logo,cover,phone,whatsapp,email,website,address,city_id,area,opening_hours,facebook,instagram,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            user_id(), $name, unique_slug('businesses', $name), int_input('category_id') ?: null,
            str_input('description', '', 4000), $logo ?? null, $cover ?? null,
            preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null,
            preg_replace('/\D/', '', str_input('whatsapp', '', 20)) ?: null,
            strtolower(str_input('email', '', 190)) ?: null,
            filter_var(str_input('website'), FILTER_VALIDATE_URL) ?: null,
            str_input('address', '', 255), int_input('city_id') ?: null, str_input('area', '', 140),
            str_input('opening_hours', '', 190), str_input('facebook', '', 190) ?: null, str_input('instagram', '', 190) ?: null,
            setting('shops_require_approval') === '1' ? 'pending' : 'approved', now(),
        ]);
        $bizId = last_id();
        if (setting('shops_require_approval') === '1') notify_admins('New business listing', $name . ' submitted for review.', 'moderation', '/admin/businesses?status=pending');
        clear_old();
        flash('success', setting('shops_require_approval') === '1' ? 'Business submitted for approval. Shukriya!' : 'Business published!');
        audit_log('business.created', 'business', $bizId, $name);
        redirect('/businesses');
    }
}
