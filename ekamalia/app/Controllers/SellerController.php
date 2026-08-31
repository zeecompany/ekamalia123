<?php
declare(strict_types=1);

namespace App\Controllers;

/** Shop owner dashboard */
class SellerController extends Controller
{
    protected string $defaultLayout = 'layouts/dashboard';
    private function shop(): array { return require_shop(); }

    public function index(): void
    {
        $shop = $this->shop();
        $sid = (int)$shop['id'];
        $stats = [
            'products' => (int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND deleted_at IS NULL', [$sid]),
            'pending' => (int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND status="pending"', [$sid]),
            'orders' => (int)qv('SELECT COUNT(*) FROM orders WHERE shop_id=?', [$sid]),
            'new_orders' => (int)qv('SELECT COUNT(*) FROM orders WHERE shop_id=? AND status="pending"', [$sid]),
            'revenue' => (float)qv('SELECT COALESCE(SUM(total),0) FROM orders WHERE shop_id=? AND status IN ("delivered","completed")', [$sid]),
            'followers' => (int)$shop['followers_count'],
            'views' => (int)$shop['views'],
            'low_stock' => (int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND stock<=min_stock AND status="published"', [$sid]),
        ];
        $recentOrders = qa('SELECT o.*, u.name AS buyer_name FROM orders o JOIN users u ON u.id=o.user_id WHERE o.shop_id=? ORDER BY o.created_at DESC LIMIT 8', [$sid]);
        $sales7 = qa('SELECT DATE(created_at) d, COALESCE(SUM(total),0) t, COUNT(*) c FROM orders WHERE shop_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 13 DAY) GROUP BY DATE(created_at) ORDER BY d', [$sid]);
        $topProducts = qa('SELECT p.name, COALESCE(SUM(oi.quantity),0) q, COALESCE(SUM(oi.total),0) amt FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN products p ON p.id=oi.product_id WHERE o.shop_id=? AND o.status IN ("delivered","completed") GROUP BY p.id ORDER BY amt DESC LIMIT 5', [$sid]);
        $lowStock = qa('SELECT id,name,stock,min_stock FROM products WHERE shop_id=? AND stock<=min_stock AND status="published" AND deleted_at IS NULL LIMIT 8', [$sid]);
        $this->view('seller/index', ['shop' => $shop, 'stats' => $stats, 'recentOrders' => $recentOrders, 'sales7' => $sales7, 'topProducts' => $topProducts, 'lowStock' => $lowStock,
            'seo' => ['title' => 'Seller Dashboard — eKamalia']]);
    }

    /* ---------------- products ---------------- */
    public function products(): void
    {
        $shop = $this->shop();
        $q = str_input('q', '', 100);
        $status = str_input('status', '');
        $conds = ['p.shop_id=?', 'p.deleted_at IS NULL']; $params = [$shop['id']];
        if ($q !== '') { $conds[] = '(p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if (in_array($status, ['draft', 'pending', 'published', 'hidden', 'out_of_stock', 'archived'], true)) { $conds[] = 'p.status=?'; $params[] = $status; }
        $products = qa('SELECT p.*, c.name AS cat_name, (SELECT image FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                        FROM products p LEFT JOIN categories c ON c.id=p.category_id
                        WHERE ' . implode(' AND ', $conds) . ' ORDER BY p.created_at DESC LIMIT 200', $params);
        $this->view('seller/products', ['shop' => $shop, 'products' => $products, 'q' => $q, 'status' => $status,
            'seo' => ['title' => 'My Products — eKamalia Seller']]);
    }

    public function productForm(array $params = []): void
    {
        $shop = $this->shop();
        $product = null;
        if (!empty($params['id'])) {
            $product = q1('SELECT * FROM products WHERE id=? AND shop_id=? AND deleted_at IS NULL', [(int)$params['id'], $shop['id']]);
            if (!$product) not_found();
        }
        $cats = qa('SELECT * FROM categories WHERE parent_id IS NULL AND type IN ("both","product") AND status="active" ORDER BY sort_order');
        $brands = qa('SELECT * FROM brands WHERE status="active" ORDER BY name');
        $this->view('seller/product-form', ['shop' => $shop, 'product' => $product, 'cats' => $cats, 'brands' => $brands,
            'seo' => ['title' => ($product ? 'Edit' : 'Add') . ' Product — eKamalia Seller']]);
    }

    public function productSave(): void
    {
        $shop = $this->shop();
        $id = int_input('id');
        $product = $id ? q1('SELECT * FROM products WHERE id=? AND shop_id=?', [$id, $shop['id']]) : null;
        $name = str_input('name', '', 190);
        if (mb_strlen($name) < 3) { stash_old(); flash('danger', 'Product name is required.'); back('/seller/products/create'); }
        $price = float_input('price');
        if ($price <= 0) { stash_old(); flash('danger', 'Enter a valid selling price.'); back('/seller/products/create'); }
        $salePrice = float_input('sale_price') ?: null;
        if ($salePrice && $salePrice >= $price) { $salePrice = null; }

        // images
        $images = [];
        try {
            if (!empty($_FILES['images']['name'][0])) {
                $files = $_FILES['images'];
                for ($i = 0; $i < min(count($files['name']), 6); $i++) {
                    if (empty($files['name'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $_FILES['__one'] = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    $rel = upload_image('__one', 'products', (int)setting('max_upload_mb', 5));
                    if ($rel) $images[] = $rel;
                }
                unset($_FILES['__one']);
            }
        } catch (\RuntimeException $e) { stash_old(); flash('danger', 'Image: ' . $e->getMessage()); back('/seller/products/create'); }

        $stock = max(0, int_input('stock'));
        $status = str_input('status', $product ? $product['status'] : 'published');
        if (!in_array($status, ['draft', 'published', 'hidden'], true)) $status = 'published';
        if ($product && $product['status'] === 'published' && $status === 'published') $status = 'published';
        $needsApproval = setting('products_require_approval') === '1' && (!$product || $product['status'] !== 'published');
        $finalStatus = ($status === 'published' && $needsApproval) ? 'pending' : $status;

        $fields = [mb_substr($name, 0, 190), str_input('sku', '', 60) ?: null, str_input('barcode', '', 60) ?: null,
            int_input('category_id') ?: null, int_input('subcategory_id') ?: null, int_input('brand_id') ?: null,
            str_input('description', '', 20000), mb_substr(str_input('short_description', '', 300), 0, 300),
            $price, $salePrice, float_input('cost_price'), $stock, max(0, int_input('min_stock', 3)),
            str_input('unit', 'pcs', 30), float_input('shipping_fee'), str_input('delivery_time', '', 90),
            str_input('warranty', '', 190), str_input('return_policy', '', 190),
            filter_var(str_input('video_url'), FILTER_VALIDATE_URL) ?: null, str_input('weight', '', 30), str_input('dimensions', '', 60),
            float_input('tax_percent'), str_input('tags', '', 250), $finalStatus];

        if ($product) {
            $fields[] = $product['id'];
            q('UPDATE products SET name=?,sku=?,barcode=?,category_id=?,subcategory_id=?,brand_id=?,description=?,short_description=?,price=?,sale_price=?,cost_price=?,stock=?,min_stock=?,unit=?,shipping_fee=?,delivery_time=?,warranty=?,return_policy=?,video_url=?,weight=?,dimensions=?,tax_percent=?,tags=?,status=? WHERE id=?', $fields);
            $pid = (int)$product['id'];
            if ($images) {
                foreach (qa('SELECT image FROM product_images WHERE product_id=?', [$pid]) as $im) delete_upload($im['image']);
                q('DELETE FROM product_images WHERE product_id=?', [$pid]);
            }
            if ((int)$product['stock'] !== $stock) {
                q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
                    [$shop['id'], $pid, 'adjustment', $stock - (int)$product['stock'], (int)$product['stock'], $stock, 'Manual edit via dashboard', user_id(), now()]);
            }
        } else {
            $slug = unique_slug('products', $name);
            q('INSERT INTO products (shop_id,name,slug,sku,barcode,category_id,subcategory_id,brand_id,description,short_description,price,sale_price,cost_price,stock,min_stock,unit,shipping_fee,delivery_time,warranty,return_policy,video_url,weight,dimensions,tax_percent,tags,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array_merge([$shop['id'], $name, $slug], array_slice($fields, 1), [now()]));
            $pid = last_id();
            if ($stock > 0) {
                q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
                    [$shop['id'], $pid, 'opening', $stock, 0, $stock, 'Product created', user_id(), now()]);
            }
        }
        foreach ($images as $i => $img) q('INSERT INTO product_images (product_id,image,sort_order) VALUES (?,?,?)', [$pid, $img, $i]);
        clear_old();
        if ($finalStatus === 'pending') notify_admins('Product pending approval', $name . ' — ' . $shop['name'], 'moderation', '/admin/products?status=pending');
        flash('success', $product ? 'Product updated.' : ($finalStatus === 'pending' ? 'Product submitted for approval.' : 'Product published!'));
        audit_log($product ? 'product.updated' : 'product.created', 'product', $pid, $name);
        redirect('/seller/products');
    }

    public function productAction(array $params): void
    {
        $shop = $this->shop();
        $p = q1('SELECT * FROM products WHERE id=? AND shop_id=? AND deleted_at IS NULL', [(int)$params['id'], $shop['id']]);
        if (!$p) json_fail('Not found', 404);
        $action = str_input('action', $params['action'] ?? '');
        switch ($action) {
            case 'hide': q('UPDATE products SET status="hidden" WHERE id=?', [$p['id']]); $msg = 'Product hidden.'; break;
            case 'publish':
                if (setting('products_require_approval') === '1' && $p['status'] !== 'published') {
                    q('UPDATE products SET status="pending" WHERE id=?', [$p['id']]);
                    notify_admins('Product pending approval', $p['name'], 'moderation', '/admin/products?status=pending');
                    $msg = 'Submitted for approval.';
                } else { q('UPDATE products SET status="published" WHERE id=?', [$p['id']]); $msg = 'Product published.'; }
                break;
            case 'archive': q('UPDATE products SET status="archived" WHERE id=?', [$p['id']]); $msg = 'Product archived.'; break;
            case 'delete':
                q('UPDATE products SET deleted_at=?, status="archived" WHERE id=?', [now(), $p['id']]);
                $msg = 'Product deleted.';
                audit_log('product.deleted', 'product', $p['id'], $p['name']);
                break;
            case 'feature':
                q('INSERT INTO promotion_requests (user_id,type,item_id,status,created_at) VALUES (?,?,?,?,?)', [user_id(), 'product_featured', $p['id'], 'pending', now()]);
                notify_admins('Feature request', $shop['name'] . ' requested featuring: ' . $p['name'], 'moderation', '/admin/promotions');
                $msg = 'Feature request sent to admin.';
                break;
            default: json_fail('Unknown action');
        }
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/seller/products');
    }

    /* ---------------- orders ---------------- */
    public function orders(): void
    {
        $shop = $this->shop();
        $status = str_input('status', '');
        $conds = ['o.shop_id=?']; $params = [$shop['id']];
        if (in_array($status, order_statuses(), true)) { $conds[] = 'o.status=?'; $params[] = $status; }
        if ($q = str_input('q', '', 40)) { $conds[] = 'o.order_number LIKE ?'; $params[] = "%$q%"; }
        $orders = qa('SELECT o.*, u.name AS buyer_name, u.phone AS buyer_phone FROM orders o JOIN users u ON u.id=o.user_id
                      WHERE ' . implode(' AND ', $conds) . ' ORDER BY o.created_at DESC LIMIT 200', $params);
        $counts = [];
        foreach (qa('SELECT status, COUNT(*) c FROM orders WHERE shop_id=? GROUP BY status', [$shop['id']]) as $r) $counts[$r['status']] = (int)$r['c'];
        $this->view('seller/orders', ['shop' => $shop, 'orders' => $orders, 'status' => $status, 'counts' => $counts,
            'seo' => ['title' => 'Shop Orders — eKamalia Seller']]);
    }

    public function orderStatus(): void
    {
        $shop = $this->shop();
        $order = q1('SELECT * FROM orders WHERE id=? AND shop_id=?', [int_input('order_id'), $shop['id']]);
        if (!$order) json_fail('Order not found', 404);
        $new = str_input('status');
        if (!in_array($new, order_statuses(), true)) json_fail('Invalid status');
        $allowedFlow = ['pending', 'confirmed', 'processing', 'packed', 'dispatched', 'out_for_delivery', 'delivered'];
        $curIdx = array_search($order['status'], $allowedFlow, true);
        $newIdx = array_search($new, $allowedFlow, true);
        $special = ['cancelled', 'returned', 'refunded', 'refund_requested'];
        if ($newIdx !== false && $curIdx !== false && $newIdx < $curIdx && !in_array($new, ['processing'], true) && $order['status'] !== 'pending') {
            json_fail('Cannot move order backwards from "' . order_status_label($order['status']) . '"');
        }
        if (in_array($order['status'], $special, true) && !in_array($new, ['refunded'], true)) {
            json_fail('This order is ' . $order['status'] . ' and locked.');
        }
        $note = str_input('note', '', 300) ?: 'Status updated by seller';
        OrderController::transition($order, $new, $note, user_id(), in_array($new, ['cancelled', 'returned', 'refunded'], true));
        if (is_ajax()) json_ok(['message' => 'Order updated to "' . order_status_label($new) . '"']);
        flash('success', 'Order updated to "' . order_status_label($new) . '".');
        back('/seller/orders');
    }

    /* ---------------- coupons ---------------- */
    public function coupons(): void
    {
        $shop = $this->shop();
        $coupons = qa('SELECT c.*, (SELECT COUNT(*) FROM coupon_usage cu WHERE cu.coupon_id=c.id) AS used FROM coupons c WHERE c.shop_id=? ORDER BY c.id DESC', [$shop['id']]);
        $this->view('seller/coupons', ['shop' => $shop, 'coupons' => $coupons, 'seo' => ['title' => 'Shop Coupons — eKamalia Seller']]);
    }

    public function couponSave(): void
    {
        $shop = $this->shop();
        $code = strtoupper(str_input('code', '', 40));
        $type = str_input('type', 'percent');
        $value = float_input('value');
        if (!preg_match('/^[A-Z0-9]{4,20}$/', $code)) { flash('danger', 'Coupon code: 4-20 letters/numbers.'); back('/seller/coupons'); }
        if (!in_array($type, ['percent', 'fixed', 'free_delivery'], true)) $type = 'percent';
        if ($type !== 'free_delivery' && $value <= 0) { flash('danger', 'Enter a discount value.'); back('/seller/coupons'); }
        if (q1('SELECT id FROM coupons WHERE code=?', [$code])) { flash('danger', 'This coupon code is already taken.'); back('/seller/coupons'); }
        q('INSERT INTO coupons (shop_id,code,type,value,min_order,max_uses,expires_at,status,created_at) VALUES (?,?,?,?,?,?,?,?,?)', [
            $shop['id'], $code, $type, $value, float_input('min_order'), max(0, int_input('max_uses', 100)),
            str_input('expires_at') ?: null, 'active', now(),
        ]);
        flash('success', "Coupon $code created!");
        back('/seller/coupons');
    }

    public function couponDelete(array $params): void
    {
        $shop = $this->shop();
        q('DELETE FROM coupons WHERE id=? AND shop_id=?', [(int)$params['id'], $shop['id']]);
        flash('success', 'Coupon removed.');
        back('/seller/coupons');
    }

    /* ---------------- reviews ---------------- */
    public function reviews(): void
    {
        $shop = $this->shop();
        $reviews = qa('SELECT r.*, u.name AS user_name, u.avatar, p.name AS product_name FROM reviews r
                       JOIN users u ON u.id=r.user_id
                       LEFT JOIN products p ON r.item_type="product" AND p.id=r.item_id
                       WHERE (r.item_type="shop" AND r.item_id=?) OR (r.item_type="product" AND r.item_id IN (SELECT id FROM products WHERE shop_id=?))
                       ORDER BY r.created_at DESC LIMIT 100', [$shop['id'], $shop['id']]);
        $this->view('seller/reviews', ['shop' => $shop, 'reviews' => $reviews, 'seo' => ['title' => 'Shop Reviews — eKamalia Seller']]);
    }

    /* ---------------- analytics ---------------- */
    public function analytics(): void
    {
        $shop = $this->shop();
        $sid = $shop['id'];
        $daily = qa('SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue, COUNT(*) orders FROM orders WHERE shop_id=? AND created_at>=DATE_SUB(NOW(), INTERVAL 29 DAY) GROUP BY DATE(created_at) ORDER BY d', [$sid]);
        $productViews = qa('SELECT name, views FROM products WHERE shop_id=? AND deleted_at IS NULL ORDER BY views DESC LIMIT 8', [$sid]);
        $statusMix = qa('SELECT status, COUNT(*) c FROM orders WHERE shop_id=? GROUP BY status', [$sid]);
        $topCities = qa('SELECT ship_city, COUNT(*) c FROM orders WHERE shop_id=? AND ship_city<>"" GROUP BY ship_city ORDER BY c DESC LIMIT 6', [$sid]);
        $this->view('seller/analytics', ['shop' => $shop, 'daily' => $daily, 'productViews' => $productViews, 'statusMix' => $statusMix, 'topCities' => $topCities,
            'seo' => ['title' => 'Shop Analytics — eKamalia Seller']]);
    }

    /* ---------------- settings ---------------- */
    public function settings(): void
    {
        $shop = $this->shop();
        $cats = qa('SELECT * FROM categories WHERE type IN ("business","both") AND parent_id IS NULL AND status="active" ORDER BY sort_order');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $this->view('seller/settings', ['shop' => $shop, 'cats' => $cats, 'cities' => $cities, 'seo' => ['title' => 'Shop Settings — eKamalia Seller']]);
    }

    public function settingsSave(): void
    {
        $shop = $this->shop();
        try {
            $logo = upload_image('logo', 'shops', 5);
            $cover = upload_image('cover', 'shops', 5);
        } catch (\RuntimeException $e) { flash('danger', $e->getMessage()); back('/seller/settings'); }
        q('UPDATE shops SET name=?, owner_name=?, email=?, phone=?, whatsapp=?, address=?, city_id=?, area=?, description=?, category_id=?, business_hours=?, delivery_available=?, delivery_fee=?, bank_info=? WHERE id=?', [
            str_input('name', '', 160), str_input('owner_name', '', 140), strtolower(str_input('email', '', 190)) ?: null,
            preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null,
            preg_replace('/\D/', '', str_input('whatsapp', '', 20)) ?: null,
            str_input('address', '', 255), int_input('city_id') ?: null, str_input('area', '', 140),
            str_input('description', '', 4000), int_input('category_id') ?: null, str_input('business_hours', '', 190),
            input('delivery_available') ? 1 : 0, float_input('delivery_fee'), str_input('bank_info', '', 1000), $shop['id'],
        ]);
        if ($logo) { delete_upload($shop['logo']); q('UPDATE shops SET logo=? WHERE id=?', [$logo, $shop['id']]); }
        if ($cover) q('UPDATE shops SET cover=? WHERE id=?', [$cover, $shop['id']]);
        flash('success', 'Shop settings saved.');
        audit_log('shop.settings_updated', 'shop', (int)$shop['id']);
        back('/seller/settings');
    }

    /* ---------------- customers ---------------- */
    public function customers(): void
    {
        $shop = $this->shop();
        $rows = qa('SELECT u.id, u.name, u.email, u.phone, COUNT(DISTINCT o.id) AS orders_count,
                           COALESCE(SUM(CASE WHEN o.status IN ("delivered","completed") THEN o.total ELSE 0 END),0) AS spent,
                           MAX(o.created_at) AS last_order
                    FROM orders o JOIN users u ON u.id=o.user_id
                    WHERE o.shop_id=?
                    GROUP BY u.id, u.name, u.email, u.phone ORDER BY spent DESC LIMIT 200', [$shop['id']]);
        $inquiries = qv('SELECT COUNT(DISTINCT mt.buyer_id) FROM message_threads mt WHERE mt.shop_id=?', [$shop['id']]);
        $this->view('seller/customers', ['shop' => $shop, 'rows' => $rows, 'inquiries' => $inquiries, 'layout' => 'layouts/dashboard', 'seo' => ['title' => 'Customers — Seller eKamalia']]);
    }

    /* ---------------- inventory ---------------- */
    public function inventory(): void
    {
        $shop = $this->shop();
        $rows = qa('SELECT id,name,slug,stock,min_stock,price,sale_price,status,unit FROM products WHERE shop_id=? AND deleted_at IS NULL ORDER BY stock ASC, name LIMIT 300', [$shop['id']]);
        $moves = qa('SELECT im.*, p.name AS product_name, u.name AS by_user FROM inventory_movements im
                     JOIN products p ON p.id=im.product_id LEFT JOIN users u ON u.id=im.user_id
                     WHERE im.shop_id=? ORDER BY im.id DESC LIMIT 25', [$shop['id']]);
        $this->view('seller/inventory', ['shop' => $shop, 'rows' => $rows, 'moves' => $moves, 'layout' => 'layouts/dashboard', 'seo' => ['title' => 'Inventory — Seller eKamalia']]);
    }

    public function inventoryUpdate(): void
    {
        $shop = $this->shop();
        $p = q1('SELECT * FROM products WHERE id=? AND shop_id=? AND deleted_at IS NULL', [int_input('product_id'), $shop['id']]);
        if (!$p) json_fail('Product not found', 404);
        $qty = abs(int_input('quantity'));
        if ($qty < 1) json_fail('Enter quantity.');
        $dir = str_input('direction', 'in') === 'out' ? -1 : 1;
        $new = max(0, (int)$p['stock'] + $dir * $qty);
        q('UPDATE products SET stock=?, status=IF(?<=0 AND status="published","out_of_stock",IF(? > 0 AND status="out_of_stock","published",status)) WHERE id=?', [$new, $new, $new, $p['id']]);
        q('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,user_id,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
            [$shop['id'], $p['id'], $dir > 0 ? 'stock_in' : 'stock_out', $dir * $qty, (int)$p['stock'], $new, str_input('note', 'Seller panel manual update', 190), user_id(), now()]);
        is_ajax() ? json_ok(['message' => $p['name'] . ' stock → ' . $new]) : back('/seller/inventory');
    }

    /* ---------------- classified ads (seller) ---------------- */
    public function ads(): void
    {
        $me = auth();
        $rows = qa('SELECT a.*, c.name AS category_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id LIMIT 1) AS image
                    FROM ads a LEFT JOIN categories c ON c.id=a.category_id
                    WHERE a.user_id=? AND a.deleted_at IS NULL ORDER BY a.id DESC LIMIT 100', [$me['id']]);
        $stats = ['total' => count($rows), 'active' => count(array_filter($rows, fn($r) => $r['status'] === 'active')), 'views' => array_sum(array_map(fn($r) => (int)$r['views'], $rows))];
        $this->view('seller/ads', ['shop' => $this->shop(), 'rows' => $rows, 'stats' => $stats, 'layout' => 'layouts/dashboard', 'seo' => ['title' => 'My Classified Ads — Seller eKamalia']]);
    }

    /* ---------------- payments (seller earnings) ---------------- */
    public function payments(): void
    {
        $shop = $this->shop();
        $commission = (float)setting('commission_percent', 0);
        $cod = (float)qv('SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.shop_id=? AND o.payment_method="cod" AND o.status IN ("delivered","completed")', [$shop['id']]);
        $codPending = (float)qv('SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.shop_id=? AND o.payment_method="cod" AND o.status NOT IN ("delivered","completed","cancelled","refunded")', [$shop['id']]);
        $bank = (float)qv('SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN orders o ON o.id=p.order_id WHERE o.shop_id=? AND p.status="verified"', [$shop['id']]);
        $bankPending = (float)qv('SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN orders o ON o.id=p.order_id WHERE o.shop_id=? AND p.status IN ("pending","submitted")', [$shop['id']]);
        $rows = qa('SELECT o.id, o.order_number AS no, o.total, o.payment_method, o.payment_status, o.status, o.created_at, u.name AS buyer
                    FROM orders o JOIN users u ON u.id=o.user_id
                    WHERE o.shop_id=? ORDER BY o.id DESC LIMIT 100', [$shop['id']]);
        $this->view('seller/payments', ['shop' => $shop, 'commission' => $commission, 'cod' => $cod, 'codPending' => $codPending, 'bank' => $bank, 'bankPending' => $bankPending, 'rows' => $rows, 'layout' => 'layouts/dashboard', 'seo' => ['title' => 'Payments & Earnings — Seller eKamalia']]);
    }

    /* ---------------- delivery (fulfillment desk) ---------------- */
    public function delivery(): void
    {
        $shop = $this->shop();
        $rows = qa('SELECT o.*, u.name AS buyer, u.phone AS buyer_phone, c.name AS city_name
                    FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN cities c ON c.slug=o.ship_city
                    WHERE o.shop_id=? AND o.status IN ("confirmed","dispatched","processing")
                    ORDER BY o.id ASC LIMIT 100', [$shop['id']]);
        $counts = ['confirmed' => 0, 'dispatched' => 0, 'delivered30' => (int)qv('SELECT COUNT(*) FROM orders WHERE shop_id=? AND status="delivered" AND created_at > (NOW() - INTERVAL 30 DAY)', [$shop['id']])];
        foreach ($rows as $r) { if (isset($counts[$r['status']])) $counts[$r['status']]++; }
        $this->view('seller/delivery', ['shop' => $shop, 'rows' => $rows, 'counts' => $counts, 'layout' => 'layouts/dashboard', 'seo' => ['title' => 'Delivery Desk — Seller eKamalia']]);
    }
}
