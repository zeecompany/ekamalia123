<?php
/** Layout: Admin panel */
$me = require_admin();
$flashMsg = flash();
$p = current_path();
$active = fn(string $prefix) => str_starts_with($p, $prefix) ? 'active' : '';
$menu = [
    ['section', 'Main'],
    ['/admin', 'fa-gauge-high', 'Dashboard'],
    ['/admin/analytics', 'fa-chart-line', 'Analytics'],
    ['section', 'Marketplace'],
    ['/admin/users', 'fa-users', 'Users', 'pending-users'],
    ['/admin/shops', 'fa-store', 'Shops'],
    ['/admin/products', 'fa-box', 'Products'],
    ['/admin/ads', 'fa-tag', 'Classified Ads'],
    ['/admin/orders', 'fa-boxes-stacked', 'Orders'],
    ['/admin/payments', 'fa-money-bill-wave', 'Payments', 'pending-payments'],
    ['/admin/businesses', 'fa-map-location-dot', 'Businesses'],
    ['section', 'Catalog'],
    ['/admin/categories', 'fa-layer-group', 'Categories & Brands'],
    ['/admin/reviews', 'fa-star', 'Reviews', 'pending-reviews'],
    ['/admin/comments', 'fa-comments', 'Comments'],
    ['/admin/reports', 'fa-flag', 'Reports', 'open-reports'],
    ['/admin/inbox', 'fa-inbox', 'Contact Inbox', 'new-messages'],
    ['section', 'Content & Marketing'],
    ['/admin/sliders', 'fa-panorama', 'Hero Sliders'],
    ['/admin/marquee', 'fa-bullhorn', 'Marquee'],
    ['/admin/advertisements', 'fa-rectangle-ad', 'Advertisements'],
    ['/admin/homepage', 'fa-table-columns', 'Homepage Builder'],
    ['/admin/pages', 'fa-file-lines', 'CMS Pages'],
    ['/admin/news', 'fa-newspaper', 'News'],
    ['/admin/testimonials', 'fa-quote-left', 'Testimonials'],
    ['section', 'Commerce'],
    ['/admin/packages', 'fa-crown', 'Packages'],
    ['/admin/promotions', 'fa-rocket', 'Promotions'],
    ['/admin/pos-requests', 'fa-cash-register', 'POS Requests', 'pending-pos'],
    ['/admin/banks', 'fa-building-columns', 'Bank Accounts'],
    ['section', 'Kamalia Services'],
    ['/admin/bijli', 'fa-bolt', 'Bijli Updates Manager'],
    ['section', 'System'],
    ['/admin/settings', 'fa-gear', 'Settings'],
    ['/admin/audit', 'fa-clipboard-list', 'Audit Logs'],
    ['/admin/health', 'fa-heart-pulse', 'System Health'],
    ['/admin/backups', 'fa-database', 'Backups'],
];
$badges = [
    'pending-users' => (int)qv('SELECT COUNT(*) FROM users WHERE status="pending"'),
    'pending-shops' => (int)qv('SELECT COUNT(*) FROM shops WHERE status="pending"'),
    'pending-products' => (int)qv('SELECT COUNT(*) FROM products WHERE status="pending"'),
    'pending-ads' => (int)qv('SELECT COUNT(*) FROM ads WHERE status="pending"'),
    'pending-payments' => (int)qv('SELECT COUNT(*) FROM payments WHERE status="submitted"'),
    'pending-reviews' => (int)qv('SELECT COUNT(*) FROM reviews WHERE status="pending"'),
    'open-reports' => (int)qv('SELECT COUNT(*) FROM reports WHERE status="open"'),
    'pending-pos' => (int)qv('SELECT COUNT(*) FROM pos_requests WHERE status="pending"'),
    'new-messages' => (int)qv('SELECT COUNT(*) FROM contact_messages WHERE status="new"'),
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($seo['title'] ?? 'Admin')) ?></title>
<meta name="robots" content="noindex,nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="as-brand"><i class="fa-solid fa-store"></i> eKamalia <span class="as-admin">ADMIN</span></div>
    <nav class="as-nav">
      <?php foreach ($menu as $item): ?>
        <?php if ($item[0] === 'section'): ?>
          <div class="as-section"><?= e($item[1]) ?></div>
        <?php else: ?>
          <a class="as-link <?= $active($item[0]) ?>" href="<?= url($item[0]) ?>">
            <i class="fa-solid <?= e($item[1]) ?>"></i><span><?= e($item[2]) ?></span>
            <?php if (!empty($item[3]) && ($badges[$item[3]] ?? 0) > 0): ?><span class="as-badge"><?= $badges[$item[3]] ?></span><?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="as-footer">
      <a href="<?= url('/') ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>View Website</a>
    </div>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-light d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('open')" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button>
      <div class="fw-bold d-none d-md-block"><?= e($seo['title'] ?? 'Admin') ?></div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-light rounded-circle" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center" href="<?= url('/admin/bijli') ?>" title="Bijli quick status"><i class="fa-solid fa-bolt text-warning"></i></a>
        <div class="dropdown">
          <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-2" data-bs-toggle="dropdown">
            <img src="<?= $me['avatar'] ? upload_url($me['avatar']) : asset('img/avatar-default.svg') ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover" alt="">
            <span class="small fw-semibold d-none d-sm-inline"><?= e($me['name']) ?></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
            <a class="dropdown-item rounded-3" href="<?= url('/dashboard') ?>"><i class="fa-solid fa-user me-2"></i>My Account</a>
            <a class="dropdown-item rounded-3 text-danger" href="<?= url('/logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
          </div>
        </div>
      </div>
    </header>
    <main class="admin-content p-3 p-lg-4"><?= $content ?></main>
  </div>
</div>
<div class="toast-ek"></div>
<?php if ($flashMsg): ?><script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($flashMsg['message']) ?>, <?= json_encode($flashMsg['type']) ?>));</script><?php endif; ?>
<script>window.EK_BASE = <?= json_encode(base_path()) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
