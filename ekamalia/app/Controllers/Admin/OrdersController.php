<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class OrdersController extends Controller
{
    public function index(): void
    {
        require_admin();
        $status = str_input('status', '');
        $q = str_input('q', '', 40);
        $conds = ['1=1']; $params = [];
        if (in_array($status, order_statuses(), true)) { $conds[] = 'o.status=?'; $params[] = $status; }
        if ($q) { $conds[] = '(o.order_number LIKE ? OR o.ship_name LIKE ? OR o.ship_phone LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
        $perPage = 30; $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM orders o WHERE ' . implode(' AND ', $conds), $params);
        $orders = qa('SELECT o.*, s.name AS shop_name, u.name AS buyer_name FROM orders o
                      LEFT JOIN shops s ON s.id=o.shop_id JOIN users u ON u.id=o.user_id
                      WHERE ' . implode(' AND ', $conds) . " ORDER BY o.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);
        $this->view('admin/orders', ['orders' => $orders, 'status' => $status, 'q' => $q, 'total' => $total, 'perPage' => $perPage, 'seo' => ['title' => 'Orders — Admin']], 'layouts/admin');
    }

    public function show(array $params): void
    {
        require_admin();
        $order = q1('SELECT o.*, s.name AS shop_name, s.phone AS shop_phone, u.name AS buyer_name, u.email AS buyer_email, u.phone AS buyer_phone2
                     FROM orders o LEFT JOIN shops s ON s.id=o.shop_id JOIN users u ON u.id=o.user_id WHERE o.id=?', [(int)$params['id']]);
        if (!$order) not_found();
        $items = qa('SELECT * FROM order_items WHERE order_id=?', [$order['id']]);
        $history = qa('SELECT h.*, u.name AS by_name FROM order_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.order_id=? ORDER BY h.created_at', [$order['id']]);
        $payment = q1('SELECT p.*, b.bank_name FROM payments p LEFT JOIN bank_accounts b ON b.id=p.bank_account_id WHERE p.order_id=? ORDER BY p.id DESC LIMIT 1', [$order['id']]);
        $this->view('admin/order-view', ['order' => $order, 'items' => $items, 'history' => $history, 'payment' => $payment, 'seo' => ['title' => 'Order ' . $order['order_number'] . ' — Admin']], 'layouts/admin');
    }

    public function status(): void
    {
        require_admin();
        $order = q1('SELECT * FROM orders WHERE id=?', [int_input('order_id')]);
        if (!$order) json_fail('Order not found', 404);
        $new = str_input('status');
        if (!in_array($new, order_statuses(), true)) json_fail('Invalid status');
        \App\Controllers\OrderController::transition($order, $new, str_input('note', 'Updated by admin', 300), user_id(), in_array($new, ['cancelled', 'returned', 'refunded'], true));
        if (is_ajax()) json_ok(['message' => 'Order updated to ' . order_status_label($new)]);
        flash('success', 'Order updated.');
        back('/admin/orders/' . $order['id']);
    }

    /* ---------------- payments verification ---------------- */
    public function payments(): void
    {
        require_admin();
        $status = str_input('status', 'submitted');
        $conds = ['1=1']; $params = [];
        if (in_array($status, ['pending', 'submitted', 'verified', 'rejected', 'refunded'], true)) { $conds[] = 'p.status=?'; $params[] = $status; }
        $payments = qa('SELECT p.*, o.order_number, o.total, o.status AS order_status, u.name AS user_name, u.email, b.bank_name
                        FROM payments p JOIN orders o ON o.id=p.order_id JOIN users u ON u.id=p.user_id LEFT JOIN bank_accounts b ON b.id=p.bank_account_id
                        WHERE ' . implode(' AND ', $conds) . ' ORDER BY FIELD(p.status,"submitted","pending","verified","rejected"), p.created_at DESC LIMIT 200', $params);
        $this->view('admin/payments', ['payments' => $payments, 'status' => $status, 'seo' => ['title' => 'Payments — Admin']], 'layouts/admin');
    }

    public function paymentVerify(array $params): void
    {
        require_admin();
        $p = q1('SELECT p.*, o.order_number, o.id AS oid, o.user_id AS buyer_id, o.total FROM payments p JOIN orders o ON o.id=p.order_id WHERE p.id=?', [(int)$params['id']]);
        if (!$p) not_found();
        $action = str_input('action');
        if ($action === 'verify') {
            q('UPDATE payments SET status="verified", verified_by=?, verified_at=? WHERE id=?', [user_id(), now(), $p['id']]);
            q('UPDATE orders SET payment_status="verified" WHERE id=?', [$p['oid']]);
            // notify buyer + seller
            notify((int)$p['buyer_id'], 'Payment verified ✅', 'Payment for order ' . $p['order_number'] . ' confirmed. Seller will process it now.', 'payment', '/order/' . $p['order_number']);
            $shopId = qv('SELECT shop_id FROM orders WHERE id=?', [$p['oid']]);
            if ($shopId) { $seller = qv('SELECT user_id FROM shops WHERE id=?', [$shopId]); if ($seller) notify((int)$seller, 'Payment verified for order ' . $p['order_number'], 'You can now process this order.', 'payment', '/seller/orders'); }
            audit_log('payment.verified', 'payment', (int)$p['id'], $p['order_number']);
            $msg = 'Payment verified';
        } elseif ($action === 'reject') {
            q('UPDATE payments SET status="rejected", verified_by=?, verified_at=?, note=? WHERE id=?', [user_id(), now(), str_input('note', '', 300) ?: null, $p['id']]);
            q('UPDATE orders SET payment_status="rejected" WHERE id=?', [$p['oid']]);
            notify((int)$p['buyer_id'], 'Payment rejected ❌', 'The receipt for order ' . $p['order_number'] . ' could not be verified. ' . str_input('note', '', 200), 'payment', '/payment/' . $p['order_number']);
            audit_log('payment.rejected', 'payment', (int)$p['id'], $p['order_number']);
            $msg = 'Payment rejected';
        } else json_fail('Unknown action');
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/payments');
    }

    /* ---------------- banks ---------------- */
    public function banks(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $data = [str_input('bank_name', '', 140), str_input('account_title', '', 140), str_input('account_number', '', 60),
                     str_input('iban', '', 40) ?: null, str_input('branch', '', 140) ?: null, str_input('instructions', '', 500) ?: null,
                     input('status') === 'inactive' ? 'inactive' : 'active', int_input('sort_order')];
            if ($id) { array_push($data, $id); q('UPDATE bank_accounts SET bank_name=?,account_title=?,account_number=?,iban=?,branch=?,instructions=?,status=?,sort_order=? WHERE id=?', $data); }
            else q('INSERT INTO bank_accounts (bank_name,account_title,account_number,iban,branch,instructions,status,sort_order) VALUES (?,?,?,?,?,?,?,?)', $data);
            audit_log('bank.saved', 'bank', $id, $data[0]);
            flash('success', 'Bank account saved.');
            back('/admin/banks');
        }
        $banks = qa('SELECT * FROM bank_accounts ORDER BY sort_order, id');
        $this->view('admin/banks', ['banks' => $banks, 'seo' => ['title' => 'Bank Accounts — Admin']], 'layouts/admin');
    }

    public function bankDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM bank_accounts WHERE id=?', [(int)$params['id']]);
        audit_log('bank.deleted', 'bank', (int)$params['id']);
        flash('success', 'Bank account deleted.');
        back('/admin/banks');
    }

    /* ---------------- analytics ---------------- */
    public function analytics(): void
    {
        require_admin();
        $daily = qa('SELECT DATE(created_at) d, COUNT(*) orders, COALESCE(SUM(total),0) revenue FROM orders WHERE created_at>=DATE_SUB(NOW(), INTERVAL 29 DAY) GROUP BY DATE(created_at) ORDER BY d');
        $monthly = qa('SELECT DATE_FORMAT(created_at,"%Y-%m") m, COUNT(*) orders, COALESCE(SUM(total),0) revenue FROM orders WHERE created_at>=DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY m ORDER BY m');
        $newUsers = qa('SELECT DATE(created_at) d, COUNT(*) c FROM users WHERE created_at>=DATE_SUB(NOW(), INTERVAL 29 DAY) GROUP BY DATE(created_at) ORDER BY d');
        $topSearches = qa('SELECT query, COUNT(*) c FROM search_logs GROUP BY query ORDER BY c DESC LIMIT 12');
        $noResults = qa('SELECT query FROM search_logs WHERE results_count=0 GROUP BY query ORDER BY COUNT(*) DESC LIMIT 10');
        $topProducts = qa('SELECT p.name, SUM(p.views) views FROM products p WHERE p.deleted_at IS NULL GROUP BY p.id ORDER BY views DESC LIMIT 8');
        $topAds = qa('SELECT a.title, a.views FROM ads a WHERE a.deleted_at IS NULL GROUP BY a.id ORDER BY a.views DESC LIMIT 8');
        $topShops = qa('SELECT s.name, COUNT(o.id) orders, COALESCE(SUM(o.total),0) revenue FROM shops s LEFT JOIN orders o ON o.shop_id=s.id AND o.status IN ("delivered","completed") GROUP BY s.id ORDER BY revenue DESC LIMIT 8');
        $this->view('admin/analytics', ['daily' => $daily, 'monthly' => $monthly, 'newUsers' => $newUsers, 'topSearches' => $topSearches,
            'noResults' => $noResults, 'topProducts' => $topProducts, 'topAds' => $topAds, 'topShops' => $topShops, 'seo' => ['title' => 'Analytics — Admin']], 'layouts/admin');
    }
}
