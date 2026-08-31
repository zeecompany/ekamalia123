<?php
declare(strict_types=1);

namespace App\Controllers;

class ChatController extends Controller
{
    private const SELLER_PERMS = ['seller', 'admin'];

    private function myThreads(): array
    {
        $me = auth();
        return qa('SELECT t.*, ub.name AS buyer_name, ub.avatar AS buyer_avatar, us.name AS seller_name, us.avatar AS seller_avatar,
                          s.name AS shop_name, s.logo AS shop_logo,
                          (SELECT m.body FROM messages m WHERE m.thread_id=t.id ORDER BY m.created_at DESC LIMIT 1) AS last_msg,
                          (SELECT m.type FROM messages m WHERE m.thread_id=t.id ORDER BY m.created_at DESC LIMIT 1) AS last_type
                   FROM message_threads t
                   JOIN users ub ON ub.id=t.buyer_id JOIN users us ON us.id=t.seller_id
                   LEFT JOIN shops s ON s.id=t.shop_id
                   WHERE (t.buyer_id=? OR t.seller_id=?) AND t.status="active"
                   ORDER BY COALESCE(t.last_message_at, t.created_at) DESC LIMIT 100', [$me['id'], $me['id']]);
    }

    public function index(): void
    {
        $me = require_login();
        q('UPDATE users SET last_activity=? WHERE id=?', [now(), $me['id']]);
        $threads = $this->myThreads();
        $this->view('chat/index', ['threads' => $threads, 'seo' => ['title' => t('nav.messages') . ' — eKamalia']]);
    }

    private function loadThread(int $id): array
    {
        $me = auth();
        $t = q1('SELECT t.*, ub.name AS buyer_name, us.name AS seller_name, s.name AS shop_name, s.logo AS shop_logo, s.slug AS shop_slug
                 FROM message_threads t JOIN users ub ON ub.id=t.buyer_id JOIN users us ON us.id=t.seller_id
                 LEFT JOIN shops s ON s.id=t.shop_id WHERE t.id=?', [$id]);
        if (!$t || ((int)$t['buyer_id'] !== (int)$me['id'] && (int)$t['seller_id'] !== (int)$me['id'] && $me['role'] !== 'admin')) not_found();
        return $t;
    }

    public function show(array $params): void
    {
        $me = require_login();
        $t = $this->loadThread((int)$params['id']);
        // mark read (messages I received)
        if ((int)$t['buyer_id'] === (int)$me['id']) q('UPDATE message_threads SET buyer_unread=0 WHERE id=?', [$t['id']]);
        else q('UPDATE message_threads SET seller_unread=0 WHERE id=?', [$t['id']]);
        q('UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_id<>?', [$t['id'], $me['id']]);
        $messages = qa('SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.thread_id=? ORDER BY m.created_at LIMIT 200', [$t['id']]);
        $otherId = (int)$t['buyer_id'] === (int)$me['id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'];
        $other = q1('SELECT name,avatar,last_activity FROM users WHERE id=?', [$otherId]);
        $blocked = qv('SELECT COUNT(*) FROM blocked_users WHERE user_id=? AND blocked_id=?', [$me['id'], $otherId]) > 0;
        $threads = $this->myThreads();
        $refProduct = $t['product_id'] ? q1('SELECT name,slug,(SELECT image FROM product_images WHERE product_id=p.id LIMIT 1) AS image, sale_price, price FROM products p WHERE id=?', [$t['product_id']]) : null;
        $refAd = $t['ad_id'] ? q1('SELECT title,slug FROM ads WHERE id=?', [$t['ad_id']]) : null;

        $this->view('chat/show', ['threads' => $threads, 'thread' => $t, 'messages' => $messages, 'other' => $other, 'blocked' => $blocked,
            'refProduct' => $refProduct, 'refAd' => $refAd, 'pageType' => '', 'pageId' => 0,
            'seo' => ['title' => 'Chat — eKamalia']]);
    }

    public function send(array $params): void
    {
        $me = require_login();
        $t = $this->loadThread((int)$params['id']);
        $otherId = (int)$t['buyer_id'] === (int)$me['id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'];
        if (qv('SELECT COUNT(*) FROM blocked_users WHERE user_id=? AND blocked_id=?', [$otherId, $me['id']])) {
            json_fail('You cannot message this user.');
        }
        $body = trim(str_input('body', '', 3000));
        $image = null;
        try { $image = upload_image('image', 'chat', (int)setting('max_upload_mb', 5)); } catch (\RuntimeException $e) { json_fail($e->getMessage()); }
        if ($body === '' && !$image && !input('ref_id')) json_fail('Write a message first.');
        $type = $image ? 'image' : (input('ref_type') === 'product' ? 'product' : (input('ref_type') === 'order' ? 'order' : 'text'));
        q('INSERT INTO messages (thread_id,sender_id,type,body,image,ref_id,created_at) VALUES (?,?,?,?,?,?,?)',
            [$t['id'], $me['id'], $type, $body ?: null, $image, int_input('ref_id') ?: null, now()]);
        $last = $body ?: ($image ? '📷 Photo' : 'Attachment');
        q('UPDATE message_threads SET last_message=?, last_message_at=?, buyer_unread = IF(buyer_id=?, buyer_unread+0, buyer_unread+1), seller_unread = IF(seller_id=?, seller_unread+0, seller_unread+1) WHERE id=?',
            [$last, now(), $me['id'], $me['id'], $t['id']]);
        q('UPDATE users SET last_activity=? WHERE id=?', [now(), $me['id']]);
        notify($otherId, 'New message from ' . $me['name'], mb_substr($last, 0, 120), 'message', '/chat/' . $t['id']);
        json_ok(['id' => last_id()]);
    }

    public function poll(array $params): void
    {
        $me = require_login();
        $t = $this->loadThread((int)$params['id']);
        $after = int_input('after');
        $messages = qa('SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.thread_id=? AND m.id>? ORDER BY m.created_at', [$t['id'], $after]);
        $otherId = (int)$t['buyer_id'] === (int)$me['id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'];
        $other = q1('SELECT last_activity FROM users WHERE id=?', [$otherId]);
        $online = $other && $other['last_activity'] && (time() - strtotime($other['last_activity'])) < 180;
        q('UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_id<>?', [$t['id'], $me['id']]);
        json_ok(['messages' => array_map(function ($m) use ($me) {
            return ['id' => (int)$m['id'], 'me' => (int)$m['sender_id'] === (int)$me['id'], 'type' => $m['type'],
                    'body' => $m['body'], 'image' => $m['image'] ? upload_url($m['image']) : null,
                    'time' => date('h:i A', strtotime($m['created_at'])), 'read' => (bool)$m['is_read']];
        }, $messages), 'online' => $online]);
    }

    public function block(array $params): void
    {
        $me = require_login();
        $t = $this->loadThread((int)$params['id']);
        $otherId = (int)$t['buyer_id'] === (int)$me['id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'];
        q('INSERT IGNORE INTO blocked_users (user_id,blocked_id,created_at) VALUES (?,?,?)', [$me['id'], $otherId, now()]);
        json_ok(['message' => 'User blocked.']);
    }

    public function reportThread(array $params): void
    {
        $me = require_login();
        $t = $this->loadThread((int)$params['id']);
        q('INSERT INTO reports (reporter_id,item_type,item_id,reason,details,created_at) VALUES (?,?,?,?,?,?)',
            [$me['id'], 'message', $t['id'], str_input('reason', 'harassment', 60), str_input('details', '', 500), now()]);
        notify_admins('Chat reported', 'Thread #' . $t['id'] . ' reported by ' . $me['name'], 'moderation', '/admin/reports');
        json_ok(['message' => 'Reported to moderation team.']);
    }

    /* start thread about a product or ad */
    public function start(): void
    {
        $me = require_login();
        $type = str_input('type'); // product | ad | shop
        $id = int_input('id');
        $first = trim(str_input('body', '', 500));
        $sellerId = 0; $shopId = null; $productId = null; $adId = null;
        if ($type === 'product') {
            $p = q1('SELECT p.*, s.user_id, s.id AS sid FROM products p JOIN shops s ON s.id=p.shop_id WHERE p.id=? AND p.status="published"', [$id]);
            if (!$p) not_found();
            $sellerId = (int)$p['user_id']; $shopId = (int)$p['sid']; $productId = $id;
        } elseif ($type === 'ad') {
            $a = q1('SELECT * FROM ads WHERE id=? AND status="active"', [$id]);
            if (!$a) not_found();
            $sellerId = (int)$a['user_id']; $adId = $id;
        } else {
            $s = q1('SELECT * FROM shops WHERE id=? AND status="approved"', [$id]);
            if (!$s) not_found();
            $sellerId = (int)$s['user_id']; $shopId = $id;
        }
        if ($sellerId === (int)$me['id']) { flash('info', 'This is your own listing.'); back('/'); }
        $t = q1('SELECT * FROM message_threads WHERE buyer_id=? AND seller_id=? AND (product_id<=>? AND ad_id<=>? AND shop_id<=>?)',
            [$me['id'], $sellerId, $productId, $adId, $shopId]);
        if (!$t) {
            q('INSERT INTO message_threads (buyer_id,seller_id,shop_id,ad_id,product_id,created_at) VALUES (?,?,?,?,?,?)', [$me['id'], $sellerId, $shopId, $adId, $productId, now()]);
            $tid = last_id();
        } else $tid = (int)$t['id'];
        if ($first !== '') {
            q('INSERT INTO messages (thread_id,sender_id,body,created_at) VALUES (?,?,?,?)', [$tid, $me['id'], $first, now()]);
            q('UPDATE message_threads SET last_message=?, last_message_at=?, seller_unread=seller_unread+1 WHERE id=?', [$first, now(), $tid]);
            notify($sellerId, 'New inquiry from ' . $me['name'], mb_substr($first, 0, 120), 'message', '/chat/' . $tid);
        }
        redirect('/chat/' . $tid);
    }
}
