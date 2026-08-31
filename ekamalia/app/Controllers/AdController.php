<?php
declare(strict_types=1);

namespace App\Controllers;

class AdController extends Controller
{
    /* ---------------- public browse ---------------- */
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
        $this->listing(["a.category_id IN ($in)" => []], $cat);
    }

    private function listing(array $where, ?array $cat = null): void
    {
        $uid = user_id();
        $conds = ['a.status="active"', 'a.deleted_at IS NULL'];
        $params = [];
        foreach ($where as $w => $p) { $conds[] = $w; $params = array_merge($params, $p); }

        $q = str_input('q', '', 120);
        if ($q !== '') { $conds[] = '(a.title LIKE ? OR a.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if ($citySlug = str_input('city', '', 140)) {
            $c = q1('SELECT id FROM cities WHERE slug=?', [$citySlug]);
            if ($c) { $conds[] = 'a.city_id=?'; $params[] = $c['id']; }
        }
        if ($sub = int_input('sub')) { $conds[] = 'a.subcategory_id=?'; $params[] = $sub; }
        if ($min = input('min_price')) { $conds[] = 'a.price>=?'; $params[] = (float)$min; }
        if ($max = input('max_price')) { $conds[] = 'a.price<=?'; $params[] = (float)$max; }
        if ($cond = str_input('condition')) { $conds[] = 'a.condition=?'; $params[] = $cond; }
        if (input('delivery')) $conds[] = 'a.delivery_available=1';
        if (input('negotiable')) $conds[] = 'a.negotiable=1';
        if (input('featured')) $conds[] = 'a.is_featured=1';

        $sort = str_input('sort', 'newest');
        $order = match ($sort) {
            'price_low' => 'a.price ASC',
            'price_high' => 'a.price DESC',
            'popular' => 'a.views DESC',
            default => 'a.is_featured DESC, a.is_urgent DESC, a.created_at DESC',
        };

        $perPage = per_page();
        $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM ads a WHERE ' . implode(' AND ', $conds), $params);
        $ads = qa('SELECT a.*, c.name AS city_name, u.name AS user_name, u.is_verified AS user_verified,
                    (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id ORDER BY sort_order LIMIT 1) AS image
                  FROM ads a LEFT JOIN cities c ON c.id=a.city_id JOIN users u ON u.id=a.user_id
                  WHERE ' . implode(' AND ', $conds) . " ORDER BY $order LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);

        if ($q !== '') log_search($q, $total);

        $subcats = $cat ? qa('SELECT c.*, (SELECT COUNT(*) FROM ads a WHERE a.category_id=c.id AND a.status="active") AS cnt FROM categories c WHERE c.parent_id=? ORDER BY c.sort_order', [$cat['id']]) : [];
        $this->view('ads/index', [
            'ads' => $ads, 'total' => $total, 'perPage' => $perPage, 'cat' => $cat, 'subcats' => $subcats,
            'seo' => ['title' => ($cat ? $cat['name'] . ' in Kamalia' : t('ad.title')) . ' — Buy & Sell | eKamalia'],
        ]);
    }

    public function show(array $params): void
    {
        $ad = q1('SELECT a.*, c.name AS city_name, c.slug AS city_slug, u.name AS user_name, u.is_verified AS user_verified, u.created_at AS user_since, u.id AS owner_id
                  FROM ads a LEFT JOIN cities c ON c.id=a.city_id JOIN users u ON u.id=a.user_id
                  WHERE a.slug=? AND a.deleted_at IS NULL', [$params['slug']]);
        if (!$ad || !in_array($ad['status'], ['active', 'sold', 'expired'], true)) not_found();
        $me = auth();
        if ($ad['status'] === 'active' && (!$me || $me['id'] != $ad['user_id'])) {
            q('UPDATE ads SET views=views+1 WHERE id=?', [$ad['id']]);
        }
        $images = qa('SELECT image FROM ad_images WHERE ad_id=? ORDER BY sort_order', [$ad['id']]);
        $cat = $ad['category_id'] ? q1('SELECT * FROM categories WHERE id=?', [$ad['category_id']]) : null;
        $subcat = $ad['subcategory_id'] ? q1('SELECT * FROM categories WHERE id=?', [$ad['subcategory_id']]) : null;
        $sellerAds = (int)qv('SELECT COUNT(*) FROM ads WHERE user_id=? AND status="active"', [$ad['user_id']]);
        $similar = qa('SELECT a.*, c.name AS city_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id ORDER BY sort_order LIMIT 1) AS image
                       FROM ads a LEFT JOIN cities c ON c.id=a.city_id
                       WHERE a.status="active" AND a.id<>? AND (a.category_id=? OR a.city_id=?) ORDER BY a.created_at DESC LIMIT 8',
                       [$ad['id'], $ad['category_id'], $ad['city_id']]);
        $wishlisted = $me && qv('SELECT COUNT(*) FROM wishlists WHERE user_id=? AND item_type="ad" AND item_id=?', [$me['id'], $ad['id']]) > 0;
        $liked = $me && qv('SELECT COUNT(*) FROM likes WHERE user_id=? AND item_type="ad" AND item_id=?', [$me['id'], $ad['id']]) > 0;
        $comments = $this->commentsFor('ad', $ad['id']);

        $this->view('ads/show', [
            'ad' => $ad, 'images' => $images, 'cat' => $cat, 'subcat' => $subcat, 'sellerAds' => $sellerAds,
            'similar' => $similar, 'wishlisted' => $wishlisted, 'liked' => $liked, 'comments' => $comments,
            'reportModal' => true, 'pageType' => 'ad', 'pageId' => $ad['id'],
            'seo' => [
                'title' => $ad['title'] . ' — ' . money($ad['price']) . ' | eKamalia',
                'description' => mb_substr(strip_tags((string)$ad['description']), 0, 160),
                'og_image' => $images ? upload_url($images[0]['image']) : asset('img/og-image.jpg'),
            ],
            'jsonLd' => $this->adJsonLd($ad, $images),
        ]);
    }

    private function adJsonLd(array $ad, array $images): string
    {
        $img = $images ? upload_url($images[0]['image']) : asset('img/placeholder.svg');
        $json = json_encode([
            '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $ad['title'],
            'image' => [$img], 'description' => mb_substr(strip_tags((string)$ad['description']), 0, 300),
            'offers' => ['@type' => 'Offer', 'price' => (float)$ad['price'], 'priceCurrency' => 'PKR',
                         'availability' => $ad['status'] === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut'],
        ], JSON_UNESCAPED_SLASHES);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /* ---------------- create / edit ---------------- */
    public function create(): void
    {
        require_login();
        $cats = qa('SELECT * FROM categories WHERE parent_id IS NULL AND type IN ("both","ad") AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $myCount = (int)qv('SELECT COUNT(*) FROM ads WHERE user_id=? AND status IN ("active","pending","paused")', [user_id()]);
        $limit = (int)setting('free_ads_limit', 10);
        $this->view('ads/form', ['ad' => null, 'cats' => $cats, 'cities' => $cities, 'myCount' => $myCount, 'limit' => $limit,
            'seo' => ['title' => 'Post a Free Ad — eKamalia']]);
    }

    public function store(): void
    {
        require_login();
        if (rate_limited('ad_post', client_ip(), 12, 3600)) { flash('danger', 'Too many ads posted recently. Please wait.'); back('/ads/create'); }
        $this->saveAd(null);
    }

    public function edit(array $params): void
    {
        $ad = q1('SELECT * FROM ads WHERE id=? AND user_id=? AND deleted_at IS NULL', [(int)$params['id'], user_id()]);
        if (!$ad) not_found();
        $cats = qa('SELECT * FROM categories WHERE parent_id IS NULL AND type IN ("both","ad") AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $images = qa('SELECT * FROM ad_images WHERE ad_id=? ORDER BY sort_order', [$ad['id']]);
        $this->view('ads/form', ['ad' => $ad, 'images' => $images, 'cats' => $cats, 'cities' => $cities,
            'seo' => ['title' => 'Edit Ad — eKamalia']]);
    }

    public function update(array $params): void
    {
        $ad = q1('SELECT * FROM ads WHERE id=? AND user_id=? AND deleted_at IS NULL', [(int)$params['id'], user_id()]);
        if (!$ad) not_found();
        $this->saveAd($ad);
    }

    private function saveAd(?array $ad): void
    {
        $me = auth();
        $title = str_input('title', '', 190);
        $desc = str_input('description', '', 8000);
        $catId = int_input('category_id');
        $subId = int_input('subcategory_id') ?: null;
        $price = float_input('price');
        $cityId = int_input('city_id') ?: null;
        $errors = [];
        if (mb_strlen($title) < 8) $errors[] = 'Title must be at least 8 characters.';
        if (mb_strlen($desc) < 20) $errors[] = 'Please write a proper description (20+ characters).';
        if (!$catId || !q1('SELECT id FROM categories WHERE id=?', [$catId])) $errors[] = 'Please select a valid category.';
        if ($price < 0 || $price > 9999999999) $errors[] = 'Enter a valid price.';
        if (!$errors) {
            // upload images
            $newImages = [];
            if (!empty($_FILES['images'])) {
                $files = $_FILES['images'];
                $count = is_array($files['name']) ? count($files['name']) : 0;
                for ($i = 0; $i < min($count, 8); $i++) {
                    if (empty($files['name'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $tmp = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    $_FILES['__one'] = $tmp;
                    try { $rel = upload_image('__one', 'ads', (int)setting('max_upload_mb', 5)); if ($rel) $newImages[] = $rel; }
                    catch (\RuntimeException $ex) { $errors[] = 'Image: ' . $ex->getMessage(); }
                }
                unset($_FILES['__one']);
            }
        }
        if ($errors) { stash_old(); flash('danger', implode(' ', $errors)); back($ad ? '/ads/' . $ad['id'] . '/edit' : '/ads/create'); }

        $autoApprove = setting('ads_require_approval') !== '1';
        $data = [
            mb_substr($title, 0, 190), $desc, $catId, $subId, str_input('condition', 'used'),
            $price, input('negotiable') ? 1 : 0, $cityId, str_input('area', '', 140),
            preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null,
            preg_replace('/\D/', '', str_input('whatsapp', '', 20)) ?: null,
            str_input('seller_type', 'individual'), input('delivery') ? 1 : 0,
            filter_var(str_input('video_url'), FILTER_VALIDATE_URL) ?: null,
            str_input('tags', '', 250),
            $autoApprove ? 'active' : 'pending',
            date('Y-m-d H:i:s', time() + 86400 * 30),
        ];
        if ($ad) {
            $data[] = $ad['id'];
            q('UPDATE ads SET title=?,description=?,category_id=?,subcategory_id=?,`condition`=?,price=?,negotiable=?,city_id=?,area=?,phone=?,whatsapp=?,seller_type=?,delivery_available=?,video_url=?,tags=?,status=? ,expires_at=? WHERE id=?', $data);
            $adId = $ad['id'];
            // replace images if new uploaded
            if ($newImages) {
                foreach (qa('SELECT image FROM ad_images WHERE ad_id=?', [$adId]) as $im) delete_upload($im['image']);
                q('DELETE FROM ad_images WHERE ad_id=?', [$adId]);
            }
        } else {
            $slug = unique_slug('ads', $title);
            $dataAll = array_merge([$me['id'], $slug], $data);
            q('INSERT INTO ads (user_id,slug,title,description,category_id,subcategory_id,`condition`,price,negotiable,city_id,area,phone,whatsapp,seller_type,delivery_available,video_url,tags,status,expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $dataAll);
            $adId = last_id();
        }
        foreach ($newImages as $i => $img) q('INSERT INTO ad_images (ad_id,image,sort_order) VALUES (?,?,?)', [$adId, $img, $i]);
        clear_old();
        if (!$autoApprove) {
            notify_admins('New ad pending approval', $title . ' is waiting for review.', 'moderation', '/admin/ads?status=pending');
            flash('success', 'Ad submitted! It will appear after a quick review.');
        } else {
            flash('success', 'Ad published successfully!');
        }
        audit_log($ad ? 'ad.updated' : 'ad.created', 'ad', $adId, $title);
        redirect('/dashboard/my-ads');
    }

    /* ---------------- my ads actions ---------------- */
    public function myAds(): void
    {
        require_login();
        $ads = qa('SELECT a.*, c.name AS city_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id ORDER BY sort_order LIMIT 1) AS image
                   FROM ads a LEFT JOIN cities c ON c.id=a.city_id WHERE a.user_id=? AND a.deleted_at IS NULL ORDER BY a.created_at DESC', [user_id()]);
        $this->view('dashboard/my-ads', ['ads' => $ads, 'seo' => ['title' => 'My Ads — eKamalia']]);
    }

    public function adAction(array $params): void
    {
        require_login();
        $ad = q1('SELECT * FROM ads WHERE id=? AND user_id=? AND deleted_at IS NULL', [(int)$params['id'], user_id()]);
        if (!$ad) { if (is_ajax()) json_fail('Not found', 404); not_found(); }
        $action = str_input('action', $params['action'] ?? '');
        switch ($action) {
            case 'pause': q('UPDATE ads SET status="paused" WHERE id=?', [$ad['id']]); $msg = 'Ad paused.'; break;
            case 'resume': q('UPDATE ads SET status="active" WHERE id=?', [$ad['id']]); $msg = 'Ad is live again.'; break;
            case 'sold': q('UPDATE ads SET status="sold" WHERE id=?', [$ad['id']]); $msg = 'Marked as sold. Congrats!'; break;
            case 'available': q('UPDATE ads SET status="active" WHERE id=?', [$ad['id']]); $msg = 'Marked as available.'; break;
            case 'renew': q('UPDATE ads SET status="active", expires_at=?, created_at=? WHERE id=?', [date('Y-m-d H:i:s', time() + 86400 * 30), now(), $ad['id']]); $msg = 'Ad renewed for 30 more days.'; break;
            case 'delete':
                q('UPDATE ads SET deleted_at=? WHERE id=?', [now(), $ad['id']]);
                foreach (qa('SELECT image FROM ad_images WHERE ad_id=?', [$ad['id']]) as $im) delete_upload($im['image']);
                $msg = 'Ad deleted.';
                audit_log('ad.deleted', 'ad', $ad['id'], $ad['title']);
                break;
            case 'promote':
                q('INSERT INTO promotion_requests (user_id,type,item_id,status,created_at) VALUES (?,?,?,?,?)', [user_id(), 'ad_featured', $ad['id'], 'pending', now()]);
                notify_admins('Promotion request', user()['name'] . ' requested promotion for ad: ' . $ad['title'], 'moderation', '/admin/promotions');
                $msg = 'Promotion request sent! Our team will contact you.';
                break;
            default: if (is_ajax()) json_fail('Unknown action'); flash('danger', 'Unknown action.'); back('/dashboard/my-ads');
        }
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/dashboard/my-ads');
    }

    /* ---------------- comments (shared) ---------------- */
    public function commentsFor(string $type, int $id): array
    {
        $rows = qa('SELECT cm.*, u.name AS user_name, u.avatar, u.is_verified AS user_verified
                    FROM comments cm JOIN users u ON u.id=cm.user_id
                    WHERE cm.item_type=? AND cm.item_id=? AND cm.parent_id IS NULL AND cm.status="visible"
                    ORDER BY cm.created_at DESC LIMIT 30', [$type, $id]);
        foreach ($rows as &$r) {
            $r['replies'] = qa('SELECT cm.*, u.name AS user_name, u.avatar FROM comments cm JOIN users u ON u.id=cm.user_id
                                WHERE cm.parent_id=? AND cm.status="visible" ORDER BY cm.created_at', [$r['id']]);
            $r['liked'] = user_id() && qv('SELECT COUNT(*) FROM likes WHERE user_id=? AND item_type="comment" AND item_id=?', [user_id(), $r['id']]) > 0;
        }
        return $rows;
    }
}
