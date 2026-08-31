<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cart;

class CartController extends Controller
{
    public function index(): void
    {
        $groups = Cart::groups();
        $totals = Cart::totals($groups);
        $banks = qa('SELECT * FROM bank_accounts WHERE status="active" ORDER BY sort_order');
        $this->view('cart/index', ['groups' => $groups, 'totals' => $totals, 'banks' => $banks,
            'seo' => ['title' => t('cart.title') . ' — eKamalia']]);
    }

    public function add(): void
    {
        [$ok, $msg] = Cart::add(int_input('product_id'), max(1, int_input('quantity', 1)));
        if (is_ajax()) {
            if ($ok) json_ok(['message' => $msg, 'cart_count' => Cart::count()]);
            json_fail($msg);
        }
        flash($ok ? 'success' : 'danger', $msg);
        back('/cart');
    }

    public function update(): void
    {
        [$ok, $msg] = Cart::update(int_input('product_id'), int_input('quantity', 1));
        if (is_ajax()) { $ok ? json_ok(['message' => $msg]) : json_fail($msg); }
        flash($ok ? 'success' : 'danger', $msg);
        back('/cart');
    }

    public function remove(): void
    {
        [$ok, $msg] = Cart::remove(int_input('product_id'));
        if (is_ajax()) json_ok(['message' => $msg]);
        back('/cart');
    }

    public function coupon(): void
    {
        $code = strtoupper(str_input('code', '', 40));
        if ($code === '') {
            unset($_SESSION['coupon']);
            if (is_ajax()) json_ok(['message' => 'Coupon removed']);
            back('/cart');
        }
        $c = q1('SELECT * FROM coupons WHERE code=?', [$code]);
        if (!$c || $c['status'] !== 'active') json_fail('Invalid coupon code');
        if ($c['expires_at'] && $c['expires_at'] < now()) json_fail('This coupon has expired');
        if ($c['starts_at'] && $c['starts_at'] > now()) json_fail('This coupon is not active yet');
        if ($c['max_uses'] && (int)$c['used_count'] >= (int)$c['max_uses']) json_fail('Coupon usage limit reached');
        if ($c['shop_id']) {
            $has = false;
            foreach (Cart::items() as $it) if ((int)$it['shop_id2'] === (int)$c['shop_id']) $has = true;
            if (!$has) json_fail('This coupon applies to a specific shop — add its products first');
        }
        $_SESSION['coupon'] = $code;
        if (is_ajax()) json_ok(['message' => 'Coupon applied! 🎉']);
        back('/cart');
    }
}
