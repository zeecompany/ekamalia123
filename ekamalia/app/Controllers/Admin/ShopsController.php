<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class ShopsController extends Controller
{
    public function index(): void
    {
        require_admin();
        $status = str_input('status', '');
        $q = str_input('q', '', 100);
        $conds = ['s.deleted_at IS NULL']; $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) { $conds[] = 's.status=?'; $params[] = $status; }
        if ($q) { $conds[] = 's.name LIKE ?'; $params[] = "%$q%"; }
        $shops = qa('SELECT s.*, c.name AS city_name, u.name AS owner_name, u.email AS owner_email, p.name AS package_name
                     FROM shops s LEFT JOIN cities c ON c.id=s.city_id LEFT JOIN users u ON u.id=s.user_id LEFT JOIN packages p ON p.id=s.package_id
                     WHERE ' . implode(' AND ', $conds) . ' ORDER BY FIELD(s.status,"pending","approved","suspended","rejected"), s.created_at DESC LIMIT 200', $params);
        $this->view('admin/shops', ['shops' => $shops, 'status' => $status, 'q' => $q, 'seo' => ['title' => 'Shops — Admin']], 'layouts/admin');
    }

    public function update(array $params): void
    {
        require_admin();
        $shop = q1('SELECT * FROM shops WHERE id=?', [(int)$params['id']]);
        if (!$shop) not_found();
        $action = str_input('action');
        $note = str_input('note', '', 300);
        switch ($action) {
            case 'approve':
                q('UPDATE shops SET status="approved", status_note=? WHERE id=?', [$note ?: null, $shop['id']]);
                notify((int)$shop['user_id'], 'Shop approved 🎉', $shop['name'] . ' is now live on eKamalia! Start adding products.', 'shop', '/seller');
                send_app_mail($shop['email'] ?: qv('SELECT email FROM users WHERE id=?', [$shop['user_id']]), 'Your shop is approved! 🎉', $shop['name'] . ' has been approved. Login to your seller dashboard and add your first products.', '/seller');
                $msg = 'Shop approved';
                break;
            case 'reject':
                q('UPDATE shops SET status="rejected", status_note=? WHERE id=?', [$note ?: 'Does not meet guidelines', $shop['id']]);
                notify((int)$shop['user_id'], 'Shop not approved', $shop['name'] . ': ' . ($note ?: 'Please review our seller policy and resubmit.'), 'shop');
                $msg = 'Shop rejected';
                break;
            case 'suspend':
                q('UPDATE shops SET status="suspended", status_note=? WHERE id=?', [$note ?: 'Under review', $shop['id']]);
                notify((int)$shop['user_id'], 'Shop suspended', $shop['name'] . ' has been temporarily suspended. ' . $note, 'shop');
                $msg = 'Shop suspended';
                break;
            case 'verify': q('UPDATE shops SET is_verified=1 WHERE id=?', [$shop['id']]); notify((int)$shop['user_id'], 'Shop verified ✅', 'Your shop is now Verified on eKamalia.', 'shop'); $msg = 'Shop verified'; break;
            case 'unverify': q('UPDATE shops SET is_verified=0 WHERE id=?', [$shop['id']]); $msg = 'Verification removed'; break;
            case 'feature': q('UPDATE shops SET is_featured=1 WHERE id=?', [$shop['id']]); $msg = 'Shop featured'; break;
            case 'unfeature': q('UPDATE shops SET is_featured=0 WHERE id=?', [$shop['id']]); $msg = 'Featured removed'; break;
            case 'package':
                $pkgId = int_input('package_id') ?: null;
                $days = max(1, int_input('days', 30));
                q('UPDATE shops SET package_id=?, package_expires_at=? WHERE id=?', [$pkgId, $pkgId ? date('Y-m-d H:i:s', time() + $days * 86400) : null, $shop['id']]);
                $msg = 'Package assigned';
                break;
            default: json_fail('Unknown action');
        }
        audit_log('shop.' . $action, 'shop', (int)$shop['id'], $shop['name']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/shops');
    }

    /* ---------------- POS requests ---------------- */
    public function posRequests(): void
    {
        require_admin();
        $requests = qa('SELECT r.*, u.name AS user_name, u.email, s.name AS shop_name, p.name AS package_name
                        FROM pos_requests r JOIN users u ON u.id=r.user_id LEFT JOIN shops s ON s.id=r.shop_id LEFT JOIN pos_packages p ON p.id=r.package_id
                        ORDER BY FIELD(r.status,"pending","approved","suspended","rejected"), r.created_at DESC LIMIT 200');
        $active = qa('SELECT pu.*, u.name, u.email, s.name AS shop_name FROM pos_users pu JOIN users u ON u.id=pu.user_id LEFT JOIN shops s ON s.id=pu.shop_id ORDER BY pu.status, pu.role LIMIT 200');
        $this->view('admin/pos-requests', ['requests' => $requests, 'active' => $active, 'seo' => ['title' => 'POS Requests — Admin']], 'layouts/admin');
    }

    public function posRequestUpdate(array $params): void
    {
        require_admin();
        $r = q1('SELECT r.*, u.email FROM pos_requests r JOIN users u ON u.id=r.user_id WHERE r.id=?', [(int)$params['id']]);
        if (!$r) not_found();
        $action = str_input('action');
        if ($action === 'approve') {
            $pkgId = int_input('package_id') ?: $r['package_id'];
            $days = max(1, int_input('days', 365));
            $existingShop = $r['shop_id'] ?: qv('SELECT id FROM shops WHERE user_id=? AND status="approved"', [$r['user_id']]);
            q('UPDATE pos_requests SET status="approved", package_id=?, expires_at=?, admin_note=?, decided_at=? WHERE id=?',
                [$pkgId, date('Y-m-d H:i:s', time() + $days * 86400), str_input('note', '', 300) ?: null, now(), $r['id']]);
            if (!q1('SELECT id FROM pos_users WHERE user_id=? AND (shop_id=? OR user_id=?)', [$r['user_id'], $existingShop, $existingShop])) {
                q('INSERT INTO pos_users (user_id,shop_id,pos_request_id,role,permissions,status,expires_at,created_at) VALUES (?,?,?,?,?,?,?,?)',
                    [$r['user_id'], $existingShop, $r['id'], 'owner', '[]', 'active', date('Y-m-d H:i:s', time() + $days * 86400), now()]);
            } else {
                q('UPDATE pos_users SET status="active", expires_at=? WHERE user_id=?', [date('Y-m-d H:i:s', time() + $days * 86400), $r['user_id']]);
            }
            notify((int)$r['user_id'], 'POS approved 🎉', 'Your Premium POS access is active for ' . $days . ' days. Login and open the POS terminal.', 'pos', '/pos');
            send_app_mail($r['email'], 'POS Access Approved — eKamalia', 'Congratulations! Your Premium POS account is now active. Open your POS terminal from your dashboard.', '/pos');
            $msg = 'POS approved & activated';
        } elseif ($action === 'reject') {
            q('UPDATE pos_requests SET status="rejected", admin_note=?, decided_at=? WHERE id=?', [str_input('note', '', 300) ?: null, now(), $r['id']]);
            notify((int)$r['user_id'], 'POS request declined', 'Your POS request was declined. ' . str_input('note', '', 200), 'pos');
            $msg = 'POS request rejected';
        } elseif ($action === 'suspend') {
            q('UPDATE pos_requests SET status="suspended" WHERE id=?', [$r['id']]);
            q('UPDATE pos_users SET status="suspended" WHERE user_id=?', [$r['user_id']]);
            notify((int)$r['user_id'], 'POS suspended', 'Your POS access has been suspended. Contact support.', 'pos');
            $msg = 'POS suspended';
        } elseif ($action === 'extend') {
            $days = max(1, int_input('days', 30));
            q('UPDATE pos_users SET expires_at=DATE_ADD(COALESCE(expires_at,NOW()), INTERVAL ? DAY), status="active" WHERE user_id=?', [$days, $r['user_id']]);
            q('UPDATE pos_requests SET expires_at=DATE_ADD(COALESCE(expires_at,NOW()), INTERVAL ? DAY) WHERE id=?', [$days, $r['id']]);
            $msg = "POS extended $days days";
        } else json_fail('Unknown action');
        audit_log('pos.' . $action, 'pos_request', (int)$r['id']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/pos-requests');
    }

    /* ---------------- packages ---------------- */
    public function packages(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $data = [str_input('name', '', 120), float_input('price'), int_input('duration_days', 30), int_input('ad_limit'),
                     int_input('product_limit', 50), int_input('featured_ads'), input('can_shop') ? 1 : 0, input('can_coupons') ? 1 : 0, input('can_pos') ? 1 : 0];
            if ($id) { array_push($data, $id); q('UPDATE packages SET name=?,price=?,duration_days=?,ad_limit=?,product_limit=?,featured_ads=?,can_shop=?,can_coupons=?,can_pos=? WHERE id=?', $data); }
            else q('INSERT INTO packages (name,price,duration_days,ad_limit,product_limit,featured_ads,can_shop,can_coupons,can_pos,slug) VALUES (?,?,?,?,?,?,?,?,?,?)', array_merge($data, [unique_slug('packages', $data[0])]));
            audit_log('package.saved', 'package', $id);
            flash('success', 'Package saved.');
            back('/admin/packages');
        }
        $packages = qa('SELECT * FROM packages ORDER BY sort_order, price');
        $posPackages = qa('SELECT * FROM pos_packages ORDER BY price');
        $this->view('admin/packages', ['packages' => $packages, 'posPackages' => $posPackages, 'seo' => ['title' => 'Packages — Admin']], 'layouts/admin');
    }

    public function posPackages(): void
    {
        require_admin();
        $id = int_input('id');
        $data = [str_input('name', '', 120), float_input('price'), int_input('duration_days', 365), int_input('max_users', 3), int_input('max_products', 1000), input('status') === 'inactive' ? 'inactive' : 'active'];
        if ($id) { array_push($data, $id); q('UPDATE pos_packages SET name=?,price=?,duration_days=?,max_users=?,max_products=?,status=? WHERE id=?', $data); }
        else q('INSERT INTO pos_packages (name,price,duration_days,max_users,max_products,status) VALUES (?,?,?,?,?,?)', $data);
        audit_log('pos_package.saved', 'pos_package', $id);
        flash('success', 'POS package saved.');
        back('/admin/packages');
    }

    public function packageDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM packages WHERE id=?', [(int)$params['id']]);
        flash('success', 'Package deleted.');
        back('/admin/packages');
    }

    /* ---------------- promotions queue ---------------- */
    public function promotions(): void
    {
        require_admin();
        $requests = qa('SELECT pr.*, u.name AS user_name, a.title AS ad_title, p.name AS product_name
                        FROM promotion_requests pr JOIN users u ON u.id=pr.user_id
                        LEFT JOIN ads a ON pr.type IN ("ad_featured","ad_urgent") AND a.id=pr.item_id
                        LEFT JOIN products p ON pr.type="product_featured" AND p.id=pr.item_id
                        ORDER BY FIELD(pr.status,"pending","approved","rejected"), pr.created_at DESC LIMIT 100');
        $this->view('admin/promotions', ['requests' => $requests, 'seo' => ['title' => 'Promotion Requests — Admin']], 'layouts/admin');
    }

    public function promotionUpdate(array $params): void
    {
        require_admin();
        $r = q1('SELECT * FROM promotion_requests WHERE id=?', [(int)$params['id']]);
        if (!$r) not_found();
        $action = str_input('action');
        $days = max(1, int_input('days', 7));
        if ($action === 'approve') {
            match ($r['type']) {
                'ad_featured' => q('UPDATE ads SET is_featured=1, featured_until=? WHERE id=?', [date('Y-m-d H:i:s', time() + $days * 86400), $r['item_id']]),
                'ad_urgent' => q('UPDATE ads SET is_urgent=1, urgent_until=? WHERE id=?', [date('Y-m-d H:i:s', time() + $days * 86400), $r['item_id']]),
                'product_featured' => q('UPDATE products SET is_featured=1 WHERE id=?', [$r['item_id']]),
                'shop_featured' => q('UPDATE shops SET is_featured=1 WHERE id=?', [$r['item_id']]),
                default => null,
            };
            q('UPDATE promotion_requests SET status="approved", admin_note=? WHERE id=?', ["Approved for $days days", $r['id']]);
            notify((int)$r['user_id'], 'Promotion approved ⭐', 'Your promotion is active for ' . $days . ' days.', 'moderation');
            $msg = 'Promotion approved';
        } else {
            q('UPDATE promotion_requests SET status="rejected", admin_note=? WHERE id=?', [str_input('note', '', 300) ?: null, $r['id']]);
            notify((int)$r['user_id'], 'Promotion declined', 'Your promotion request was declined. ' . str_input('note', '', 200), 'moderation');
            $msg = 'Promotion rejected';
        }
        audit_log('promotion.' . $action, 'promotion', (int)$r['id']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/promotions');
    }
}
