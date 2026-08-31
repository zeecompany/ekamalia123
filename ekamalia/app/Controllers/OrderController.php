<?php
declare(strict_types=1);

namespace App\Controllers;

class OrderController extends Controller
{
    public function index(): void
    {
        $me = require_login();
        $orders = qa('SELECT o.*, s.name AS shop_name, (SELECT image FROM order_items oi WHERE oi.order_id=o.id LIMIT 1) AS first_image,
                      (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS items_count
                      FROM orders o LEFT JOIN shops s ON s.id=o.shop_id
                      WHERE o.user_id=? ORDER BY o.created_at DESC LIMIT 100', [$me['id']]);
        $this->view('dashboard/orders', ['orders' => $orders, 'seo' => ['title' => t('nav.my_orders') . ' — eKamalia']]);
    }

    public function show(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT o.*, s.name AS shop_name, s.slug AS shop_slug, s.phone AS shop_phone, s.whatsapp AS shop_whatsapp
                     FROM orders o LEFT JOIN shops s ON s.id=o.shop_id WHERE o.order_number=?', [$params['number']]);
        if (!$order) not_found();
        $isBuyer = (int)$order['user_id'] === (int)$me['id'];
        $isSeller = false;
        $myShop = my_shop();
        if ($myShop && (int)$order['shop_id'] === (int)$myShop['id']) $isSeller = true;
        if (!$isBuyer && !$isSeller && $me['role'] !== 'admin') not_found();

        $items = qa('SELECT oi.*, p.slug AS product_slug FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?', [$order['id']]);
        $history = qa('SELECT h.*, u.name AS changed_by_name FROM order_status_history h LEFT JOIN users u ON u.id=h.changed_by
                       WHERE h.order_id=? ORDER BY h.created_at', [$order['id']]);
        $payment = q1('SELECT p.*, b.bank_name, b.account_title, b.account_number, b.iban, b.branch, b.instructions FROM payments p
                       LEFT JOIN bank_accounts b ON b.id=p.bank_account_id WHERE p.order_id=? ORDER BY p.id DESC LIMIT 1', [$order['id']]);
        $siblings = qa('SELECT id,order_number,shop_id,status,total FROM orders WHERE group_number=? AND id<>?', [$order['group_number'], $order['id']]);

        $this->view('orders/show', ['order' => $order, 'items' => $items, 'history' => $history, 'payment' => $payment, 'siblings' => $siblings,
            'isSeller' => $isSeller, 'isBuyer' => $isBuyer,
            'seo' => ['title' => 'Order ' . $order['order_number'] . ' — Track | eKamalia']]);
    }

    /* public tracking (limited info, by order number + any session ownership or guest token) */
    public function track(): void
    {
        $this->view('orders/track', ['seo' => ['title' => 'Track Your Order — eKamalia']]);
    }

    public function trackResult(): void
    {
        $no = strtoupper(str_input('order_number', '', 30));
        $order = q1('SELECT id, order_number, group_number, status, payment_status, created_at, estimated_delivery FROM orders WHERE order_number=?', [$no]);
        $history = $order ? qa('SELECT status,note,created_at FROM order_status_history WHERE order_id=? ORDER BY created_at', [$order['id']]) : [];
        $this->view('orders/track', ['searchNo' => $no, 'order' => $order, 'history' => $history,
            'seo' => ['title' => 'Track Order — eKamalia']]);
    }

    /* ---------------- buyer actions ---------------- */
    public function cancel(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT * FROM orders WHERE order_number=?', [$params['number']]);
        if (!$order || ((int)$order['user_id'] !== (int)$me['id'] && $me['role'] !== 'admin')) not_found();
        if (!in_array($order['status'], ['pending', 'confirmed'], true)) json_fail('Order can no longer be cancelled at this stage.');
        $reason = str_input('reason', 'Cancelled by customer', 300);
        $this->transition($order, 'cancelled', $reason, (int)$me['id'], true);
        if (is_ajax()) json_ok(['message' => 'Order cancelled.']);
        flash('success', 'Order cancelled.');
        back('/order/' . $order['order_number']);
    }

    public function refundRequest(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT * FROM orders WHERE order_number=?', [$params['number']]);
        if (!$order || (int)$order['user_id'] !== (int)$me['id']) not_found();
        if (!in_array($order['status'], ['delivered', 'completed'], true)) json_fail('Refund can be requested after delivery.');
        if ($order['status'] !== 'refund_requested') {
            $this->transition($order, 'refund_requested', str_input('reason', 'Buyer requested refund', 300), (int)$me['id'], false);
            notify_admins('Refund requested', 'Order ' . $order['order_number'] . ' — ' . money($order['total']), 'payment', '/admin/orders?status=refund_requested');
        }
        if (is_ajax()) json_ok(['message' => 'Refund request submitted.']);
        flash('success', 'Refund request submitted. Our team will review it.');
        back('/order/' . $order['order_number']);
    }

    public function confirmReceived(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT * FROM orders WHERE order_number=?', [$params['number']]);
        if (!$order || (int)$order['user_id'] !== (int)$me['id']) not_found();
        if ($order['status'] !== 'delivered') json_fail('Order is not marked delivered yet.');
        $this->transition($order, 'completed', 'Buyer confirmed receipt', (int)$me['id'], false);
        if (is_ajax()) json_ok(['message' => 'Thank you! Order completed.']);
        flash('success', 'Order completed. Don\'t forget to leave a review!');
        back('/order/' . $order['order_number']);
    }

    /* ---------------- shared transition helper ---------------- */
    public static function transition(array $order, string $newStatus, string $note, int $byUser, bool $restock): void
    {
        q('UPDATE orders SET status=?, updated_at=? WHERE id=?', [$newStatus, now(), $order['id']]);
        q('INSERT INTO order_status_history (order_id,status,note,changed_by,created_at) VALUES (?,?,?,?,?)', [$order['id'], $newStatus, mb_substr($note, 0, 290), $byUser, now()]);
        if ($restock && in_array($newStatus, ['cancelled', 'returned', 'refunded'], true) && !in_array($order['status'], ['cancelled', 'returned', 'refunded'], true)) {
            $items = qa('SELECT * FROM order_items WHERE order_id=?', [$order['id']]);
            foreach ($items as $it) {
                if (!$it['product_id']) continue;
                $p = q1('SELECT stock,shop_id FROM products WHERE id=?', [$it['product_id']]);
                if (!$p) continue;
                $new = (int)$p['stock'] + (int)$it['quantity'];
                q('UPDATE products SET stock=?, status=IF(status="out_of_stock","published",status) WHERE id=?', [$new, $it['product_id']]);
                q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,reference_type,reference_id,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                    [$p['shop_id'], $it['product_id'], 'return_in', (int)$it['quantity'], (int)$p['stock'], $new, 'Order ' . $order['order_number'] . ' — ' . $newStatus, 'order', $order['id'], $byUser, now()]);
            }
        }
        // notify the other party
        $msg = 'Order ' . $order['order_number'] . ' is now: ' . order_status_label($newStatus);
        if ((int)$order['user_id'] !== $byUser) {
            notify((int)$order['user_id'], 'Order update — ' . $order['order_number'], $msg, 'order', '/order/' . $order['order_number']);
        } elseif ($order['shop_id']) {
            $seller = qv('SELECT user_id FROM shops WHERE id=?', [$order['shop_id']]);
            if ($seller && (int)$seller !== $byUser) notify((int)$seller, 'Order update — ' . $order['order_number'], $msg, 'order', '/seller/orders');
        }
    }
}
