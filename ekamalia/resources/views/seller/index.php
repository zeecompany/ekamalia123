<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-store me-2 text-success"></i><?= e($shop['name']) ?></h1>
    <div class="text-muted small"><?= e($shop['is_verified'] ? '✅ Verified Shop' : 'Shop') ?> • <?= e($shop['city_name'] ?? 'Kamalia') ?></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/seller/products/create') ?>"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
    <a class="btn btn-outline-success rounded-pill px-3" target="_blank" href="<?= url('/shop/' . $shop['slug']) ?>"><i class="fa-solid fa-eye me-1"></i>View Shop</a>
  </div>
</div>
<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-box-open"></i></div><div><div class="val"><?= $stats['products'] ?></div><div class="lbl">Products</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-truck-fast"></i></div><div><div class="val"><?= $stats['new_orders'] ?></div><div class="lbl">New Orders</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-sack-dollar"></i></div><div><div class="val" style="font-size:1rem"><?= money($stats['revenue']) ?></div><div class="lbl">Delivered Revenue</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="val"><?= $stats['low_stock'] ?></div><div class="lbl">Low Stock Alerts</div></div></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="ek-card p-4 mb-3">
      <h5 class="fw-bold mb-3">Sales — Last 14 Days</h5>
      <canvas id="salesChart" height="90"></canvas>
    </div>
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Top Products</h5>
      <?php foreach ($topProducts as $tp): ?>
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span class="small fw-semibold text-truncate" style="max-width:60%"><?= e($tp['name']) ?></span>
        <span class="text-muted small"><?= (int)$tp['q'] ?> sold</span>
        <b class="small text-success"><?= money($tp['amt']) ?></b>
      </div>
      <?php endforeach; ?>
      <?php if (!$topProducts): ?><p class="text-muted small mb-0">No sales yet — promote your shop!</p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="ek-card p-4 mb-3">
      <h5 class="fw-bold mb-3">Latest Orders</h5>
      <?php foreach ($recentOrders as $o): ?>
      <a class="d-flex justify-content-between align-items-center py-2 border-bottom text-decoration-none text-dark" href="<?= url('/seller/orders') ?>">
        <div><b class="small"><?= e($o['order_number']) ?></b><div class="text-muted" style="font-size:.7rem"><?= e($o['buyer_name']) ?> • <?= e(time_ago($o['created_at'])) ?></div></div>
        <?= status_badge($o['status']) ?>
      </a>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><p class="text-muted small mb-0">No orders yet.</p><?php endif; ?>
    </div>
    <div class="ek-card p-4 border-warning">
      <h6 class="fw-bold text-warning mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Low Stock</h6>
      <?php foreach ($lowStock as $ls): ?>
      <div class="d-flex justify-content-between py-1 border-bottom small"><span class="text-truncate"><?= e($ls['name']) ?></span><b class="text-danger"><?= (int)$ls['stock'] ?></b></div>
      <?php endforeach; ?>
      <?php if (!$lowStock): ?><p class="text-muted small mb-0">All stocked up 👍</p><?php endif; ?>
    </div>
  </div>
</div>
<script>
ekChart('salesChart', { type: 'line', data: { labels: <?= json_encode(array_column($sales7, 'd')) ?>, datasets: [{ label: 'Sales (Rs)', data: <?= json_encode(array_map('floatval', array_column($sales7, 't'))) ?>, borderColor: '#0B7A3E', backgroundColor: 'rgba(11,122,62,.1)', fill: true, tension: .35 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
</script>
