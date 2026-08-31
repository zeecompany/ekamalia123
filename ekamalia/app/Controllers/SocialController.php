<?php
declare(strict_types=1);

namespace App\Controllers;

/** Likes, wishlist, follow, comments, reviews, reports, saved searches, recently viewed */
class SocialController extends Controller
{
    public function toggleWishlist(): void
    {
        $me = require_login();
        $type = str_input('type') === 'ad' ? 'ad' : 'product';
        $id = int_input('id');
        $exists = $type === 'ad'
            ? q1('SELECT id FROM ads WHERE id=?', [$id]) : q1('SELECT id FROM products WHERE id=?', [$id]);
        if (!$exists) json_fail('Item not found', 404);
        $row = q1('SELECT id FROM wishlists WHERE user_id=? AND item_type=? AND item_id=?', [$me['id'], $type, $id]);
        if ($row) { q('DELETE FROM wishlists WHERE id=?', [$row['id']]); json_ok(['added' => false]); }
        q('INSERT IGNORE INTO wishlists (user_id,item_type,item_id,created_at) VALUES (?,?,?,?)', [$me['id'], $type, $id, now()]);
        json_ok(['added' => true]);
    }

    public function toggleLike(): void
    {
        $me = require_login();
        $type = str_input('type');
        $id = int_input('id');
        $allowed = ['product', 'ad', 'comment', 'business'];
        if (!in_array($type, $allowed, true)) json_fail('Invalid like target');
        $table = ['product' => 'products', 'ad' => 'ads', 'comment' => 'comments', 'business' => 'businesses'][$type];
        if (!q1("SELECT id FROM `$table` WHERE id=?", [$id])) json_fail('Not found', 404);
        $row = q1('SELECT id FROM likes WHERE user_id=? AND item_type=? AND item_id=?', [$me['id'], $type, $id]);
        if ($row) {
            q('DELETE FROM likes WHERE id=?', [$row['id']]);
            if ($type !== 'comment') q("UPDATE `$table` SET likes_count=GREATEST(likes_count-1,0) WHERE id=?", [$id]);
            json_ok(['liked' => false, 'count' => (int)qv("SELECT likes_count FROM `$table` WHERE id=?", [$id])]);
        }
        q('INSERT IGNORE INTO likes (user_id,item_type,item_id,created_at) VALUES (?,?,?,?)', [$me['id'], $type, $id, now()]);
        if ($type !== 'comment') q("UPDATE `$table` SET likes_count=likes_count+1 WHERE id=?", [$id]);
        json_ok(['liked' => true, 'count' => (int)qv("SELECT likes_count FROM `$table` WHERE id=?", [$id])]);
    }

    public function toggleFollow(): void
    {
        $me = require_login();
        $shopId = int_input('shop');
        $shop = q1('SELECT id,user_id,name FROM shops WHERE id=? AND status="approved"', [$shopId]);
        if (!$shop) json_fail('Shop not found', 404);
        $row = q1('SELECT id FROM shop_followers WHERE shop_id=? AND user_id=?', [$shopId, $me['id']]);
        if ($row) {
            q('DELETE FROM shop_followers WHERE id=?', [$row['id']]);
            q('UPDATE shops SET followers_count=GREATEST(followers_count-1,0) WHERE id=?', [$shopId]);
            json_ok(['following' => false]);
        }
        q('INSERT IGNORE INTO shop_followers (shop_id,user_id,created_at) VALUES (?,?,?)', [$shopId, $me['id'], now()]);
        q('UPDATE shops SET followers_count=followers_count+1 WHERE id=?', [$shopId]);
        notify((int)$shop['user_id'], 'New follower 🎉', $me['name'] . ' started following ' . $shop['name'] . '.', 'shop', '/shop-info');
        json_ok(['following' => true]);
    }

    /* ---------------- comments ---------------- */
    public function commentStore(): void
    {
        $me = require_login();
        if (setting('comments_enabled') !== '1') json_fail('Comments are currently disabled.');
        $type = str_input('item_type');
        $id = int_input('item_id');
        $body = trim(str_input('body', '', 2000));
        $parent = int_input('parent_id') ?: null;
        if (!in_array($type, ['product', 'ad', 'business', 'news'], true)) json_fail('Invalid target');
        if (mb_strlen($body) < 2) json_fail('Comment is too short.');
        if (rate_limited('comment', $me['id'], 20, 600)) json_fail('Slow down a little 🙂');
        q('INSERT INTO comments (user_id,item_type,item_id,parent_id,body,created_at) VALUES (?,?,?,?,?,?)', [$me['id'], $type, $id, $parent, $body, now()]);
        if ($parent) {
            $parentRow = q1('SELECT user_id FROM comments WHERE id=?', [$parent]);
            if ($parentRow && (int)$parentRow['user_id'] !== (int)$me['id']) notify((int)$parentRow['user_id'], 'New reply to your comment', $me['name'] . ': ' . mb_substr($body, 0, 100), 'comment', current_path());
        }
        $table = ['product' => 'products', 'ad' => 'ads', 'business' => 'businesses', 'news' => 'news'][$type];
        q("UPDATE `$table` SET comments_count=COALESCE(comments_count,0)+1 WHERE id=?", [$id]);
        rate_hit('comment', (string)$me['id']);
        json_ok(['message' => 'Comment posted!']);
    }

    public function commentDelete(): void
    {
        $me = require_login();
        $c = q1('SELECT * FROM comments WHERE id=?', [int_input('id')]);
        if (!$c) json_fail('Not found', 404);
        if ((int)$c['user_id'] !== (int)$me['id'] && $me['role'] !== 'admin') json_fail('Unauthorized', 403);
        q('UPDATE comments SET status="deleted" WHERE id=?', [$c['id']]);
        json_ok(['message' => 'Comment deleted.']);
    }

    /* ---------------- reviews ---------------- */
    public function reviewStore(): void
    {
        $me = require_login();
        $type = str_input('item_type');
        $id = int_input('item_id');
        $rating = max(1, min(5, int_input('rating', 5)));
        $body = trim(str_input('body', '', 2000));
        if (!in_array($type, ['product', 'shop', 'business'], true)) json_fail('Invalid target');
        if (mb_strlen($body) < 5) json_fail('Please write a little more about your experience.');

        $verified = 0; $orderId = null;
        if ($type === 'product') {
            $orderId = qv('SELECT o.id FROM orders o JOIN order_items oi ON oi.order_id=o.id
                           WHERE o.user_id=? AND oi.product_id=? AND o.status IN ("delivered","completed") LIMIT 1', [$me['id'], $id]);
            $verified = $orderId ? 1 : 0;
        }
        $already = q1('SELECT id FROM reviews WHERE user_id=? AND item_type=? AND item_id=?', [$me['id'], $type, $id]);
        if ($already) json_fail('You have already reviewed this.');
        q('INSERT INTO reviews (user_id,item_type,item_id,order_id,rating,title,body,status,is_verified_purchase,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)',
            [$me['id'], $type, $id, $orderId, $rating, str_input('title', '', 190) ?: null, $body,
             setting('reviews_require_approval') === '1' ? 'pending' : 'approved', $verified, now()]);
        self::recalcRating($type, $id);
        if ($type === 'shop') {
            $owner = qv('SELECT user_id FROM shops WHERE id=?', [$id]);
            if ($owner) notify((int)$owner, 'New review received ⭐', $me['name'] . ' rated you ' . $rating . '/5.', 'review', '/seller/reviews');
        }
        json_ok(['message' => 'Review submitted. Shukriya!']);
    }

    public static function recalcRating(string $type, int $id): void
    {
        $table = ['product' => 'products', 'shop' => 'shops', 'business' => 'businesses'][$type];
        $avg = (float)qv("SELECT COALESCE(AVG(rating),0) FROM reviews WHERE item_type=? AND item_id=? AND status='approved'", [$type, $id]);
        $cnt = (int)qv("SELECT COUNT(*) FROM reviews WHERE item_type=? AND item_id=? AND status='approved'", [$type, $id]);
        q("UPDATE `$table` SET rating_avg=?, rating_count=? WHERE id=?", [round($avg, 2), $cnt, $id]);
    }

    public function reviewReply(): void
    {
        $me = require_login();
        $r = q1('SELECT * FROM reviews WHERE id=?', [int_input('id')]);
        if (!$r) json_fail('Not found', 404);
        // permission: seller of shop or admin
        $allowed = $me['role'] === 'admin';
        if ($r['item_type'] === 'shop' && !$allowed) {
            $myShop = my_shop();
            $allowed = $myShop && (int)$myShop['id'] === (int)$r['item_id'];
        }
        if ($r['item_type'] === 'product' && !$allowed) {
            $myShop = my_shop();
            $allowed = $myShop && $myShop && qv('SELECT shop_id FROM products WHERE id=?', [$r['item_id']]) == $myShop['id'];
        }
        if (!$allowed) json_fail('Unauthorized', 403);
        $reply = trim(str_input('reply', '', 1000));
        if ($reply === '') json_fail('Write a reply first.');
        q('UPDATE reviews SET seller_reply=?, replied_at=? WHERE id=?', [$reply, now(), $r['id']]);
        notify((int)$r['user_id'], 'Seller replied to your review', mb_substr($reply, 0, 120), 'review', current_path());
        json_ok(['message' => 'Reply posted.']);
    }

    /* ---------------- reports ---------------- */
    public function report(): void
    {
        $me = auth();
        $type = str_input('item_type');
        $id = int_input('item_id');
        if (!in_array($type, ['ad', 'product', 'shop', 'user', 'comment', 'business', 'message'], true)) json_fail('Invalid');
        $reason = str_input('reason', 'other', 60);
        q('INSERT INTO reports (reporter_id,item_type,item_id,reason,details,created_at) VALUES (?,?,?,?,?,?)',
            [$me ? $me['id'] : null, $type, $id, $reason, str_input('details', '', 500), now()]);
        notify_admins('New report: ' . $type, 'Reason: ' . $reason . ' — review the moderation queue.', 'moderation', '/admin/reports');
        json_ok(['message' => 'Report received. Our team will review it.']);
    }

    /* ---------------- saved searches ---------------- */
    public function saveSearch(): void
    {
        $me = require_login();
        $name = str_input('name', '', 160) ?: 'My search';
        $params = json_encode(array_intersect_key($_GET, array_flip(['q', 'city', 'min_price', 'max_price', 'cat'])));
        q('INSERT INTO saved_searches (user_id,name,params,created_at) VALUES (?,?,?,?)', [$me['id'], $name, $params, now()]);
        if (is_ajax()) json_ok(['message' => 'Search saved! We will notify you about new matches.']);
        flash('success', 'Search saved!');
        back('/search');
    }

    /* ---------------- recently viewed (privacy-aware, session-scoped) ---------------- */
    public function recentProduct(array $params): void
    {
        self::rememberRecent('product', (int)$params['id']);
        json_ok();
    }
    public function recentAd(array $params): void
    {
        self::rememberRecent('ad', (int)$params['id']);
        json_ok();
    }
    public function recentShop(array $params): void
    {
        self::rememberRecent('shop', (int)$params['id']);
        json_ok();
    }
    private static function rememberRecent(string $type, int $id): void
    {
        $list = $_SESSION['recently_viewed'] ?? [];
        $list = array_values(array_filter($list, fn($x) => !($x['type'] === $type && $x['id'] === $id)));
        array_unshift($list, ['type' => $type, 'id' => $id, 'at' => time()]);
        $_SESSION['recently_viewed'] = array_slice($list, 0, 12);
    }

    public static function recentlyViewed(): array
    {
        $list = $_SESSION['recently_viewed'] ?? [];
        $out = [];
        foreach ($list as $item) {
            if ($item['type'] === 'product') {
                $r = q1('SELECT p.name,p.slug,(SELECT image FROM product_images pi WHERE pi.product_id=p.id LIMIT 1) AS image, p.sale_price, p.price FROM products p WHERE p.id=? AND p.status="published"', [$item['id']]);
                if ($r) $out[] = ['type' => 'Product', 'title' => $r['name'], 'url' => url('/product/' . $r['slug']), 'image' => img_or($r['image']), 'price' => money($r['sale_price'] ?: $r['price'])];
            } elseif ($item['type'] === 'ad') {
                $r = q1('SELECT title,slug FROM ads WHERE id=? AND status="active"', [$item['id']]);
                if ($r) $out[] = ['type' => 'Ad', 'title' => $r['title'], 'url' => url('/ad/' . $r['slug']), 'image' => asset('img/placeholder.svg'), 'price' => ''];
            } elseif ($item['type'] === 'shop') {
                $r = q1('SELECT name,slug,logo FROM shops WHERE id=? AND status="approved"', [$item['id']]);
                if ($r) $out[] = ['type' => 'Shop', 'title' => $r['name'], 'url' => url('/shop/' . $r['slug']), 'image' => img_or($r['logo'], 'assets/img/shop-logo.svg'), 'price' => ''];
            }
        }
        return $out;
    }
}
