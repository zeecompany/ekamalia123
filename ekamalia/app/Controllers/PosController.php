<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * eKamalia Premium POS — browser-based point of sale.
 * Access is granted only after admin approval (pos_users).
 */
class PosController extends Controller
{
    private function shopId(): int
    {
        $pos = require_pos();
        return (int)($pos['shop_id'] ?? 0) ?: (int)(my_shop()['id'] ?? 0);
    }

    public function requestPage(): void
    {
        $me = require_login();
        $request = q1('SELECT r.*, p.name AS package_name FROM pos_requests r LEFT JOIN pos_packages p ON p.id=r.package_id WHERE r.user_id=? ORDER BY r.id DESC LIMIT 1', [$me['id']]);
        $packages = qa('SELECT * FROM pos_packages WHERE status="active"');
        $this->view('pos/request', ['request' => $request, 'packages' => $packages, 'seo' => ['title' => 'Premium POS for Your Business — eKamalia']]);
    }

    public function requestSubmit(): void
    {
        $me = require_login();
        if (q1('SELECT id FROM pos_requests WHERE user_id=? AND status="pending"', [$me['id']])) { flash('info', 'Your POS request is already pending review.'); back('/pos/request'); }
        if (q1('SELECT id FROM pos_users WHERE user_id=? AND status="active"', [$me['id']])) { flash('info', 'You already have POS access.'); redirect('/pos'); }
        $shop = my_shop();
        q('INSERT INTO pos_requests (user_id,shop_id,business_name,package_id,note,created_at) VALUES (?,?,?,?,?,?)',
            [$me['id'], $shop['id'] ?? null, str_input('business_name', '', 160) ?: ($shop['name'] ?? null), int_input('package_id') ?: null, str_input('note', '', 500), now()]);
        notify_admins('POS access request', $me['name'] . ' requested POS access.', 'pos', '/admin/pos-requests');
        send_app_mail($me['email'], 'POS Request Received — eKamalia', 'Your Premium POS request has been received. Our team will review and activate your account shortly.', '/pos/request');
        flash('success', 'POS request submitted! You will be notified once approved.');
        audit_log('pos.requested', 'user', (int)$me['id']);
        back('/pos/request');
    }

    /* ---------------- terminal ---------------- */
    public function index(): void
    {
        $pos = require_pos();
        if (!pos_can($pos, 'sales')) { flash('danger', 'No sales permission.'); redirect('/pos/dashboard'); }
        $sid = $this->shopId();
        $cats = qa('SELECT DISTINCT c.id,c.name,c.icon FROM categories c JOIN products p ON (p.category_id=c.id OR p.subcategory_id=c.id) WHERE p.shop_id=? AND p.status="published"', [$sid]);
        $q = trim(str_input('q', '', 80));
        $catId = int_input('cat');
        $conds = ['p.shop_id=?', 'p.status="published"', 'p.deleted_at IS NULL']; $params = [$sid];
        if ($q !== '') { $conds[] = '(p.name LIKE ? OR p.barcode=? OR p.sku=?)'; $params[] = "%$q%"; $params[] = $q; $params[] = $q; }
        if ($catId) { $conds[] = '(p.category_id=? OR p.subcategory_id=?)'; $params[] = $catId; $params[] = $catId; }
        $products = qa('SELECT p.*, (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                        FROM products p WHERE ' . implode(' AND ', $conds) . ' ORDER BY p.name LIMIT 100', $params);
        $customers = qa('SELECT * FROM pos_customers WHERE shop_id=? ORDER BY name LIMIT 200', [$sid]);
        $holds = qa('SELECT ps.*, u.name AS cashier_name, (SELECT COUNT(*) FROM pos_sale_items si WHERE si.sale_id=ps.id) AS items FROM pos_sales ps LEFT JOIN users u ON u.id=ps.cashier_id WHERE ps.shop_id=? AND ps.status="held" ORDER BY ps.created_at DESC LIMIT 20', [$sid]);
        /* a resumed hold: injected into the terminal cart client-side, then cleared */
        $resume = null;
        if (!empty($_SESSION['pos_resume'])) {
            $resume = $_SESSION['pos_resume'];
            unset($_SESSION['pos_resume']);
        }
        $this->view('pos/sale', ['pos' => $pos, 'products' => $products, 'cats' => $cats, 'customers' => $customers, 'holds' => $holds, 'q' => $q, 'catId' => $catId, 'resume' => $resume,
            'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Terminal — eKamalia']]);
    }

    public function checkout(): void
    {
        $pos = require_pos();
        if (!pos_can($pos, 'sales')) json_fail('No sales permission', 403);
        $sid = $this->shopId();
        $cart = json_decode(str_input('cart', '[]', 100000), true) ?: [];
        if (!$cart) json_fail('Cart is empty');
        $type = str_input('type', 'sale') === 'quotation' ? 'quotation' : 'sale';
        $hold = str_input('hold') === '1';
        $discount = max(0, float_input('discount'));
        $taxPercent = max(0, float_input('tax_percent'));
        $customerId = int_input('customer_id') ?: null;
        $payMethod = str_input('payment_method', 'cash');
        $paid = float_input('paid_amount');
        $note = str_input('note', '', 300);

        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        db()->beginTransaction();
        try {
            $subtotal = 0; $items = [];
            foreach ($cart as $line) {
                $p = q1('SELECT * FROM products WHERE id=? AND shop_id=? AND status="published"', [(int)($line['id'] ?? 0), $sid]);
                if (!$p) throw new \RuntimeException('A product in the cart is no longer available.');
                $qty = max(1, (int)($line['qty'] ?? 1));
                if ($type === 'sale' && !$hold && $qty > (int)$p['stock']) throw new \RuntimeException($p['name'] . ': only ' . $p['stock'] . ' in stock');
                $lineTotal = (float)$p['sale_price'] > 0 ? (float)$p['sale_price'] * $qty : (float)$p['price'] * $qty;
                $subtotal += $lineTotal;
                $items[] = ['p' => $p, 'qty' => $qty, 'total' => $lineTotal, 'price' => $lineTotal / $qty];
            }
            $afterDiscount = max(0, $subtotal - $discount);
            $tax = round($afterDiscount * $taxPercent / 100, 2);
            $total = round($afterDiscount + $tax, 2);
            $change = $type === 'sale' && !$hold ? max(0, $paid - $total) : 0;

            q('INSERT INTO pos_sales (shop_id,invoice_no,type,status,customer_id,cashier_id,subtotal,discount,tax,total,paid_amount,change_amount,payment_method,payment_detail,note,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $sid, $invoiceNo, $type, $hold ? 'held' : 'completed', $customerId, user_id(),
                round($subtotal, 2), $discount, $tax, $total,
                $hold ? 0 : min(max($paid, $type === 'sale' ? $total : $paid), 99999999), $change, $payMethod,
                str_input('payment_detail', '', 300) ?: null, $note ?: null, now(),
            ]);
            $saleId = last_id();
            foreach ($items as $it) {
                q('INSERT INTO pos_sale_items (sale_id,product_id,name,price,cost,quantity,total) VALUES (?,?,?,?,?,?,?)',
                    [$saleId, $it['p']['id'], $it['p']['name'], $it['price'], $it['p']['cost_price'], $it['qty'], $it['total']]);
                if ($type === 'sale' && !$hold) {
                    $new = max(0, (int)$it['p']['stock'] - $it['qty']);
                    q('UPDATE products SET stock=?, status=IF(stock<=0,"out_of_stock",status) WHERE id=?', [$new, $it['p']['id']]);
                    q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,reference_type,reference_id,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                        [$sid, $it['p']['id'], 'pos_sale', -$it['qty'], (int)$it['p']['stock'], $new, 'POS sale ' . $invoiceNo, 'pos_sale', $saleId, user_id(), now()]);
                }
            }
            if ($customerId && $type === 'sale' && !$hold) {
                q('UPDATE pos_customers SET total_purchases=total_purchases+? WHERE id=?', [$total, $customerId]);
                if ($paid < $total) q('UPDATE pos_customers SET balance=balance+? WHERE id=?', [$total - $paid, $customerId]);
            }
            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            json_fail($e->getMessage());
        }
        audit_log('pos.sale', 'pos_sale', $saleId, $invoiceNo . ' total ' . $total);
        json_ok(['sale_id' => $saleId, 'invoice_no' => $invoiceNo, 'total' => $total, 'change' => $change, 'type' => $type, 'message' => $hold ? 'Sale held.' : 'Sale completed ✓']);
    }

    public function resumeHold(array $params): void
    {
        $pos = require_pos();
        $sid = $this->shopId();
        $sale = q1('SELECT * FROM pos_sales WHERE id=? AND shop_id=? AND status="held"', [(int)$params['id'], $sid]);
        if (!$sale) not_found();
        /* capture held lines into the session cart BEFORE removing the hold row */
        $lines = qa('SELECT product_id, quantity, price, total FROM pos_sale_items WHERE sale_id=?', [$sale['id']]);
        $cart = [];
        foreach ($lines as $l) {
            $qty = max(1, (int)$l['quantity']);
            $cart[] = ['id' => (int)$l['product_id'], 'qty' => $qty,
                       'price' => $qty > 0 ? round((float)$l['total'] / $qty, 2) : (float)$l['price']];
        }
        q('DELETE FROM pos_sales WHERE id=?', [$sale['id']]); // items already captured above
        if ($cart) {
            $_SESSION['pos_resume'] = [
                'cart'        => $cart,
                'customer_id' => (int)($sale['customer_id'] ?? 0),
                'discount'    => (float)($sale['discount'] ?? 0),
                'tax_percent' => (float)($sale['tax_percent'] ?? 0),
            ];
            flash('success', 'Held sale ' . $sale['invoice_no'] . ' restored to cart.');
        } else {
            flash('warning', 'That held sale was empty and has been removed.');
        }
        redirect('/pos');
    }

    /* ---------------- invoices ---------------- */
    public function invoices(): void
    {
        $pos = require_pos();
        $sid = $this->shopId();
        $type = str_input('type', 'sale');
        $conds = ['ps.shop_id=?']; $params = [$sid];
        $conds[] = $type === 'quotation' ? 'ps.type="quotation"' : 'ps.type="sale"';
        if ($q = str_input('q', '', 40)) { $conds[] = '(ps.invoice_no LIKE ? OR ps.note LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        $sales = qa('SELECT ps.*, c.name AS customer_name, u.name AS cashier FROM pos_sales ps LEFT JOIN pos_customers c ON c.id=ps.customer_id LEFT JOIN users u ON u.id=ps.cashier_id
                     WHERE ' . implode(' AND ', $conds) . ' ORDER BY ps.created_at DESC LIMIT 200', $params);
        $this->view('pos/invoices', ['sales' => $sales, 'type' => $type, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Invoices — eKamalia']]);
    }

    public function invoice(array $params): void
    {
        $pos = require_pos();
        $sid = $this->shopId();
        $sale = q1('SELECT ps.*, c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address, u.name AS cashier_name
                    FROM pos_sales ps LEFT JOIN pos_customers c ON c.id=ps.customer_id LEFT JOIN users u ON u.id=ps.cashier_id
                    WHERE ps.id=? AND ps.shop_id=?', [(int)$params['id'], $sid]);
        if (!$sale) not_found();
        $items = qa('SELECT * FROM pos_sale_items WHERE sale_id=?', [$sale['id']]);
        $shop = q1('SELECT * FROM shops WHERE id=?', [$sid]);
        $this->view('pos/invoice-view', ['sale' => $sale, 'items' => $items, 'shop' => $shop, 'layout' => 'layouts/pos', 'seo' => ['title' => $sale['invoice_no'] . ' — Invoice']]);
    }

    public function invoicePrint(array $params): void
    {
        $pos = pos_access();
        if (!$pos) not_found();
        $sid = $this->shopId();
        $sale = q1('SELECT ps.*, c.name AS customer_name, c.phone AS customer_phone, u.name AS cashier_name
                    FROM pos_sales ps LEFT JOIN pos_customers c ON c.id=ps.customer_id LEFT JOIN users u ON u.id=ps.cashier_id
                    WHERE ps.id=? AND ps.shop_id=?', [(int)$params['id'], $sid]);
        if (!$sale) not_found();
        $items = qa('SELECT * FROM pos_sale_items WHERE sale_id=?', [$sale['id']]);
        $shop = q1('SELECT * FROM shops WHERE id=?', [$sid]);
        $this->view('pos/print', ['sale' => $sale, 'items' => $items, 'shop' => $shop, 'layout' => null]);
    }

    /* ---------------- returns ---------------- */
    public function returns(): void
    {
        $pos = require_pos();
        if (!pos_can($pos, 'returns') && !pos_can($pos, 'sales')) { flash('danger', 'No permission.'); redirect('/pos'); }
        $sid = $this->shopId();
        $returns = qa('SELECT pr.*, ps.invoice_no, u.name AS by_user FROM pos_returns pr JOIN pos_sales ps ON ps.id=pr.sale_id LEFT JOIN users u ON u.id=pr.created_by WHERE pr.shop_id=? ORDER BY pr.created_at DESC LIMIT 100', [$sid]);
        $this->view('pos/returns', ['returns' => $returns, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Sales Return — eKamalia']]);
    }

    public function returnFind(): void
    {
        $pos = require_pos();
        $sid = $this->shopId();
        $no = str_input('invoice_no', '', 30);
        $sale = q1('SELECT ps.*, c.name AS customer_name FROM pos_sales ps LEFT JOIN pos_customers c ON c.id=ps.customer_id WHERE ps.invoice_no=? AND ps.shop_id=? AND ps.status IN ("completed","partially_returned")', [$no, $sid]);
        if (!$sale) json_fail('Invoice not found or already fully returned.');
        $items = qa('SELECT * FROM pos_sale_items WHERE sale_id=?', [$sale['id']]);
        json_ok(['sale' => ['id' => (int)$sale['id'], 'invoice_no' => $sale['invoice_no'], 'total' => (float)$sale['total'], 'customer' => $sale['customer_name'], 'date' => $sale['created_at']],
                 'items' => array_map(fn($i) => ['id' => (int)$i['id'], 'name' => $i['name'], 'price' => (float)$i['price'], 'qty' => (int)$i['quantity'], 'returned' => (int)$i['returned_qty']], $items)]);
    }

    public function returnProcess(): void
    {
        $pos = require_pos();
        if (!pos_can($pos, 'returns') && $pos['role'] !== 'owner') json_fail('No permission', 403);
        $sid = $this->shopId();
        $saleId = int_input('sale_id');
        $items = json_decode(str_input('items', '[]'), true) ?: [];
        $reason = str_input('reason', '', 300);
        $refundMethod = str_input('refund_method', 'cash');
        $sale = q1('SELECT * FROM pos_sales WHERE id=? AND shop_id=? AND status IN ("completed","partially_returned")', [$saleId, $sid]);
        if (!$sale) json_fail('Invoice not found.');
        if (!$items) json_fail('Select at least one item to return.');
        db()->beginTransaction();
        try {
            $total = 0;
            $returnNo = 'RET-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
            q('INSERT INTO pos_returns (shop_id,sale_id,return_no,total,refund_method,reason,created_by,created_at) VALUES (?,?,?,?,?,?,?,?)',
                [$sid, $saleId, $returnNo, 0, $refundMethod, $reason ?: null, user_id(), now()]);
            $returnId = last_id();
            foreach ($items as $line) {
                $si = q1('SELECT * FROM pos_sale_items WHERE id=? AND sale_id=?', [(int)($line['id'] ?? 0), $saleId]);
                if (!$si) continue;
                $qty = min(max(1, (int)($line['qty'] ?? 1)), (int)$si['quantity'] - (int)$si['returned_qty']);
                if ($qty <= 0) continue;
                $amount = round((float)$si['price'] * $qty, 2);
                q('INSERT INTO pos_return_items (return_id,sale_item_id,product_id,quantity,amount) VALUES (?,?,?,?,?)', [$returnId, $si['id'], $si['product_id'], $qty, $amount]);
                q('UPDATE pos_sale_items SET returned_qty=returned_qty+? WHERE id=?', [$qty, $si['id']]);
                if ($si['product_id']) {
                    $p = q1('SELECT stock FROM products WHERE id=?', [$si['product_id']]);
                    if ($p) {
                        $new = (int)$p['stock'] + $qty;
                        q('UPDATE products SET stock=? WHERE id=?', [$new, $si['product_id']]);
                        q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,reference_type,reference_id,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                            [$sid, $si['product_id'], 'pos_return', $qty, (int)$p['stock'], $new, 'POS return ' . $returnNo, 'pos_return', $returnId, user_id(), now()]);
                    }
                }
                $total += $amount;
            }
            if ($total <= 0) throw new \RuntimeException('Nothing to return (quantities already returned).');
            q('UPDATE pos_returns SET total=? WHERE id=?', [$total, $returnId]);
            $allReturned = qv('SELECT COUNT(*) FROM pos_sale_items WHERE sale_id=? AND returned_qty<quantity', [$saleId]) == 0;
            q('UPDATE pos_sales SET status=? WHERE id=?', [$allReturned ? 'returned' : 'partially_returned', $saleId]);
            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            json_fail($e->getMessage());
        }
        audit_log('pos.return', 'pos_return', $returnId, $returnNo . ' amount ' . $total);
        json_ok(['message' => "Return $returnNo processed — refund " . money($total), 'return_id' => $returnId]);
    }

    /* ---------------- purchases ---------------- */
    public function purchases(): void
    {
        $pos = require_pos_perm('purchases');
        $sid = $this->shopId();
        $purchases = qa('SELECT pp.*, s.name AS supplier_name, u.name AS by_user, (SELECT COUNT(*) FROM pos_purchase_items WHERE purchase_id=pp.id) AS items
                         FROM pos_purchases pp LEFT JOIN pos_suppliers s ON s.id=pp.supplier_id LEFT JOIN users u ON u.id=pp.created_by
                         WHERE pp.shop_id=? ORDER BY pp.created_at DESC LIMIT 100', [$sid]);
        $suppliers = qa('SELECT * FROM pos_suppliers WHERE shop_id=? ORDER BY name', [$sid]);
        $products = qa('SELECT id,name,cost_price FROM products WHERE shop_id=? AND deleted_at IS NULL AND status<>"archived" ORDER BY name LIMIT 500', [$sid]);
        $this->view('pos/purchases', ['purchases' => $purchases, 'suppliers' => $suppliers, 'products' => $products, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Purchases — eKamalia']]);
    }

    public function purchaseSave(): void
    {
        $pos = require_pos_perm('purchases');
        $sid = $this->shopId();
        $supplierId = int_input('supplier_id') ?: null;
        $invoiceNo = str_input('invoice_no', '', 60);
        $date = str_input('purchase_date') ?: date('Y-m-d');
        $lines = json_decode(str_input('items', '[]'), true) ?: [];
        if (!$lines) { flash('danger', 'Add at least one product line.'); back('/pos/purchases'); }
        db()->beginTransaction();
        try {
            $subtotal = 0;
            foreach ($lines as $l) $subtotal += (float)($l['cost'] ?? 0) * max(1, (int)($l['qty'] ?? 1));
            $discount = float_input('discount');
            $tax = float_input('tax');
            $total = max(0, $subtotal - $discount + $tax);
            $paid = float_input('paid_amount');
            q('INSERT INTO pos_purchases (shop_id,supplier_id,invoice_no,purchase_date,subtotal,discount,tax,total,paid_amount,status,note,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $sid, $supplierId, $invoiceNo ?: null, $date, $subtotal, $discount, $tax, $total, $paid,
                str_input('status', 'completed'), str_input('note', '', 300) ?: null, user_id(), now(),
            ]);
            $pid = last_id();
            foreach ($lines as $l) {
                $product = q1('SELECT * FROM products WHERE id=? AND shop_id=?', [(int)($l['id'] ?? 0), $sid]);
                $qty = max(1, (int)($l['qty'] ?? 1));
                $cost = max(0, (float)($l['cost'] ?? 0));
                $name = $product['name'] ?? str_input('name_' . ($l['id'] ?? ''), 'Item', 190);
                q('INSERT INTO pos_purchase_items (purchase_id,product_id,name,cost,quantity,received_qty,total) VALUES (?,?,?,?,?,?,?)',
                    [$pid, $product['id'] ?? null, $name, $cost, $qty, $qty, $cost * $qty]);
                if ($product) {
                    $new = (int)$product['stock'] + $qty;
                    q('UPDATE products SET stock=?, cost_price=? WHERE id=?', [$new, $cost ?: $product['cost_price'], $product['id']]);
                    q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,reference_type,reference_id,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                        [$sid, $product['id'], 'purchase', $qty, (int)$product['stock'], $new, 'Purchase ' . ($invoiceNo ?: $pid), 'purchase', $pid, user_id(), now()]);
                }
            }
            if ($supplierId && $paid < $total) q('UPDATE pos_suppliers SET balance=balance+? WHERE id=?', [$total - $paid, $supplierId]);
            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('danger', 'Purchase failed: ' . $e->getMessage());
            back('/pos/purchases');
        }
        audit_log('pos.purchase', 'purchase', $pid, 'total ' . $total);
        flash('success', 'Purchase saved & stock updated.');
        back('/pos/purchases');
    }

    /* ---------------- customers & suppliers ---------------- */
    public function customers(): void
    {
        $pos = require_pos_perm('customers');
        $sid = $this->shopId();
        $q = str_input('q', '', 80);
        $conds = ['shop_id=?']; $params = [$sid];
        if ($q) { $conds[] = '(name LIKE ? OR phone LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        $customers = qa('SELECT * FROM pos_customers WHERE ' . implode(' AND ', $conds) . ' ORDER BY name LIMIT 300', $params);
        $this->view('pos/customers', ['customers' => $customers, 'q' => $q, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Customers — eKamalia']]);
    }

    public function customerSave(): void
    {
        $pos = require_pos_perm('customers');
        $sid = $this->shopId();
        $name = str_input('name', '', 140);
        if (mb_strlen($name) < 2) json_fail('Customer name required.');
        q('INSERT INTO pos_customers (shop_id,name,phone,email,address,note,balance,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
            [$sid, $name, str_input('phone', '', 20) ?: null, str_input('email', '', 190) ?: null, str_input('address', '', 255) ?: null, str_input('note', '', 300) ?: null, 0, user_id(), now()]);
        $cid = last_id();
        if (is_ajax()) json_ok(['id' => $cid, 'message' => 'Customer added']);
        flash('success', 'Customer added.');
        back('/pos/customers');
    }

    public function customerDelete(array $params): void
    {
        $pos = require_pos_perm('customers');
        q('DELETE FROM pos_customers WHERE id=? AND shop_id=?', [(int)$params['id'], $this->shopId()]);
        flash('success', 'Customer removed.');
        back('/pos/customers');
    }

    public function suppliers(): void
    {
        $pos = require_pos_perm('purchases');
        $sid = $this->shopId();
        $suppliers = qa('SELECT * FROM pos_suppliers WHERE shop_id=? ORDER BY name LIMIT 300', [$sid]);
        $this->view('pos/suppliers', ['suppliers' => $suppliers, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Suppliers — eKamalia']]);
    }

    public function supplierSave(): void
    {
        $pos = require_pos_perm('purchases');
        $name = str_input('name', '', 140);
        if (mb_strlen($name) < 2) json_fail('Supplier name required.');
        q('INSERT INTO pos_suppliers (shop_id,name,company,phone,email,address,payment_terms,balance,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
            [$this->shopId(), $name, str_input('company', '', 160) ?: null, str_input('phone', '', 20) ?: null, str_input('email', '', 190) ?: null,
             str_input('address', '', 255) ?: null, str_input('payment_terms', '', 190) ?: null, 0, now()]);
        is_ajax() ? json_ok(['message' => 'Supplier added']) : back('/pos/suppliers');
    }

    /* ---------------- expenses ---------------- */
    public function expenses(): void
    {
        $pos = require_pos_perm('expenses');
        $sid = $this->shopId();
        $expenses = qa('SELECT e.*, u.name AS by_user FROM pos_expenses e LEFT JOIN users u ON u.id=e.created_by WHERE e.shop_id=? ORDER BY e.expense_date DESC LIMIT 200', [$sid]);
        $totalMonth = (float)qv('SELECT COALESCE(SUM(amount),0) FROM pos_expenses WHERE shop_id=? AND expense_date >= DATE_FORMAT(NOW(),"%Y-%m-01")', [$sid]);
        $this->view('pos/expenses', ['expenses' => $expenses, 'totalMonth' => $totalMonth, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Expenses — eKamalia']]);
    }

    public function expenseSave(): void
    {
        $pos = require_pos_perm('expenses');
        $amount = float_input('amount');
        if ($amount <= 0) { flash('danger', 'Enter a valid amount.'); back('/pos/expenses'); }
        q('INSERT INTO pos_expenses (shop_id,category,amount,note,expense_date,created_by,created_at) VALUES (?,?,?,?,?,?,?)',
            [$this->shopId(), str_input('category', 'general', 80), $amount, str_input('note', '', 300) ?: null, str_input('expense_date') ?: date('Y-m-d'), user_id(), now()]);
        flash('success', 'Expense recorded.');
        back('/pos/expenses');
    }

    public function expenseDelete(array $params): void
    {
        $pos = require_pos_perm('expenses');
        q('DELETE FROM pos_expenses WHERE id=? AND shop_id=?', [(int)$params['id'], $this->shopId()]);
        flash('success', 'Expense deleted.');
        back('/pos/expenses');
    }

    /* ---------------- inventory ---------------- */
    public function inventory(): void
    {
        $pos = require_pos_perm('inventory');
        $sid = $this->shopId();
        $products = qa('SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.shop_id=? AND p.deleted_at IS NULL AND p.status<>"archived" ORDER BY (p.stock<=p.min_stock) DESC, p.name LIMIT 400', [$sid]);
        $movements = qa('SELECT im.*, p.name AS product_name, u.name AS by_user FROM inventory_movements im JOIN products p ON p.id=im.product_id LEFT JOIN users u ON u.id=im.user_id WHERE im.shop_id=? ORDER BY im.created_at DESC LIMIT 100', [$sid]);
        $lowCount = (int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND stock<=min_stock AND status="published"', [$sid]);
        $this->view('pos/inventory', ['products' => $products, 'movements' => $movements, 'lowCount' => $lowCount, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Inventory — eKamalia']]);
    }

    public function adjustStock(): void
    {
        $pos = require_pos_perm('inventory');
        $sid = $this->shopId();
        $p = q1('SELECT * FROM products WHERE id=? AND shop_id=?', [int_input('product_id'), $sid]);
        if (!$p) json_fail('Product not found', 404);
        $type = str_input('type', 'adjustment');
        if (!in_array($type, ['adjustment', 'damage', 'stock_in', 'stock_out', 'transfer'], true)) $type = 'adjustment';
        $qty = abs(int_input('quantity'));
        if ($qty < 1) json_fail('Enter quantity.');
        $sign = in_array($type, ['stock_in'], true) ? 1 : (in_array($type, ['stock_out', 'damage'], true) ? -1 : (int_input('sign', 1) >= 0 ? 1 : -1));
        $new = max(0, (int)$p['stock'] + $sign * $qty);
        q('UPDATE products SET stock=?, status=IF(stock<=0 AND status="published","out_of_stock",IF(? > 0 AND status="out_of_stock","published",status)) WHERE id=?', [$new, $new, $p['id']]);
        q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
            [$sid, $p['id'], $type, $sign * $qty, (int)$p['stock'], $new, str_input('reason', 'Manual ' . $type, 190), user_id(), now()]);
        if (is_ajax()) json_ok(['message' => 'Stock updated: ' . $p['name'] . ' → ' . $new]);
        flash('success', $p['name'] . ' stock updated to ' . $new . '.');
        back('/pos/inventory');
    }

    /* ---------------- dashboard & reports ---------------- */
    public function dashboard(): void
    {
        $pos = require_pos();
        $sid = $this->shopId();
        $today = date('Y-m-d');
        $stats = [
            'today_sales' => (float)qv('SELECT COALESCE(SUM(total),0) FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at)=?', [$sid, $today]),
            'today_count' => (int)qv('SELECT COUNT(*) FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at)=?', [$sid, $today]),
            'month_sales' => (float)qv('SELECT COALESCE(SUM(total),0) FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND created_at>=DATE_FORMAT(NOW(),"%Y-%m-01")', [$sid]),
            'month_profit' => (float)qv('SELECT COALESCE(SUM((si.price-si.cost)*si.quantity),0) FROM pos_sale_items si JOIN pos_sales ps ON ps.id=si.sale_id WHERE ps.shop_id=? AND ps.type="sale" AND ps.status="completed" AND ps.created_at>=DATE_FORMAT(NOW(),"%Y-%m-01")', [$sid]),
            'customers' => (int)qv('SELECT COUNT(*) FROM pos_customers WHERE shop_id=?', [$sid]),
            'receivables' => (float)qv('SELECT COALESCE(SUM(balance),0) FROM pos_customers WHERE shop_id=?', [$sid]),
            'payables' => (float)qv('SELECT COALESCE(SUM(balance),0) FROM pos_suppliers WHERE shop_id=?', [$sid]),
            'low_stock' => (int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND stock<=min_stock AND status="published"', [$sid]),
        ];
        $daily = qa('SELECT DATE(created_at) d, COALESCE(SUM(total),0) t FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND created_at>=DATE_SUB(NOW(), INTERVAL 13 DAY) GROUP BY DATE(created_at) ORDER BY d', [$sid]);
        $top = qa('SELECT si.name, SUM(si.quantity) q, SUM(si.total) amt FROM pos_sale_items si JOIN pos_sales ps ON ps.id=si.sale_id WHERE ps.shop_id=? AND ps.type="sale" AND ps.status="completed" GROUP BY si.name ORDER BY amt DESC LIMIT 6', [$sid]);
        $this->view('pos/dashboard', ['pos' => $pos, 'stats' => $stats, 'daily' => $daily, 'top' => $top, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Dashboard — eKamalia']]);
    }

    public function reports(): void
    {
        $pos = require_pos_perm('reports');
        $sid = $this->shopId();
        $from = str_input('from') ?: date('Y-m-01');
        $to = str_input('to') ?: date('Y-m-d');
        $salesByDay = qa('SELECT DATE(created_at) d, COUNT(*) c, COALESCE(SUM(total),0) t FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY d', [$sid, $from, $to]);
        $totals = [
            'sales' => (float)qv('SELECT COALESCE(SUM(total),0) FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at) BETWEEN ? AND ?', [$sid, $from, $to]),
            'profit' => (float)qv('SELECT COALESCE(SUM((si.price-si.cost)*si.quantity),0) FROM pos_sale_items si JOIN pos_sales ps ON ps.id=si.sale_id WHERE ps.shop_id=? AND ps.type="sale" AND ps.status="completed" AND DATE(ps.created_at) BETWEEN ? AND ?', [$sid, $from, $to]),
            'purchases' => (float)qv('SELECT COALESCE(SUM(total),0) FROM pos_purchases WHERE shop_id=? AND status<>"cancelled" AND purchase_date BETWEEN ? AND ?', [$sid, $from, $to]),
            'expenses' => (float)qv('SELECT COALESCE(SUM(amount),0) FROM pos_expenses WHERE shop_id=? AND expense_date BETWEEN ? AND ?', [$sid, $from, $to]),
            'returns' => (float)qv('SELECT COALESCE(SUM(total),0) FROM pos_returns WHERE shop_id=? AND DATE(created_at) BETWEEN ? AND ?', [$sid, $from, $to]),
        ];
        $byPayment = qa('SELECT payment_method, COUNT(*) c, COALESCE(SUM(total),0) t FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at) BETWEEN ? AND ? GROUP BY payment_method', [$sid, $from, $to]);
        $topProducts = qa('SELECT si.name, SUM(si.quantity) q, SUM(si.total) amt, SUM((si.price-si.cost)*si.quantity) profit FROM pos_sale_items si JOIN pos_sales ps ON ps.id=si.sale_id WHERE ps.shop_id=? AND ps.type="sale" AND ps.status="completed" AND DATE(ps.created_at) BETWEEN ? AND ? GROUP BY si.name ORDER BY amt DESC LIMIT 10', [$sid, $from, $to]);
        $this->view('pos/reports', ['salesByDay' => $salesByDay, 'totals' => $totals, 'byPayment' => $byPayment, 'topProducts' => $topProducts,
            'from' => $from, 'to' => $to, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Reports — eKamalia']]);
    }

    /* reports CSV export */
    public function reportExport(): void
    {
        $pos = require_pos_perm('reports');
        $sid = $this->shopId();
        $from = str_input('from') ?: date('Y-m-01');
        $to = str_input('to') ?: date('Y-m-d');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ekamalia-pos-report-' . $from . '_to_' . $to . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['eKamalia POS Report', $from . ' to ' . $to]);
        fputcsv($out, []);
        fputcsv($out, ['Date', 'Invoices', 'Sales Total']);
        foreach (qa('SELECT DATE(created_at) d, COUNT(*) c, SUM(total) t FROM pos_sales WHERE shop_id=? AND type="sale" AND status="completed" AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY d', [$sid, $from, $to]) as $r) {
            fputcsv($out, [$r['d'], $r['c'], $r['t']]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Product', 'Qty Sold', 'Revenue', 'Profit']);
        foreach (qa('SELECT si.name, SUM(si.quantity) q, SUM(si.total) amt, SUM((si.price-si.cost)*si.quantity) profit FROM pos_sale_items si JOIN pos_sales ps ON ps.id=si.sale_id WHERE ps.shop_id=? AND ps.type="sale" AND ps.status="completed" AND DATE(ps.created_at) BETWEEN ? AND ? GROUP BY si.name ORDER BY amt DESC', [$sid, $from, $to]) as $r) {
            fputcsv($out, [$r['name'], $r['q'], $r['amt'], $r['profit']]);
        }
        fclose($out);
        exit;
    }

    /* ---------------- staff ---------------- */
    public function staff(): void
    {
        $pos = require_pos();
        if ($pos['role'] !== 'owner') { flash('danger', 'Only the owner can manage staff.'); redirect('/pos/dashboard'); }
        $sid = $this->shopId();
        $staff = qa('SELECT pu.*, u.name, u.email, u.phone FROM pos_users pu JOIN users u ON u.id=pu.user_id WHERE (pu.shop_id=? OR pu.user_id=?) ORDER BY pu.role', [$sid, $sid]);
        $users = qa('SELECT id,name,email FROM users WHERE status="active" AND id<>? AND id NOT IN (SELECT user_id FROM pos_users WHERE shop_id=?) ORDER BY name LIMIT 300', [auth()['id'], $sid]);
        $this->view('pos/staff', ['staff' => $staff, 'users' => $users, 'layout' => 'layouts/pos', 'seo' => ['title' => 'POS Staff — eKamalia']]);
    }

    public function staffAdd(): void
    {
        $pos = require_pos();
        if ($pos['role'] !== 'owner') json_fail('Only owner can add staff', 403);
        $sid = $this->shopId();
        $userId = int_input('user_id');
        $role = str_input('role', 'cashier');
        $preset = ['manager' => ['sales', 'inventory', 'purchases', 'reports', 'customers', 'returns'],
                   'cashier' => ['sales', 'customers'],
                   'inventory' => ['inventory', 'purchases'],
                   'sales' => ['sales']][$role] ?? ['sales'];
        $perms = input('permissions') && is_array($_POST['permissions']) ? array_values(array_intersect($_POST['permissions'], ['sales', 'inventory', 'purchases', 'reports', 'customers', 'returns', 'expenses'])) : $preset;
        if (!$userId || !q1('SELECT id FROM users WHERE id=?', [$userId])) json_fail('Select a valid user');
        if (q1('SELECT id FROM pos_users WHERE user_id=? AND (shop_id=? OR user_id=?)', [$userId, $sid, $sid])) json_fail('This user already has POS access here');
        q('INSERT INTO pos_users (user_id,shop_id,role,permissions,status,created_at) VALUES (?,?,?,?,?,?)', [$userId, $sid, $role, json_encode($perms), 'active', now()]);
        notify($userId, 'POS access granted 🎉', 'You have been added to the POS team as ' . $role . '.', 'pos', '/pos');
        is_ajax() ? json_ok(['message' => 'Staff member added']) : back('/pos/staff');
    }

    public function staffUpdate(): void
    {
        $pos = require_pos();
        if ($pos['role'] !== 'owner') json_fail('Only owner', 403);
        $id = int_input('id');
        $row = q1('SELECT * FROM pos_users WHERE id=? AND (shop_id=? OR user_id=?)', [$id, $this->shopId(), $this->shopId()]);
        if (!$row) json_fail('Not found', 404);
        if ((int)$row['user_id'] === user_id()) json_fail('You cannot change your own owner access');
        $action = str_input('action');
        if ($action === 'suspend') { q('UPDATE pos_users SET status="suspended" WHERE id=?', [$id]); $msg = 'Access suspended'; }
        elseif ($action === 'activate') { q('UPDATE pos_users SET status="active" WHERE id=?', [$id]); $msg = 'Access activated'; }
        elseif ($action === 'remove') { q('DELETE FROM pos_users WHERE id=?', [$id]); $msg = 'Access removed'; }
        elseif ($action === 'role') {
            $role = str_input('role', 'cashier');
            if (!in_array($role, ['manager', 'cashier', 'inventory', 'sales'], true)) json_fail('Invalid role');
            q('UPDATE pos_users SET role=? WHERE id=?', [$role, $id]);
            $msg = 'Role updated';
        } else json_fail('Unknown action');
        if (is_ajax()) json_ok(['message' => $msg]);
        back('/pos/staff');
    }
}
