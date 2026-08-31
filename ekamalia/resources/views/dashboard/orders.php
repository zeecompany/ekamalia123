<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold mb-0"><?= e(t('nav.my_orders')) ?></h1>
  <a class="btn btn-outline-secondary btn-sm rounded-pill" href="<?= url('/track') ?>"><i class="fa-solid fa-truck-fast me-1"></i>Track by number</a>
</div>
<?php if ($orders): ?>
<div class="table-responsive ek-card table-responsive-stack">
  <table class="table align-middle">
    <thead><tr><th>Order</th><th>Shop</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td data-label="Order"><b class="text-nowrap"><?= e($o['order_number']) ?></b><div class="text-muted" style="font-size:.7rem"><?= e(fmt_date($o['created_at'])) ?></div></td>
      <td data-label="Shop" class="small"><?= e($o['shop_name'] ?: 'eKamalia') ?></td>
      <td data-label="Items" class="small"><?= (int)$o['items_count'] ?></td>
      <td data-label="Total"><b class="text-success"><?= money($o['total']) ?></b></td>
      <td data-label="Payment"><?= status_badge($o['payment_status']) ?></td>
      <td data-label="Status"><?= status_badge($o['status']) ?></td>
      <td><a class="btn btn-sm btn-ek-sm rounded-pill" href="<?= url('/order/' . $o['order_number']) ?>">Details</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="ek-empty"><i class="fa-solid fa-box"></i><h5>No orders yet</h5><a class="btn btn-ek rounded-pill px-4" href="<?= url('/products') ?>">Browse Products</a></div>
<?php endif; ?>
