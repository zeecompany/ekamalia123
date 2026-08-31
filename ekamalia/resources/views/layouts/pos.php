<?php
/** Layout: POS terminal */
$pos = pos_access();
if (!$pos) redirect('/pos/request');
$me = auth();
$shop = q1('SELECT * FROM shops WHERE id=?', [(int)($pos['shop_id'] ?? 0) ?: (int)(my_shop()['id'] ?? 0)]);
$flashMsg = flash();
$p = current_path();
$active = fn(string $prefix) => str_starts_with($p, $prefix) ? 'active' : '';
$nav = [
    ['/pos', 'fa-cash-register', 'Terminal', 'sales'],
    ['/pos/dashboard', 'fa-chart-pie', 'Dashboard', null],
    ['/pos/invoices', 'fa-file-invoice-dollar', 'Invoices', 'sales'],
    ['/pos/returns', 'fa-rotate-left', 'Returns', 'returns'],
    ['/pos/purchases', 'fa-truck-ramp-box', 'Purchases', 'purchases'],
    ['/pos/inventory', 'fa-boxes-stacked', 'Inventory', 'inventory'],
    ['/pos/customers', 'fa-users', 'Customers', 'customers'],
    ['/pos/suppliers', 'fa-industry', 'Suppliers', 'purchases'],
    ['/pos/expenses', 'fa-receipt', 'Expenses', 'expenses'],
    ['/pos/reports', 'fa-chart-line', 'Reports', 'reports'],
];
if (($pos['role'] ?? '') === 'owner') $nav[] = ['/pos/staff', 'fa-user-gear', 'Staff', null];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($seo['title'] ?? 'POS') ?> — eKamalia</title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<link href="<?= asset('css/pos.css') ?>" rel="stylesheet">
</head>
<body class="pos-body">
<div class="pos-shell">
  <aside class="pos-sidebar" id="posSidebar">
    <div class="ps-brand">
      <div><i class="fa-solid fa-cash-register"></i> POS</div>
      <div class="ps-shop"><?= e($shop['name'] ?? 'My Shop') ?></div>
      <div class="ps-role"><?= e(ucfirst($pos['role'])) ?> • <?= e($me['name']) ?></div>
    </div>
    <nav class="ps-nav">
      <?php foreach ($nav as $n): ?>
        <?php if ($n[3] === null || pos_can($pos, $n[3]) || $pos['role'] === 'owner'): ?>
          <a class="ps-link <?= $active($n[0]) ?>" href="<?= url($n[0]) ?>"><i class="fa-solid <?= $n[1] ?>"></i><span><?= e($n[2]) ?></span></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="ps-footer">
      <a href="<?= url('/dashboard') ?>"><i class="fa-solid fa-arrow-left me-2"></i>Back to account</a>
    </div>
  </aside>
  <div class="pos-main">
    <header class="pos-topbar">
      <button class="btn btn-light d-lg-none" onclick="document.getElementById('posSidebar').classList.toggle('open')" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
      <div class="fw-bold small text-muted d-none d-md-block"><?= e($seo['title'] ?? 'eKamalia POS') ?></div>
      <span class="ms-auto badge text-bg-light"><i class="fa-solid fa-circle text-success" style="font-size:.5rem"></i> Online</span>
      <div class="dropdown">
        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown" style="width:36px;height:36px"><i class="fa-solid fa-user"></i></button>
        <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
          <a class="dropdown-item rounded-3" href="<?= url('/dashboard') ?>">My Account</a>
          <a class="dropdown-item rounded-3 text-danger" href="<?= url('/logout') ?>">Logout</a>
        </div>
      </div>
    </header>
    <main class="pos-content"><?= $content ?></main>
  </div>
</div>
<div class="toast-ek"></div>
<?php if ($flashMsg): ?><script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($flashMsg['message']) ?>, <?= json_encode($flashMsg['type']) ?>));</script><?php endif; ?>
<script>window.EK_BASE = <?= json_encode(base_path()) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/pos.js') ?>"></script>
<?= $pageScripts ?? '' ?>
</body>
</html>
