<?php
declare(strict_types=1);

namespace App\Controllers;

class NotificationController extends Controller
{
    public function index(): void
    {
        $me = require_login();
        $notifications = qa('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100', [$me['id']]);
        q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$me['id']]);
        $this->view('dashboard/notifications', ['notifications' => $notifications, 'seo' => ['title' => t('nav.notifications') . ' — eKamalia']]);
    }

    public function poll(): void
    {
        $me = require_login();
        $unread = (int)qv('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$me['id']]);
        $unreadMsgs = (int)qv('SELECT COALESCE(SUM(CASE WHEN seller_id=? THEN buyer_unread ELSE seller_unread END),0) FROM message_threads WHERE (buyer_id=? OR seller_id=?)', [$me['id'], $me['id'], $me['id']]);
        $rows = qa('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 8', [$me['id']]);
        $html = '';
        foreach ($rows as $n) {
            $icon = ['order' => 'fa-box', 'message' => 'fa-comment', 'payment' => 'fa-money-bill', 'pos' => 'fa-cash-register',
                     'shop' => 'fa-store', 'moderation' => 'fa-shield', 'comment' => 'fa-comment-dots', 'review' => 'fa-star'][$n['type']] ?? 'fa-bell';
            $html .= '<a class="dd-item' . ($n['is_read'] ? '' : ' unread') . '" href="' . e($n['url'] ?: '/notifications') . '">'
                . '<i class="fa-solid ' . $icon . ' mt-1" style="color:var(--ek-green)"></i><div><div class="fw-semibold">' . e($n['title']) . '</div>'
                . '<div class="text-muted" style="font-size:.75rem">' . e(mb_substr((string)$n['body'], 0, 90)) . '</div>'
                . '<div class="text-muted" style="font-size:.68rem">' . e(time_ago($n['created_at'])) . '</div></div></a>';
        }
        if (!$rows) $html = '<div class="p-4 text-center text-muted small">No notifications yet</div>';
        json_ok(['unread' => $unread, 'unread_messages' => $unreadMsgs, 'html' => $html]);
    }

    public function readAll(): void
    {
        $me = require_login();
        q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$me['id']]);
        if (is_ajax()) json_ok(['message' => 'All marked as read']);
        back('/notifications');
    }
}
