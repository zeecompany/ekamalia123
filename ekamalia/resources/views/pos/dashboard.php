<h1 class="h4 fw-bold mb-1">POS Dashboard</h1>
<p class="text-muted mb-4"><?= e(fmt_date(date('Y-m-d H:i:s'), 'l, d M Y')) ?></p>
<div class="row g-3 mb-3 row-cols-2 row-cols-lg-4">
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-cash-register"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($stats['today_sales']) ?></div><div class="lbl">Today's Sales (<?= (int)$stats['today_count'] ?>)</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-calendar-alt"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($stats['month_sales']) ?></div><div class="lbl">This Month</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-arrow-trend-up"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($stats['month_profit']) ?></div><div class="lbl">Month Profit</div></div></div></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/pos/inventory') ?>"><div class="kpi-card"><div class="ic <?= $stats['low_stock'] ? 'ic-red' : 'ic-blue' ?>"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="val"><?= (int)$stats['low_stock'] ?></div><div class="lbl">Low Stock Items</div></div></div></a></div>
</div>
<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><a class="text-decoration-none" href="<?= url('/pos/customers') ?>"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-users"></i></div><div><div class="val"><?= (int)$stats['customers'] ?></div><div class="lbl">Customers</div></div></div></a></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-hand-holding-dollar"></i></div><div><div class="val" style="font-size:1rem"><?= money($stats['receivables']) ?></div><div class="lbl">Customer Receivables</div></div></div></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/pos/suppliers') ?>"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-file-invoice-dollar"></i></div><div><div class="val" style="font-size:1rem"><?= money($stats['payables']) ?></div><div class="lbl">Supplier Payables</div></div></div></a></div>
  <div class="col"><a class="text-decoration-none" href="<?= url('/pos/returns') ?>"><div class="kpi-card"><div class="ic ic-teal"><i class="fa-solid fa-rotate-left"></i></div><div><div class="val"><i class="fa-solid fa-rotate-left"></i></div><div class="lbl">Returns Desk</div></div></div></a></div>
</div>
<div class="row g-4">
  <div class="col-lg-8"><div class="ek-card p-4"><h6 class="fw-bold mb-3">Sales — Last 14 Days</h6><canvas id="posChart" height="90"></canvas></div></div>
  <div class="col-lg-4"><div class="ek-card p-4"><h6 class="fw-bold mb-3">Top Products</h6>
    <?php foreach ($top as $t): ?>
    <div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-truncate"><?= e($t['name']) ?></span><span><?= (int)$t['q'] ?> sold</span><b class="text-success"><?= money($t['amt']) ?></b></div>
    <?php endforeach; ?>
    <?php if (!$top): ?><p class="text-muted small mb-0">No completed sales yet — open the terminal and make your first sale!</p><?php endif; ?>
  </div></div>
</div>
<script>
ekChart('posChart', { type: 'line', data: { labels: <?= json_encode(array_column($daily, 'd')) ?>, datasets: [{ label: 'Sales', data: <?= json_encode(array_map('floatval', array_column($daily, 't'))) ?>, borderColor: '#0B7A3E', backgroundColor: 'rgba(11,122,62,.12)', fill: true, tension: .3 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
</script>
