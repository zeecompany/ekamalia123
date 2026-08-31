<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Cart engine — DB-backed for logged users, session-backed for guests.
 * Supports multi-shop checkout: order is split into per-shop sub-orders at checkout.
 */
class Cart
{
    public static function items(): array
    {
        $uid = \user_id();
        if ($uid) {
            return \qa(
                'SELECT ci.id AS cart_id, ci.quantity, p.id AS product_id, p.*, s.name AS shop_name, s.slug AS shop_slug, s.delivery_fee AS shop_delivery_fee, s.id AS shop_id2,
                        (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS image
                 FROM cart_items ci JOIN products p ON p.id=ci.product_id JOIN shops s ON s.id=p.shop_id
                 WHERE ci.user_id=? AND p.status="published" AND p.deleted_at IS NULL
                 ORDER BY ci.created_at',
                [$uid]
            );
        }
        // guests: session cart
        $sess = $_SESSION['guest_cart'] ?? [];
        if (!$sess) return [];
        $ids = array_map('intval', array_keys($sess));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = \qa(
            "SELECT p.id AS product_id, p.*, s.name AS shop_name, s.slug AS shop_slug, s.delivery_fee AS shop_delivery_fee, s.id AS shop_id2,
                    (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS image
             FROM products p JOIN shops s ON s.id=p.shop_id
             WHERE p.id IN ($in) AND p.status='published' AND p.deleted_at IS NULL",
            $ids
        );
        foreach ($rows as &$r) $r['quantity'] = (int)($sess[$r['product_id']] ?? 1);
        return $rows;
    }

    public static function count(): int
    {
        $uid = \user_id();
        if ($uid) return (int)\qv('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id=?', [$uid]);
        return array_sum(array_map('intval', ($_SESSION['guest_cart'] ?? [])));
    }

    public static function add(int $productId, int $qty = 1): array
    {
        $p = \q1('SELECT p.*, s.status AS shop_status FROM products p JOIN shops s ON s.id=p.shop_id WHERE p.id=? AND p.status="published" AND p.deleted_at IS NULL', [$productId]);
        if (!$p) return [false, 'Product not available.'];
        if ($p['shop_status'] !== 'approved') return [false, 'This shop is currently not accepting orders.'];
        if ($p['stock'] < 1) return [false, 'Sorry, this product is out of stock.'];
        $qty = max(1, min($qty, 99));
        $uid = \user_id();
        if ($uid) {
            $existing = \q1('SELECT * FROM cart_items WHERE user_id=? AND product_id=?', [$uid, $productId]);
            if ($existing) {
                $new = min($existing['quantity'] + $qty, $p['stock']);
                \q('UPDATE cart_items SET quantity=? WHERE id=?', [$new, $existing['id']]);
            } else {
                \q('INSERT INTO cart_items (user_id,product_id,quantity) VALUES (?,?,?)', [$uid, $productId, min($qty, $p['stock'])]);
            }
        } else {
            $cart = $_SESSION['guest_cart'] ?? [];
            $cart[$productId] = min(($cart[$productId] ?? 0) + $qty, $p['stock']);
            $_SESSION['guest_cart'] = $cart;
        }
        return [true, 'Added to cart'];
    }

    public static function update(int $productId, int $qty): array
    {
        $p = \q1('SELECT stock FROM products WHERE id=? AND status="published"', [$productId]);
        if (!$p) return [false, 'Product not found.'];
        if ($qty < 1) return self::remove($productId);
        if ($qty > 99) $qty = 99;
        if ($qty > (int)$p['stock']) return [false, 'Only ' . $p['stock'] . ' in stock'];
        $uid = \user_id();
        if ($uid) {
            \q('UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?', [$qty, $uid, $productId]);
        } else {
            $cart = $_SESSION['guest_cart'] ?? [];
            if (isset($cart[$productId])) { $cart[$productId] = $qty; $_SESSION['guest_cart'] = $cart; }
        }
        return [true, 'Updated'];
    }

    public static function remove(int $productId): array
    {
        $uid = \user_id();
        if ($uid) \q('DELETE FROM cart_items WHERE user_id=? AND product_id=?', [$uid, $productId]);
        else { $cart = $_SESSION['guest_cart'] ?? []; unset($cart[$productId]); $_SESSION['guest_cart'] = $cart; }
        return [true, 'Removed'];
    }

    /** merge guest cart into user cart at login */
    public static function merge(int $userId): void
    {
        $guest = $_SESSION['guest_cart'] ?? [];
        foreach ($guest as $pid => $qty) {
            $ex = \q1('SELECT id,quantity FROM cart_items WHERE user_id=? AND product_id=?', [$userId, $pid]);
            if ($ex) \q('UPDATE cart_items SET quantity=LEAST(quantity+?,99) WHERE id=?', [(int)$qty, $ex['id']]);
            else \q('INSERT IGNORE INTO cart_items (user_id,product_id,quantity) VALUES (?,?,?)', [$userId, $pid, (int)$qty]);
        }
        unset($_SESSION['guest_cart']);
    }

    public static function clear(int $userId): void
    {
        \q('DELETE FROM cart_items WHERE user_id=?', [$userId]);
    }

    /** effective unit price (sale price wins) */
    public static function unitPrice(array $p): float
    {
        return ($p['sale_price'] && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price'])
            ? (float)$p['sale_price'] : (float)$p['price'];
    }

    /** group items per shop with totals; applies session coupon (platform or shop-level) */
    public static function groups(): array
    {
        $items = self::items();
        $groups = [];
        foreach ($items as $it) {
            $sid = (int)$it['shop_id2'];
            $groups[$sid] ??= ['shop_id' => $sid, 'shop_name' => $it['shop_name'], 'shop_slug' => $it['shop_slug'],
                               'delivery_fee' => (float)$it['shop_delivery_fee'], 'items' => [], 'subtotal' => 0];
            $price = self::unitPrice($it);
            $line = $price * (int)$it['quantity'];
            $groups[$sid]['items'][] = $it + ['unit_price' => $price, 'line_total' => $line];
            $groups[$sid]['subtotal'] += $line;
        }
        return $groups;
    }

    public static function totals(array $groups): array
    {
        $subtotal = 0; $shipping = 0; $discount = 0; $freeDelivery = false;
        foreach ($groups as $g) { $subtotal += $g['subtotal']; $shipping += $g['delivery_fee']; }
        $coupon = $_SESSION['coupon'] ?? null;
        if ($coupon) {
            $c = \q1('SELECT * FROM coupons WHERE code=? AND status="active"', [$coupon]);
            $valid = $c && (!$c['expires_at'] || $c['expires_at'] > \now())
                && (!$c['starts_at'] || $c['starts_at'] <= \now())
                && (!$c['max_uses'] || (int)$c['used_count'] < (int)$c['max_uses'])
                && (!$c['shop_id'] || isset($groups[(int)$c['shop_id']]));
            if (!$valid) { unset($_SESSION['coupon']); }
            elseif ($subtotal >= (float)$c['min_order']) {
                if ($c['type'] === 'free_delivery') { $freeDelivery = true; $discount = 0; }
                else {
                    $target = $c['shop_id'] ? ($groups[(int)$c['shop_id']]['subtotal'] ?? 0) : $subtotal;
                    $discount = $c['type'] === 'percent' ? $target * (float)$c['value'] / 100 : min((float)$c['value'], $target);
                    if ($c['max_discount']) $discount = min($discount, (float)$c['max_discount']);
                }
            }
        }
        if ($freeDelivery) $shipping = 0;
        return ['subtotal' => $subtotal, 'shipping' => $shipping, 'discount' => $discount,
                'total' => max(0, $subtotal + $shipping - $discount), 'coupon' => $coupon, 'free_delivery' => $freeDelivery];
    }
}
