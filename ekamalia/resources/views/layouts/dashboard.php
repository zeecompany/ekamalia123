<?php
/** Layout: buyer + seller dashboards */
$me = require_login();
$flashMsg = flash();
$p = current_path();
/* header/footer partial globals (same as layouts/main) */
$seo = array_merge(seo_defaults(), $seo ?? []);
$citiesTop = qa('SELECT id,name,slug,is_primary FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order LIMIT 30');
$mainCats = qa('SELECT name,slug,icon FROM categories WHERE parent_id IS NULL AND type IN ("both","ad","product") AND status="active" ORDER BY sort_order LIMIT 14');
$footerPages = qa('SELECT title,slug FROM pages WHERE status="active" AND show_in_footer=1 ORDER BY id LIMIT 8');
$cartCount = App\Services\Cart::count();
$myShopExists = qv('SELECT COUNT(*) FROM shops WHERE user_id=? AND status="approved"', [$me['id']]);
$unreadNotifs = (int)qv('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$me['id']]);
$unreadMsgs = (int)qv('SELECT COALESCE(SUM(CASE WHEN seller_id=? THEN buyer_unread ELSE seller_unread END),0) FROM message_threads WHERE (buyer_id=? OR seller_id=?)', [$me['id'], $me['id'], $me['id']]);
$auth = $auth ?? $me;
$isSeller = (bool)my_shop();
$posActive = (bool)pos_access();
$nav = [
    ['/dashboard', 'fa-gauge-high', 'Overview'],
    ['/dashboard/orders', 'fa-box', 'My Orders'],
    ['/dashboard/my-ads', 'fa-tag', 'My Ads'],
    ['/dashboard/wishlist', 'fa-heart', 'Wishlist'],
    ['/dashboard/following', 'fa-users', 'Following'],
    ['/messages', 'fa-comment-dots', 'Messages'],
    ['/notifications', 'fa-bell', 'Notifications'],
    ['/dashboard/activity', 'fa-comments', 'My Activity'],
    ['/dashboard/addresses', 'fa-location-dot', 'Addresses'],
    ['/dashboard/profile', 'fa-user', 'Profile'],
    ['/dashboard/security', 'fa-shield-halved', 'Security'],
];
$sellerNav = [
    ['/seller', 'fa-store', 'Shop Overview'],
    ['/seller/products', 'fa-box-open', 'Products'],
    ['/seller/orders', 'fa-truck-fast', 'Shop Orders'],
    ['/seller/coupons', 'fa-ticket', 'Coupons'],
    ['/seller/inventory', 'fa-warehouse', 'Inventory'],
    ['/seller/customers', 'fa-users', 'Customers'],
    ['/seller/payments', 'fa-wallet', 'Payments'],
    ['/seller/delivery', 'fa-truck', 'Delivery Desk'],
    ['/seller/ads', 'fa-tags', 'My Classified Ads'],
    ['/seller/reviews', 'fa-star', 'Reviews'],
    ['/seller/analytics', 'fa-chart-line', 'Analytics'],
    ['/seller/settings', 'fa-gear', 'Shop Settings'],
];
?>
<!doctype html>
<html lang="en" dir="<?= lang_dir() ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($seo['title'] ?? 'Dashboard') ?> — eKamalia</title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<?php if (lang() === 'ur'): ?><link href="<?= asset('css/app-rtl.css') ?>" rel="stylesheet"><?php endif; ?>
</head>
<body>
<?php include views_path('partials/marquee-top'); ?>
<?php include views_path('partials/header'); ?>

<div class="container py-4">
  <div class="row g-4">
    <aside class="col-lg-3 d-none d-lg-block">
      <div class="ek-card p-3 mb-3 text-center">
        <img src="<?= $me['avatar'] ? upload_url($me['avatar']) : asset('img/avatar-default.svg') ?>" class="rounded-circle mb-2" style="width:72px;height:72px;object-fit:cover" alt="">
        <div class="fw-bold"><?= e($me['name']) ?></div>
        <div class="text-muted small"><?= e($me['email']) ?></div>
        <?php if ($me['is_verified']): ?><span class="badge text-bg-success mt-1"><i class="fa-solid fa-circle-check me-1"></i>Verified</span><?php endif; ?>
      </div>
      <div class="ek-card p-2">
        <?php foreach ($nav as $n): ?>
          <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $p === $n[0] ? 'bg-success-subtle text-success fw-semibold' : 'text-dark' ?>" href="<?= url($n[0]) ?>">
            <i class="fa-solid <?= $n[1] ?> w-4"></i><span class="small"><?= e($n[2]) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if ($isSeller): ?>
        <div class="ek-card p-2 mt-3">
          <div class="px-3 py-2 small fw-bold text-uppercase text-muted">Seller Tools</div>
          <?php foreach ($sellerNav as $n): ?>
            <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $p === $n[0] ? 'bg-success-subtle text-success fw-semibold' : 'text-dark' ?>" href="<?= url($n[0]) ?>">
              <i class="fa-solid <?= $n[1] ?> w-4"></i><span class="small"><?= e($n[2]) ?></span>
            </a>
          <?php endforeach; ?>
          <?php if ($posActive): ?>
            <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark" href="<?= url('/pos') ?>"><i class="fa-solid fa-cash-register w-4"></i><span class="small">POS Terminal</span></a>
            <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark" href="<?= url('/pos/dashboard') ?>"><i class="fa-solid fa-chart-pie w-4"></i><span class="small">POS Dashboard</span></a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="ek-card p-3 mt-3 text-center" style="background:linear-gradient(135deg,#0B7A3E,#14915a);color:#fff">
          <i class="fa-solid fa-shop fs-4 mb-2"></i>
          <div class="fw-bold small">Start selling on eKamalia</div>
          <a href="<?= url('/shops/create') ?>" class="btn btn-warning btn-sm rounded-pill mt-2 fw-semibold">Create Shop — Free</a>
        </div>
      <?php endif; ?>
    </aside>
    <div class="col-lg-9"><?= $content ?></div>
  </div>
</div>

<?php include views_path('partials/footer'); ?>
<?php include views_path('partials/mobile-nav'); ?>
<div class="toast-ek"></div>
<?php if ($flashMsg): ?><script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($flashMsg['message']) ?>, <?= json_encode($flashMsg['type']) ?>));</script><?php endif; ?>
<script>window.EK_LOGGED_IN = true; window.EK_BASE = <?= json_encode(base_path()) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
</body>
</html>
