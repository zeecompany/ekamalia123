<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class ModerationController extends Controller
{
    /* ---------------- reviews ---------------- */
    public function reviews(): void
    {
        require_admin();
        $status = str_input('status', '');
        $conds = ['1=1']; $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) { $conds[] = 'r.status=?'; $params[] = $status; }
        $reviews = qa('SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE ' . implode(' AND ', $conds) . ' ORDER BY FIELD(r.status,"pending","approved"), r.created_at DESC LIMIT 200', $params);
        $this->view('admin/reviews', ['reviews' => $reviews, 'status' => $status, 'seo' => ['title' => 'Reviews — Admin']], 'layouts/admin');
    }

    public function reviewUpdate(array $params): void
    {
        require_admin();
        $r = q1('SELECT * FROM reviews WHERE id=?', [(int)$params['id']]);
        if (!$r) not_found();
        $action = str_input('action');
        if ($action === 'approve') { q('UPDATE reviews SET status="approved" WHERE id=?', [$r['id']]); \App\Controllers\SocialController::recalcRating($r['item_type'], (int)$r['item_id']); $msg = 'Review approved'; }
        elseif ($action === 'reject') { q('UPDATE reviews SET status="rejected" WHERE id=?', [$r['id']]); \App\Controllers\SocialController::recalcRating($r['item_type'], (int)$r['item_id']); $msg = 'Review rejected'; }
        elseif ($action === 'delete') { q('DELETE FROM reviews WHERE id=?', [$r['id']]); \App\Controllers\SocialController::recalcRating($r['item_type'], (int)$r['item_id']); $msg = 'Review deleted'; }
        else json_fail('Unknown action');
        audit_log('review.' . $action, 'review', (int)$r['id']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/reviews');
    }

    /* ---------------- comments ---------------- */
    public function comments(): void
    {
        require_admin();
        $status = str_input('status', '');
        $conds = ['1=1']; $params = [];
        if (in_array($status, ['visible', 'hidden', 'deleted'], true)) { $conds[] = 'c.status=?'; $params[] = $status; }
        $comments = qa('SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON u.id=c.user_id WHERE ' . implode(' AND ', $conds) . ' ORDER BY c.created_at DESC LIMIT 200', $params);
        $this->view('admin/comments', ['comments' => $comments, 'status' => $status, 'seo' => ['title' => 'Comments — Admin']], 'layouts/admin');
    }

    public function commentUpdate(array $params): void
    {
        require_admin();
        $c = q1('SELECT * FROM comments WHERE id=?', [(int)$params['id']]);
        if (!$c) not_found();
        $action = str_input('action');
        if ($action === 'hide') { q('UPDATE comments SET status="hidden" WHERE id=?', [$c['id']]); $msg = 'Comment hidden'; }
        elseif ($action === 'restore') { q('UPDATE comments SET status="visible" WHERE id=?', [$c['id']]); $msg = 'Comment restored'; }
        elseif ($action === 'delete') { q('UPDATE comments SET status="deleted" WHERE id=?', [$c['id']]); $msg = 'Comment deleted'; }
        else json_fail('Unknown action');
        audit_log('comment.' . $action, 'comment', (int)$c['id']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/comments');
    }

    /* ---------------- abuse reports ---------------- */
    public function reports(): void
    {
        require_admin();
        $status = str_input('status', 'open');
        $conds = ['1=1']; $params = [];
        if (in_array($status, ['open', 'reviewed', 'resolved', 'dismissed'], true)) { $conds[] = 'r.status=?'; $params[] = $status; }
        $reports = qa('SELECT r.*, u.name AS reporter_name FROM reports r LEFT JOIN users u ON u.id=r.reporter_id WHERE ' . implode(' AND ', $conds) . ' ORDER BY r.created_at DESC LIMIT 200', $params);
        $this->view('admin/reports', ['reports' => $reports, 'status' => $status, 'seo' => ['title' => 'Reports Queue — Admin']], 'layouts/admin');
    }

    public function reportUpdate(array $params): void
    {
        require_admin();
        $r = q1('SELECT * FROM reports WHERE id=?', [(int)$params['id']]);
        if (!$r) not_found();
        $action = str_input('action'); // reviewed | resolved | dismissed | hide_item | delete_item | ban_user
        $note = str_input('note', '', 300);
        $msg = 'Report updated';
        switch ($action) {
            case 'reviewed': q('UPDATE reports SET status="reviewed", admin_note=? WHERE id=?', [$note ?: null, $r['id']]); $msg = 'Marked reviewed'; break;
            case 'resolved': q('UPDATE reports SET status="resolved", admin_note=? WHERE id=?', [$note ?: null, $r['id']]); $msg = 'Marked resolved'; break;
            case 'dismissed': q('UPDATE reports SET status="dismissed", admin_note=? WHERE id=?', [$note ?: null, $r['id']]); $msg = 'Report dismissed'; break;
            case 'hide_item':
                $this->hideItem($r['item_type'], (int)$r['item_id']);
                q('UPDATE reports SET status="resolved", admin_note=? WHERE id=?', ['Item hidden: ' . $note, $r['id']]);
                $msg = 'Item hidden & report resolved';
                break;
            case 'delete_item':
                $this->deleteItem($r['item_type'], (int)$r['item_id']);
                q('UPDATE reports SET status="resolved", admin_note=? WHERE id=?', ['Item deleted', $r['id']]);
                $msg = 'Item deleted & report resolved';
                break;
            case 'ban_user':
                $uid = $this->ownerOf($r['item_type'], (int)$r['item_id']);
                if ($uid) {
                    q('UPDATE users SET status="banned" WHERE id=? AND role="user"', [$uid]);
                    q('UPDATE reports SET status="resolved", admin_note=? WHERE id=?', ['User banned', $r['id']]);
                    notify((int)$uid, 'Account suspended', 'Your account was suspended due to a policy violation. ' . $note, 'admin');
                    $msg = 'User banned & report resolved';
                } else { $msg = 'Could not identify user'; }
                break;
            default: json_fail('Unknown action');
        }
        audit_log('report.' . $action, 'report', (int)$r['id']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/reports');
    }

    private function hideItem(string $type, int $id): void
    {
        match ($type) {
            'ad' => q('UPDATE ads SET status="paused" WHERE id=?', [$id]),
            'product' => q('UPDATE products SET status="hidden" WHERE id=?', [$id]),
            'comment' => q('UPDATE comments SET status="hidden" WHERE id=?', [$id]),
            'shop' => q('UPDATE shops SET status="suspended" WHERE id=?', [$id]),
            'business' => q('UPDATE businesses SET status="rejected" WHERE id=?', [$id]),
            default => null,
        };
    }
    private function deleteItem(string $type, int $id): void
    {
        match ($type) {
            'ad' => q('UPDATE ads SET deleted_at=?, status="expired" WHERE id=?', [now(), $id]),
            'product' => q('UPDATE products SET deleted_at=?, status="archived" WHERE id=?', [now(), $id]),
            'comment' => q('UPDATE comments SET status="deleted" WHERE id=?', [$id]),
            'business' => q('UPDATE businesses SET deleted_at=? WHERE id=?', [now(), $id]),
            default => null,
        };
    }
    private function ownerOf(string $type, int $id): ?int
    {
        return match ($type) {
            'ad' => (int)(qv('SELECT user_id FROM ads WHERE id=?', [$id]) ?? 0),
            'product' => (int)(qv('SELECT s.user_id FROM products p JOIN shops s ON s.id=p.shop_id WHERE p.id=?', [$id]) ?? 0),
            'comment' => (int)(qv('SELECT user_id FROM comments WHERE id=?', [$id]) ?? 0),
            'shop' => (int)(qv('SELECT user_id FROM shops WHERE id=?', [$id]) ?? 0),
            'business' => (int)(qv('SELECT user_id FROM businesses WHERE id=?', [$id]) ?? 0),
            'user' => $id,
            default => 0,
        } ?: null;
    }

    /* ---------------- contact inbox ---------------- */
    public function inbox(): void
    {
        require_admin();
        $filter = str_input('filter', '');
        $conds = ['1=1'];
        if ($filter === 'unread') $conds[] = 'm.is_read=0';
        if ($filter === 'archived') $conds[] = 'm.status="archived"';
        if ($filter === 'replied') $conds[] = 'm.replied_at IS NOT NULL';
        $messages = qa('SELECT m.* FROM contact_messages m WHERE ' . implode(' AND ', $conds) . ' ORDER BY m.created_at DESC LIMIT 200');
        $this->view('admin/inbox', ['messages' => $messages, 'filter' => $filter, 'seo' => ['title' => 'Contact Inbox — Admin']], 'layouts/admin');
    }

    public function inboxUpdate(array $params): void
    {
        require_admin();
        $m = q1('SELECT * FROM contact_messages WHERE id=?', [(int)$params['id']]);
        if (!$m) not_found();
        $action = str_input('action');
        if ($action === 'read') q('UPDATE contact_messages SET is_read=1, status="read" WHERE id=?', [$m['id']]);
        elseif ($action === 'archive') q('UPDATE contact_messages SET status="archived" WHERE id=?', [$m['id']]);
        elseif ($action === 'delete') { q('DELETE FROM contact_messages WHERE id=?', [$m['id']]); flash('success', 'Message deleted.'); back('/admin/inbox'); }
        elseif ($action === 'reply') {
            $reply = str_input('reply', '', 5000);
            if ($reply === '') { flash('danger', 'Write a reply first.'); back('/admin/inbox'); }
            send_app_mail($m['email'], 'Re: ' . ($m['subject'] ?: 'Your eKamalia message'), $reply . "\n\n--- Original message ---\n" . mb_substr($m['message'], 0, 500), '/contact');
            q('UPDATE contact_messages SET reply_text=?, replied_at=?, is_read=1 WHERE id=?', [$reply, now(), $m['id']]);
            audit_log('inbox.replied', 'message', (int)$m['id']);
            flash('success', 'Reply sent to ' . $m['email']);
            back('/admin/inbox');
        }
        flash('success', 'Updated.');
        back('/admin/inbox');
    }

    /* ---------------- settings ---------------- */
    public function settings(): void
    {
        require_admin();
        if (is_post()) {
            $keys = ['site_name', 'site_tagline', 'site_email', 'site_phone', 'whatsapp_number', 'site_address',
                     'currency', 'currency_symbol', 'primary_color', 'footer_text',
                     'social_fb', 'social_ig', 'social_x', 'social_yt',
                     'seo_title', 'seo_description', 'seo_keywords',
                     'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption', 'smtp_from_name', 'smtp_from_email',
                     'otp_enabled', 'otp_expiry_minutes', 'reg_otp_required', 'login_otp_required',
                     'ads_require_approval', 'products_require_approval', 'shops_require_approval', 'reviews_require_approval',
                     'comments_enabled', 'chat_enabled', 'pos_enabled', 'cod_enabled', 'bank_transfer_enabled',
                     'free_ads_limit', 'per_page', 'max_upload_mb', 'commission_percent',
                     'maintenance_mode', 'maintenance_message', 'payment_instructions', 'app_theme_color', 'delivery_default_fee'];
            $updated = 0;
            foreach ($keys as $k) {
                if (!array_key_exists($k, $_POST)) continue;
                $val = is_array($_POST[$k]) ? implode(',', $_POST[$k]) : trim((string)$_POST[$k]);
                // keep-existing guard: untouched bullet field or empty must not wipe a stored secret
                if ($k === 'smtp_pass' && ($val === '' || preg_match('/^[•·*]+$/', $val))) continue;
                setting_save($k, $val); $updated++;
            }
            if (!empty($_POST['og_image_file']) || isset($_FILES['og_image'])) {
                try { $og = upload_image('og_image', 'banners', 5); if ($og) setting_save('og_image', $og); } catch (\RuntimeException $e) { flash('warning', 'OG image: ' . $e->getMessage()); }
            }
            try { $logo = upload_image('logo_file', 'banners', 3); if ($logo) setting_save('custom_logo', $logo); } catch (\RuntimeException $e) {}
            audit_log('settings.updated', 'settings', 0, "$updated keys");
            flash('success', "Settings saved ($updated keys updated).");
            back('/admin/settings' . (str_input('tab') ? '?tab=' . str_input('tab') : ''));
        }
        $tab = str_input('tab', 'general');
        $this->view('admin/settings', ['tab' => $tab, 'seo' => ['title' => 'Settings — Admin']], 'layouts/admin');
    }

    public function testEmail(): void
    {
        require_admin();
        $to = str_input('email', setting('site_email', ''), 190);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) json_fail('Enter a valid email');
        $ok = send_app_mail($to, 'eKamalia SMTP Test ✓', 'If you received this email, your SMTP configuration works. JazakAllah!', '', 'eKamalia');
        $status = q1('SELECT status, error FROM email_logs ORDER BY id DESC LIMIT 1');
        json_ok(['message' => $ok ? 'Test email sent to ' . $to : 'Send failed: ' . ($status['error'] ?? 'unknown error') . '. Check SMTP settings.']);
    }
}
