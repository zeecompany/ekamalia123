<h1 class="h3 fw-bold mb-1">Admin Dashboard</h1>
<p class="text-muted mb-4">Assalam-o-Alaikum <?= e($me['name']) ?> — here's what's happening on eKamalia today.</p>
<div class="row g-3 mb-2 row-cols-2 row-cols-lg-4">
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/users') ?>"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-users"></i></div><div><div class="val"><?= $kpi['users'] ?></div><div class="lbl">Users (<?= $kpi['active_users'] ?> active)</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/shops?status=pending') ?>"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-store"></i></div><div><div class="val"><?= $kpi['shops'] ?></div><div class="lbl">Shops <?= $kpi['pending_shops'] ? '(<span class="text-warning">' . $kpi['pending_shops'] . ' pending</span>)' : '' ?></div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/products?status=pending') ?>"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-box"></i></div><div><div class="val"><?= $kpi['products'] ?></div><div class="lbl">Products <?= $kpi['pending_products'] ? '(<span class="text-warning">' . $kpi['pending_products'] . ' pending</span>)' : '' ?></div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/ads') ?>"><div class="kpi-card"><div class="ic ic-purple"><i class="fa-solid fa-tag"></i></div><div><div class="val"><?= $kpi['ads'] ?></div><div class="lbl">Classified Ads</div></div></div></a></div>
</div>
<div class="row g-3 mb-2 row-cols-2 row-cols-lg-4">
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/orders') ?>"><div class="kpi-card"><div class="ic ic-teal"><i class="fa-solid fa-boxes-stacked"></i></div><div><div class="val"><?= $kpi['orders'] ?></div><div class="lbl">Total Orders</div></div></div></a></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-sack-dollar"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($kpi['revenue']) ?></div><div class="lbl">Delivered Revenue</div></div></div></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/payments') ?>"><div class="kpi-card"><div class="ic <?= $kpi['pending_payments'] ? 'ic-red' : 'ic-blue' ?>"><i class="fa-solid fa-money-bill-wave"></i></div><div><div class="val"><?= $kpi['pending_payments'] ?></div><div class="lbl">Payments to Verify</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/pos-requests') ?>"><div class="kpi-card"><div class="ic <?= $kpi['pending_pos'] ? 'ic-red' : 'ic-blue' ?>"><i class="fa-solid fa-cash-register"></i></div><div><div class="val"><?= $kpi['pending_pos'] ?></div><div class="lbl">POS Requests (<?= $kpi['pos_users'] ?> active)</div></div></div></a></div>
</div>
<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/reports') ?>"><div class="kpi-card"><div class="ic <?= $kpi['reports'] ? 'ic-red' : 'ic-blue' ?>"><i class="fa-solid fa-flag"></i></div><div><div class="val"><?= $kpi['reports'] ?></div><div class="lbl">Open Reports</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/reviews?status=pending') ?>"><div class="kpi-card"><div class="ic <?= $kpi['pending_reviews'] ? 'ic-gold' : 'ic-blue' ?>"><i class="fa-solid fa-star"></i></div><div><div class="val"><?= $kpi['pending_reviews'] ?></div><div class="lbl">Pending Reviews</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/admin/inbox') ?>"><div class="kpi-card"><div class="ic <?= $kpi['messages'] ? 'ic-gold' : 'ic-blue' ?>"><i class="fa-solid fa-inbox"></i></div><div><div class="val"><?= $kpi['messages'] ?></div><div class="lbl">New Inbox Messages</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/bijli') ?>"><div class="kpi-card"><div class="ic <?= $feedersOff ? 'ic-red' : 'ic-green' ?>"><i class="fa-solid fa-bolt"></i></div><div><div class="val"><?= $feedersOff ?></div><div class="lbl">Feeders Currently OFF</div></div></div></a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-lg-8"><div class="admin-card"><div class="ac-head"><h6>Revenue & Orders — Last 14 Days</h6></div><div class="ac-body"><canvas id="revChart" height="95"></canvas></div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>New Users — 14 Days</h6></div><div class="ac-body"><canvas id="usrChart" height="200"></canvas></div></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Top Categories</h6></div><div class="ac-body p-0">
    <?php foreach ($topCats as $c): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span><?= e($c['name']) ?></span><b><?= (int)$c['cnt'] ?></b></div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Top Shops by Revenue</h6></div><div class="ac-body p-0">
    <?php foreach ($topShops as $s): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><?= e($s['name']) ?></span><b class="text-success"><?= money($s['revenue']) ?></b></div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Recent Activity (Audit)</h6><a class="small" href="<?= url('/admin/audit') ?>">View all</a></div><div class="ac-body p-0">
    <?php foreach ($recent as $r): ?>
    <div class="px-3 py-2 border-bottom small"><b><?= e($r['action']) ?></b> <?= $r['user_name'] ? '<span class="text-muted">by ' . e($r['user_name']) . '</span>' : '' ?>
      <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($r['created_at'])) ?><?= $r['details'] ? ' • ' . e(mb_substr((string)$r['details'], 0, 40)) : '' ?></div></div>
    <?php endforeach; ?>
  </div></div></div>
</div>
<?php
$revMap = [];
foreach ($revenue14 as $r) { $revMap[$r['d']] = ['t' => (float)$r['t'], 'c' => (int)$r['c']]; }
$usrMap = [];
foreach ($users14 as $u) { $usrMap[$u['d']] = (int)$u['c']; }
$days = []; $rev = []; $ords = []; $usr = [];
for ($i = 13; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-$i days"));
  $days[] = date('d M', strtotime($d));
  $rev[] = $revMap[$d]['t'] ?? 0; $ords[] = $revMap[$d]['c'] ?? 0; $usr[] = $usrMap[$d] ?? 0;
}
?>
<script>
ekChart('revChart', { data: { labels: <?= json_encode($days) ?>, datasets: [
  { type: 'line', label: 'Revenue (Rs)', data: <?= json_encode($rev) ?>, borderColor: '#0B7A3E', backgroundColor: 'rgba(11,122,62,.1)', fill: true, tension: .35 },
  { type: 'bar', label: 'Orders', data: <?= json_encode($ords) ?>, backgroundColor: 'rgba(240,180,41,.55)', yAxisID: 'y1' }
] }, options: { scales: { y: { beginAtZero: true }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } } } } });
ekChart('usrChart', { type: 'bar', data: { labels: <?= json_encode($days) ?>, datasets: [{ label: 'New users', data: <?= json_encode($usr) ?>, backgroundColor: 'rgba(37,86,196,.65)', borderRadius: 4 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
</script>
