<h1 class="h4 fw-bold mb-4">Orders <span class="text-muted fs-6">(<?= number_format($total) ?>)</span></h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <a class="chip <?= $status === '' ? 'active' : '' ?>" href="<?= url('/admin/orders') ?>">All</a>
  <?php foreach (order_statuses() as $s): ?>
  <a class="chip <?= $status === $s ? 'active' : '' ?>" href="?status=<?= $s ?>"><?= order_status_label($s) ?></a>
  <?php endforeach; ?>
</div>
<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <div class="col-md-5"><input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Order #, name or phone…"></div>
  <div class="col-md-3"><button class="btn btn-ek-sm">Search</button></div>
</form>
<?php if ($orders): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Order</th><th>Customer</th><th>Shop</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
  <tr>
    <td data-label="Order"><a class="fw-bold text-decoration-none" href="<?= url('/admin/orders/' . $o['id']) ?>"><?= e($o['order_number']) ?></a>
      <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($o['created_at'])) ?></div></td>
    <td data-label="Customer" class="small"><?= e($o['buyer_name']) ?><div class="text-muted" style="font-size:.68rem"><?= e($o['ship_city']) ?></div></td>
    <td data-label="Shop" class="small"><?= e($o['shop_name'] ?? '—') ?></td>
    <td data-label="Total"><b><?= money($o['total']) ?></b></td>
    <td data-label="Payment"><?= status_badge($o['payment_status']) ?><div class="text-muted" style="font-size:.66rem"><?= strtoupper($o['payment_method']) ?></div></td>
    <td data-label="Status"><?= status_badge($o['status']) ?></td>
    <td><a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/orders/' . $o['id']) ?>"><i class="fa-solid fa-chevron-right"></i></a></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if ($total > $perPage): ?>
<nav class="mt-3"><ul class="pagination pagination-sm">
  <?php $pages = (int)ceil($total / $perPage); for ($i = 1; $i <= min($pages, 30); $i++): ?>
  <li class="page-item <?= $i === (int)max(1, int_input('page', 1)) ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-boxes-stacked"></i><h5>No orders found</h5></div><?php endif; ?>
