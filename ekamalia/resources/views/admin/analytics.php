<h1 class="h4 fw-bold mb-4">Analytics</h1>
<div class="row g-4 mb-4">
  <div class="col-lg-8"><div class="admin-card"><div class="ac-head"><h6>Orders & Revenue — 30 Days</h6></div><div class="ac-body"><canvas id="dChart" height="95"></canvas></div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Revenue — 12 Months</h6></div><div class="ac-body"><canvas id="mChart" height="230"></canvas></div></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>New Users — 30 Days</h6></div><div class="ac-body"><canvas id="uChart" height="150"></canvas></div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Top Searches</h6></div><div class="ac-body p-0">
    <?php foreach ($topSearches as $s): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><i class="fa-solid fa-magnifying-glass text-muted me-2"></i><?= e($s['query']) ?></span><b><?= (int)$s['c'] ?></b></div>
    <?php endforeach; ?>
    <?php if (!$topSearches): ?><p class="text-muted small p-3 mb-0">No search data yet.</p><?php endif; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Searches With No Results <i class="fa-solid fa-triangle-exclamation text-warning"></i></h6></div><div class="ac-body p-0">
    <?php foreach ($noResults as $n): ?>
    <div class="px-3 py-2 border-bottom small"><i class="fa-regular fa-face-frown text-muted me-2"></i><?= e($n['query']) ?></div>
    <?php endforeach; ?>
    <?php if (!$noResults): ?><p class="text-muted small p-3 mb-0">Nothing missing — great catalog coverage!</p><?php endif; ?>
  </div></div></div>
</div>
<div class="row g-4 mt-1">
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Most Viewed Products</h6></div><div class="ac-body p-0">
    <?php foreach ($topProducts as $p): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><?= e($p['name']) ?></span><b><?= number_format((int)$p['views']) ?></b></div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Most Viewed Ads</h6></div><div class="ac-body p-0">
    <?php foreach ($topAds as $a): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><?= e($a['title']) ?></span><b><?= number_format((int)$a['views']) ?></b></div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="admin-card"><div class="ac-head"><h6>Top Shops</h6></div><div class="ac-body p-0">
    <?php foreach ($topShops as $s): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><?= e($s['name']) ?></span><b class="text-success"><?= money($s['revenue']) ?></b></div>
    <?php endforeach; ?>
  </div></div></div>
</div>
<script>
const dLabels = <?= json_encode(array_column($daily, 'd')) ?>;
ekChart('dChart', { data: { labels: dLabels, datasets: [
  { type: 'line', label: 'Revenue (Rs)', data: <?= json_encode(array_map('floatval', array_column($daily, 'revenue'))) ?>, borderColor: '#0B7A3E', tension: .35 },
  { type: 'bar', label: 'Orders', data: <?= json_encode(array_map('intval', array_column($daily, 'orders'))) ?>, backgroundColor: 'rgba(240,180,41,.55)', yAxisID: 'y1' }
] }, options: { scales: { y: { beginAtZero: true }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } } } } });
ekChart('mChart', { type: 'bar', data: { labels: <?= json_encode(array_column($monthly, 'm')) ?>, datasets: [{ label: 'Revenue', data: <?= json_encode(array_map('floatval', array_column($monthly, 'revenue'))) ?>, backgroundColor: 'rgba(11,122,62,.75)', borderRadius: 6 }] }, options: { plugins: { legend: { display: false } } } });
ekChart('uChart', { type: 'line', data: { labels: <?= json_encode(array_column($newUsers, 'd')) ?>, datasets: [{ label: 'New users', data: <?= json_encode(array_map('intval', array_column($newUsers, 'c'))) ?>, borderColor: '#2456c4', tension: .3 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
</script>
