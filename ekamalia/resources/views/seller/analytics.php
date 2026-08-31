<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Shop Analytics</h1>
  <span class="badge text-bg-light border"><?= e($shop['name']) ?></span>
</div>
<div class="ek-card p-4 mb-3"><h6 class="fw-bold mb-3">Revenue & Orders — Last 30 Days</h6><canvas id="anChart" height="100"></canvas></div>
<div class="row g-4">
  <div class="col-md-6"><div class="ek-card p-4"><h6 class="fw-bold mb-3">Order Status Mix</h6><canvas id="statusChart" height="180"></canvas></div></div>
  <div class="col-md-6"><div class="ek-card p-4"><h6 class="fw-bold mb-3">Top Cities</h6>
    <?php foreach ($topCities as $tc): if (!$tc['city']) continue; ?>
    <div class="d-flex justify-content-between py-1 border-bottom small"><span><i class="fa-solid fa-location-dot text-success me-2"></i><?= e($tc['city']) ?></span><b><?= (int)$tc['c'] ?> orders</b></div>
    <?php endforeach; ?>
    <?php if (!$topCities): ?><p class="text-muted small mb-0">No city data yet.</p><?php endif; ?></div></div>
</div>
<div class="ek-card p-4 mt-3"><h6 class="fw-bold mb-3">Most Viewed Products</h6>
  <?php foreach ($productViews as $pv): ?>
  <div class="d-flex justify-content-between py-1 border-bottom small"><span class="text-truncate"><?= e($pv['name']) ?></span><b><?= number_format((int)$pv['views']) ?> views</b></div>
  <?php endforeach; ?>
</div>
<script>
const days = <?= json_encode(array_column($daily, 'd')) ?>;
ekChart('anChart', { data: { labels: days, datasets: [
  { type: 'line', label: 'Revenue (Rs)', data: <?= json_encode(array_map('floatval', array_column($daily, 'revenue'))) ?>, borderColor: '#0B7A3E', tension: .35, fill: false },
  { type: 'bar', label: 'Orders', data: <?= json_encode(array_map('intval', array_column($daily, 'orders'))) ?>, backgroundColor: 'rgba(240,180,41,.5)', yAxisID: 'y' }
] }, options: { scales: { y: { beginAtZero: true } } } });
ekChart('statusChart', { type: 'doughnut', data: { labels: <?= json_encode(array_map(fn($r) => order_status_label($r['status']), $statusMix)) ?>,
  datasets: [{ data: <?= json_encode(array_map('intval', array_column($statusMix, 'c'))) ?>, backgroundColor: ['#0B7A3E','#F0B429','#2563EB','#DC2626','#7C3AED','#0891B2','#65A30D','#64748B'] }] } });
</script>
