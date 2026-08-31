<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class UsersController extends Controller
{
    public function index(): void
    {
        require_admin();
        $q = str_input('q', '', 100);
        $status = str_input('status', '');
        $conds = ['1=1']; $params = [];
        if ($q) { $conds[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)'; $params = ["%$q%", "%$q%", "%$q%"]; }
        if (in_array($status, ['active', 'pending', 'suspended', 'banned'], true)) { $conds[] = 'u.status=?'; $params[] = $status; }
        $perPage = 30; $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM users u WHERE ' . implode(' AND ', $conds), $params);
        $users = qa('SELECT u.*, c.name AS city_name,
                    (SELECT COUNT(*) FROM shops s WHERE s.user_id=u.id) AS shops_count,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) AS orders_count
                    FROM users u LEFT JOIN cities c ON c.id=u.city_id
                    WHERE ' . implode(' AND ', $conds) . " ORDER BY u.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);
        $this->view('admin/users', ['users' => $users, 'q' => $q, 'status' => $status, 'total' => $total, 'perPage' => $perPage, 'seo' => ['title' => 'Users — Admin']], 'layouts/admin');
    }

    public function show(array $params): void
    {
        require_admin();
        $u = q1('SELECT u.*, c.name AS city_name FROM users u LEFT JOIN cities c ON c.id=u.city_id WHERE u.id=?', [(int)$params['id']]);
        if (!$u) not_found();
        $orders = qa('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 10', [$u['id']]);
        $ads = qa('SELECT id,title,status,created_at FROM ads WHERE user_id=? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 10', [$u['id']]);
        $shops = qa('SELECT id,name,slug,status FROM shops WHERE user_id=?', [$u['id']]);
        $logins = qa('SELECT * FROM login_history WHERE user_id=? ORDER BY created_at DESC LIMIT 8', [$u['id']]);
        $reports = qa('SELECT * FROM reports WHERE item_type="user" AND item_id=? ORDER BY created_at DESC LIMIT 10', [$u['id']]);
        $this->view('admin/user-view', ['u' => $u, 'orders' => $orders, 'ads' => $ads, 'shops' => $shops, 'logins' => $logins, 'reports' => $reports, 'seo' => ['title' => 'User: ' . $u['name'] . ' — Admin']], 'layouts/admin');
    }

    public function update(array $params): void
    {
        require_admin();
        $u = q1('SELECT * FROM users WHERE id=?', [(int)$params['id']]);
        if (!$u) not_found();
        $action = str_input('action');
        switch ($action) {
            case 'status':
                $s = str_input('status');
                if (!in_array($s, ['active', 'pending', 'suspended', 'banned'], true)) json_fail('Invalid status');
                if ((int)$u['id'] === user_id()) json_fail('You cannot change your own status');
                q('UPDATE users SET status=? WHERE id=?', [$s, $u['id']]);
                if (in_array($s, ['suspended', 'banned'], true)) {
                    q('UPDATE shops SET status="suspended" WHERE user_id=? AND status="approved"', [$u['id']]);
                    q('UPDATE pos_users SET status="suspended" WHERE user_id=?', [$u['id']]);
                }
                notify((int)$u['id'], 'Account status changed', 'Your account is now: ' . ucfirst($s), 'admin');
                $msg = 'User ' . $s;
                break;
            case 'verify':
                q('UPDATE users SET is_verified=1, email_verified_at=COALESCE(email_verified_at,?) WHERE id=?', [now(), $u['id']]);
                notify((int)$u['id'], 'Account verified ✅', 'Your eKamalia account is now verified.', 'admin');
                $msg = 'User verified';
                break;
            case 'unverify': q('UPDATE users SET is_verified=0 WHERE id=?', [$u['id']]); $msg = 'Verification removed'; break;
            case 'reset_password':
                $new = 'EK' . bin2hex(random_bytes(4));
                q('UPDATE users SET password=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
                send_app_mail($u['email'], 'Your eKamalia password was reset', "Your password was reset by an administrator.\n\nNew password: $new\n\nPlease login and change it immediately.", '/login');
                $msg = 'Password reset — new password emailed';
                break;
            case 'make_admin': if (user_id() === 1 || is_admin()) { q('UPDATE users SET role="admin" WHERE id=?', [$u['id']]); $msg = 'Promoted to admin'; } break;
            case 'remove_admin': if ((int)$u['id'] !== user_id()) { q('UPDATE users SET role="user" WHERE id=?', [$u['id']]); $msg = 'Admin rights removed'; } break;
            default: json_fail('Unknown action');
        }
        audit_log('user.' . $action, 'user', (int)$u['id'], $u['email']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/users');
    }
}
