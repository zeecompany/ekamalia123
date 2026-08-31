<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cart;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $me = require_login();
        $groups = Cart::groups();
        if (!$groups) { flash('warning', 'Your cart is empty.'); redirect('/products'); }
        foreach ($groups as $g) {
            foreach ($g['items'] as $it) {
                if ((int)$it['quantity'] > (int)$it['stock']) {
                    flash('danger', $it['name'] . ' has only ' . $it['stock'] . ' in stock.');
                    redirect('/cart');
                }
            }
        }
        $totals = Cart::totals($groups);
        $addresses = qa('SELECT a.*, c.name AS city_name FROM addresses a LEFT JOIN cities c ON c.id=a.city_id WHERE a.user_id=? ORDER BY a.is_default DESC, a.id DESC', [$me['id']]);
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $banks = qa('SELECT * FROM bank_accounts WHERE status="active" ORDER BY sort_order');
        $this->view('checkout/index', ['groups' => $groups, 'totals' => $totals, 'addresses' => $addresses, 'cities' => $cities, 'banks' => $banks,
            'seo' => ['title' => t('checkout.title') . ' — eKamalia']]);
    }

    public function place(): void
    {
        $me = require_login();
        $groups = Cart::groups();
        if (!$groups) { flash('warning', 'Your cart is empty.'); redirect('/products'); }

        $name = str_input('ship_name', '', 120);
        $phone = preg_replace('/\D/', '', str_input('ship_phone', '', 20));
        if (str_starts_with($phone, '92')) $phone = substr($phone, 2);
        if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
        $address = str_input('ship_address', '', 255);
        $cityId = int_input('ship_city_id');
        $city = $cityId ? q1('SELECT name FROM cities WHERE id=?', [$cityId]) : null;
        $method = str_input('payment_method', 'cod') === 'bank_transfer' ? 'bank_transfer' : 'cod';
        if ($method === 'bank_transfer' && setting('bank_transfer_enabled') !== '1') $method = 'cod';
        if ($method === 'cod' && setting('cod_enabled') !== '1') $method = 'bank_transfer';

        $errors = [];
        if (mb_strlen($name) < 3) $errors[] = 'Please enter the receiver name.';
        if (!preg_match('/^3\d{9}$/', $phone)) $errors[] = 'Enter a valid mobile number (03XXXXXXXXX).';
        if (mb_strlen($address) < 10) $errors[] = 'Please enter a complete delivery address.';
        if ($errors) { flash('danger', implode(' ', $errors)); back('/checkout'); }

        // save address if requested
        if (input('save_address')) {
            q('UPDATE addresses SET is_default=0 WHERE user_id=?', [$me['id']]);
            q('INSERT INTO addresses (user_id,label,name,phone,address,city_id,area,is_default,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
                [$me['id'], 'Home', $name, '0' . $phone, $address, $cityId, str_input('ship_area', '', 140), 1, now()]);
        }

        $totals = Cart::totals($groups);
        $groupNo = 'EK-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
        $bankId = $method === 'bank_transfer' ? (int_input('bank_account_id') ?: null) : null;
        $created = [];

        db()->beginTransaction();
        try {
            $seq = 0;
            foreach ($groups as $g) {
                $seq++;
                $subDiscount = 0;
                if ($totals['discount'] > 0) {
                    $coupon = q1('SELECT * FROM coupons WHERE code=?', [$totals['coupon']]);
                    if ($coupon && $coupon['shop_id'] && (int)$coupon['shop_id'] === (int)$g['shop_id']) $subDiscount = $totals['discount'];
                    elseif ($coupon && !$coupon['shop_id']) $subDiscount = round($totals['discount'] * ($g['subtotal'] / max(1, $totals['subtotal'])), 2);
                }
                $shipping = $totals['free_delivery'] ? 0 : $g['delivery_fee'];
                $subTotal = round($g['subtotal'] - $subDiscount, 2);
                $orderNo = count($groups) > 1 ? $groupNo . '-' . $seq : $groupNo;
                q('INSERT INTO orders (order_number,group_number,user_id,shop_id,subtotal,discount,coupon_code,shipping_fee,total,payment_method,payment_status,status,delivery_method,ship_name,ship_phone,ship_address,ship_city,ship_area,note,estimated_delivery,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                    $orderNo, $groupNo, $me['id'], $g['shop_id'],
                    $g['subtotal'], $subDiscount, $totals['coupon'], $shipping, $subTotal + $shipping,
                    $method, 'pending', 'pending', 'seller_delivery',
                    $name, '0' . $phone, $address, $city['name'] ?? '', str_input('ship_area', '', 140),
                    str_input('note', '', 500) ?: null, '2-3 working days', now(),
                ]);
                $orderId = last_id();
                q('INSERT INTO order_status_history (order_id,status,note,changed_by,created_at) VALUES (?,?,?,?,?)', [$orderId, 'pending', 'Order placed', $me['id'], now()]);

                if ($method === 'bank_transfer') {
                    q('INSERT INTO payments (order_id,user_id,method,bank_account_id,amount,status,created_at) VALUES (?,?,?,?,?,?,?)',
                        [$orderId, $me['id'], 'bank_transfer', $bankId, $subTotal + $shipping, 'pending', now()]);
                }

                foreach ($g['items'] as $it) {
                    q('INSERT INTO order_items (order_id,product_id,name,image,price,quantity,total) VALUES (?,?,?,?,?,?,?)',
                        [$orderId, $it['product_id'], $it['name'], $it['image'] ?? null, $it['unit_price'], (int)$it['quantity'], $it['line_total']]);
                    // stock + movement
                    $prev = (int)$it['stock'];
                    $new = max(0, $prev - (int)$it['quantity']);
                    q('UPDATE products SET stock=? WHERE id=?', [$new, $it['product_id']]);
                    q('UPDATE products SET status="out_of_stock" WHERE id=? AND stock<=0', [$it['product_id']]);
                    q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,reference_type,reference_id,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                        [$g['shop_id'], $it['product_id'], 'sale', -(int)$it['quantity'], $prev, $new, 'Marketplace order ' . $orderNo, 'order', $orderId, $me['id'], now()]);
                }

                // notify seller
                $shop = q1('SELECT user_id,name FROM shops WHERE id=?', [$g['shop_id']]);
                if ($shop) {
                    notify((int)$shop['user_id'], 'New order received! 🛒', 'Order ' . $orderNo . ' (' . money($subTotal + $shipping) . ') — please confirm.', 'order', '/seller/orders');
                }
                $created[] = ['id' => $orderId, 'no' => $orderNo, 'shop' => $shop['name'] ?? ''];
            }

            // coupon usage
            if ($totals['coupon']) {
                $c = q1('SELECT * FROM coupons WHERE code=?', [$totals['coupon']]);
                if ($c) {
                    q('UPDATE coupons SET used_count=used_count+1 WHERE id=?', [$c['id']]);
                    q('INSERT INTO coupon_usage (coupon_id,user_id,order_id,used_at) VALUES (?,?,?,?)', [$c['id'], $me['id'], $created[0]['id'], now()]);
                }
            }

            Cart::clear((int)$me['id']);
            unset($_SESSION['coupon']);
            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            error_log('[EK Checkout] ' . $e->getMessage());
            flash('danger', 'Could not place the order. Please try again.');
            back('/checkout');
        }

        $grand = money($totals['total']);
        send_app_mail($me['email'], 'Order Confirmed — ' . $groupNo, "Assalam-o-Alaikum {$me['name']}!\n\nYour order $groupNo has been placed successfully. Total: $grand. Payment: " . strtoupper($method) . ".\nTrack it anytime from your dashboard.", '/dashboard/orders', 'Track Order');
        notify($me['id'], 'Order placed successfully 🎉', 'Order ' . $groupNo . ' total ' . $grand . '. ' . ($method === 'bank_transfer' ? 'Please upload your payment receipt to proceed.' : 'Pay cash on delivery.'), 'order', '/order/' . $created[0]['id']);
        audit_log('order.created', 'order', $created[0]['id'], $groupNo);

        flash('success', 'Order placed successfully! 🎉');
        if ($method === 'bank_transfer') redirect('/payment/' . $created[0]['no']);
        redirect('/order/' . $created[0]['id']);
    }

    /* ---------------- payment proof ---------------- */
    public function paymentPage(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT * FROM orders WHERE order_number=? AND user_id=?', [$params['number'], $me['id']]);
        if (!$order) not_found();
        $payment = q1('SELECT * FROM payments WHERE order_id=? ORDER BY id DESC LIMIT 1', [$order['id']]);
        $banks = qa('SELECT * FROM bank_accounts WHERE status="active" ORDER BY sort_order');
        $this->view('checkout/payment', ['order' => $order, 'payment' => $payment, 'banks' => $banks,
            'seo' => ['title' => 'Payment — Order ' . $order['order_number'] . ' | eKamalia']]);
    }

    public function submitProof(array $params): void
    {
        $me = require_login();
        $order = q1('SELECT * FROM orders WHERE order_number=? AND user_id=?', [$params['number'], $me['id']]);
        if (!$order) not_found();
        $payment = q1('SELECT * FROM payments WHERE order_id=? ORDER BY id DESC LIMIT 1', [$order['id']]);
        if (!$payment) { flash('danger', 'No pending payment found.'); redirect('/dashboard/orders'); }
        if ($payment['status'] === 'verified') { flash('info', 'Payment already verified.'); redirect('/dashboard/orders'); }
        try {
            $proof = upload_image('proof', 'payments', (int)setting('max_upload_mb', 5));
        } catch (\RuntimeException $e) {
            flash('danger', 'Receipt: ' . $e->getMessage());
            back('/payment/' . $order['order_number']);
            return;
        }
        if (!$proof) { flash('danger', 'Please upload the receipt image.'); back('/payment/' . $order['order_number']); }
        q('UPDATE payments SET proof_image=?, transaction_id=?, reference_no=?, status="submitted", created_at=? WHERE id=?', [
            $proof, str_input('transaction_id', '', 90), 'PAY-' . strtoupper(substr(uniqid(), -8)), now(), $payment['id'],
        ]);
        q('UPDATE orders SET payment_status="submitted" WHERE id=?', [$order['id']]);
        notify_admins('Payment proof submitted', 'Order ' . $order['order_number'] . ' — ' . money($order['total']) . ' receipt uploaded, awaiting verification.', 'payment', '/admin/payments');
        flash('success', 'Receipt uploaded! We will verify your payment soon (usually within a few hours).');
        audit_log('payment.proof_submitted', 'order', $order['id'], $order['order_number']);
        redirect('/payment/' . $order['order_number']);
    }
}
