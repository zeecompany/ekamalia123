<?php
declare(strict_types=1);

namespace App\Controllers;

class DashboardController extends Controller
{
    protected string $defaultLayout = 'layouts/dashboard';
    public function index(): void
    {
        $me = require_login();
        $uid = (int)$me['id'];
        $stats = [
            'orders' => (int)qv('SELECT COUNT(*) FROM orders WHERE user_id=?', [$uid]),
            'active_orders' => (int)qv('SELECT COUNT(*) FROM orders WHERE user_id=? AND status NOT IN ("delivered","completed","cancelled","returned","refunded")', [$uid]),
            'ads' => (int)qv('SELECT COUNT(*) FROM ads WHERE user_id=? AND deleted_at IS NULL', [$uid]),
            'wishlist' => (int)qv('SELECT COUNT(*) FROM wishlists WHERE user_id=?', [$uid]),
            'following' => (int)qv('SELECT COUNT(*) FROM shop_followers WHERE user_id=?', [$uid]),
            'reviews' => (int)qv('SELECT COUNT(*) FROM reviews WHERE user_id=?', [$uid]),
        ];
        $recentOrders = qa('SELECT o.*, s.name AS shop_name FROM orders o LEFT JOIN shops s ON s.id=o.shop_id WHERE o.user_id=? ORDER BY o.created_at DESC LIMIT 5', [$uid]);
        $wishlist = qa('SELECT p.*, (SELECT image FROM product_images pi WHERE pi.product_id=p.id LIMIT 1) AS image, s.name AS shop_name, s.slug AS shop_slug
                        FROM wishlists w JOIN products p ON p.id=w.item_id AND w.item_type="product" JOIN shops s ON s.id=p.shop_id
                        WHERE w.user_id=? ORDER BY w.created_at DESC LIMIT 8', [$uid]);
        $recentlyViewed = SocialController::recentlyViewed();
        $myShop = my_shop();
        $posStatus = q1('SELECT * FROM pos_requests WHERE user_id=? ORDER BY id DESC LIMIT 1', [$uid]);
        $this->view('dashboard/index', ['stats' => $stats, 'recentOrders' => $recentOrders, 'wishlist' => $wishlist,
            'recentlyViewed' => $recentlyViewed, 'myShop' => $myShop, 'posStatus' => $posStatus,
            'seo' => ['title' => t('nav.dashboard') . ' — eKamalia']]);
    }

    public function profile(): void
    {
        $me = require_login();
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $this->view('dashboard/profile', ['cities' => $cities, 'seo' => ['title' => 'My Profile — eKamalia']]);
    }

    public function updateProfile(): void
    {
        $me = require_login();
        $name = str_input('name', '', 120);
        if (mb_strlen($name) < 3) { flash('danger', 'Name is too short.'); back('/dashboard/profile'); }
        $avatar = $me['avatar'];
        try {
            $newAvatar = upload_image('avatar', 'avatars', 3, [300, 300]);
            if ($newAvatar) { delete_upload($avatar); $avatar = $newAvatar; }
        } catch (\RuntimeException $e) { flash('danger', 'Photo: ' . $e->getMessage()); back('/dashboard/profile'); }
        q('UPDATE users SET name=?, phone=?, dob=?, city_id=?, avatar=?, profile_public=? WHERE id=?', [
            $name, preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null,
            str_input('dob') ?: null, int_input('city_id') ?: null, $avatar,
            input('profile_public') ? 1 : 0, $me['id'],
        ]);
        flash('success', 'Profile updated.');
        audit_log('profile.updated', 'user', (int)$me['id']);
        back('/dashboard/profile');
    }

    public function security(): void
    {
        $me = require_login();
        $logins = qa('SELECT * FROM login_history WHERE user_id=? ORDER BY created_at DESC LIMIT 10', [$me['id']]);
        $this->view('dashboard/security', ['logins' => $logins, 'seo' => ['title' => 'Security — eKamalia']]);
    }

    public function changePassword(): void
    {
        $me = require_login();
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['password'] ?? '');
        $new2 = (string)($_POST['password_confirmation'] ?? '');
        if (!password_verify($current, $me['password'])) { flash('danger', 'Current password is incorrect.'); back('/dashboard/security'); }
        if (strlen($new) < 8) { flash('danger', 'New password must be at least 8 characters.'); back('/dashboard/security'); }
        if ($new !== $new2) { flash('danger', 'Passwords do not match.'); back('/dashboard/security'); }
        q('UPDATE users SET password=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
        send_app_mail($me['email'], 'Your eKamalia password was changed', 'Your password was changed from dashboard. If this was not you, reset it immediately.', '/forgot-password');
        audit_log('password.changed', 'user', (int)$me['id']);
        flash('success', 'Password changed successfully.');
        back('/dashboard/security');
    }

    public function wishlist(): void
    {
        $me = require_login();
        $products = qa('SELECT p.*, (SELECT image FROM product_images pi WHERE pi.product_id=p.id LIMIT 1) AS image, s.name AS shop_name, s.slug AS shop_slug
                        FROM wishlists w JOIN products p ON p.id=w.item_id AND w.item_type="product" JOIN shops s ON s.id=p.shop_id
                        WHERE w.user_id=? ORDER BY w.created_at DESC LIMIT 100', [$me['id']]);
        $ads = qa('SELECT a.*, c.name AS city_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id ORDER BY sort_order LIMIT 1) AS image
                   FROM wishlists w JOIN ads a ON a.id=w.item_id AND w.item_type="ad" LEFT JOIN cities c ON c.id=a.city_id
                   WHERE w.user_id=? ORDER BY w.created_at DESC LIMIT 100', [$me['id']]);
        $this->view('dashboard/wishlist', ['products' => $products, 'ads' => $ads, 'seo' => ['title' => t('nav.wishlist') . ' — eKamalia']]);
    }

    public function following(): void
    {
        $me = require_login();
        $shops = qa('SELECT s.*, c.name AS city_name FROM shop_followers f JOIN shops s ON s.id=f.shop_id LEFT JOIN cities c ON c.id=s.city_id
                     WHERE f.user_id=? AND s.deleted_at IS NULL ORDER BY f.created_at DESC', [$me['id']]);
        $savedSearches = qa('SELECT * FROM saved_searches WHERE user_id=? ORDER BY id DESC', [$me['id']]);
        $this->view('dashboard/following', ['shops' => $shops, 'savedSearches' => $savedSearches, 'seo' => ['title' => 'Following — eKamalia']]);
    }

    public function addresses(): void
    {
        $me = require_login();
        $addresses = qa('SELECT a.*, c.name AS city_name FROM addresses a LEFT JOIN cities c ON c.id=a.city_id WHERE a.user_id=? ORDER BY a.is_default DESC, a.id DESC', [$me['id']]);
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $this->view('dashboard/addresses', ['addresses' => $addresses, 'cities' => $cities, 'seo' => ['title' => 'My Addresses — eKamalia']]);
    }

    public function addressStore(): void
    {
        $me = require_login();
        $name = str_input('name', '', 120);
        $phone = preg_replace('/\D/', '', str_input('phone', '', 20));
        $address = str_input('address', '', 255);
        if (mb_strlen($name) < 3 || strlen($phone) < 10 || mb_strlen($address) < 10) {
            flash('danger', 'Please fill name, valid phone and complete address.');
            back('/dashboard/addresses');
        }
        if (input('is_default')) q('UPDATE addresses SET is_default=0 WHERE user_id=?', [$me['id']]);
        q('INSERT INTO addresses (user_id,label,name,phone,address,city_id,area,is_default,created_at) VALUES (?,?,?,?,?,?,?,?,?)',
            [$me['id'], str_input('label', 'Home', 60), $name, $phone, $address, int_input('city_id') ?: null, str_input('area', '', 140), input('is_default') ? 1 : 0, now()]);
        flash('success', 'Address saved.');
        back('/dashboard/addresses');
    }

    public function addressDelete(array $params): void
    {
        $me = require_login();
        q('DELETE FROM addresses WHERE id=? AND user_id=?', [(int)$params['id'], $me['id']]);
        flash('success', 'Address removed.');
        back('/dashboard/addresses');
    }

    public function myComments(): void
    {
        $me = require_login();
        $comments = qa('SELECT c.*, CONCAT(UCASE(LEFT(c.item_type,1)),SUBSTRING(c.item_type,2)) AS type_label FROM comments c WHERE c.user_id=? AND c.status="visible" ORDER BY c.created_at DESC LIMIT 50', [$me['id']]);
        $reviews = qa('SELECT r.*, r.item_type AS type_label FROM reviews r WHERE r.user_id=? ORDER BY r.created_at DESC LIMIT 50', [$me['id']]);
        $this->view('dashboard/comments', ['comments' => $comments, 'reviews' => $reviews, 'seo' => ['title' => 'My Activity — eKamalia']]);
    }

    public function deleteAccount(): void
    {
        $me = require_login();
        if (str_input('confirm') !== 'DELETE') { flash('danger', 'Type DELETE to confirm account removal.'); back('/dashboard/security'); }
        // soft-delete: anonymize personal data (privacy-aware)
        q('UPDATE users SET status="banned", name="Deleted User", email=CONCAT("deleted+", id, "@ekamalia.invalid"), phone=NULL, avatar=NULL, deleted_at=? WHERE id=?', [now(), $me['id']]);
        q('DELETE FROM cart_items WHERE user_id=?', [$me['id']]);
        session_destroy();
        flash('info', 'Your account has been scheduled for deletion. Personal data removed.');
        redirect('/');
    }
}
