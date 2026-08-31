<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Shop Orders</h1>
  <form class="d-flex gap-2" method="get"><input class="form-control form-control-sm" name="q" value="<?= e($q ?? '') ?>" placeholder="Order #"><button class="btn btn-ek-sm">Search</button></form>
</div>
<div class="d-flex gap-2 flex-wrap mb-3">
  <a class="chip <?= $status === '' ? 'active' : '' ?>" href="<?= url('/seller/orders') ?>">All</a>
  <?php foreach (order_statuses() as $s): if (empty($counts[$s])) continue; ?>
    <a class="chip <?= $status === $s ? 'active' : '' ?>" href="?status=<?= $s ?>"><?= order_status_label($s) ?> (<?= $counts[$s] ?>)</a>
  <?php endforeach; ?>
</div>
<?php if ($orders): ?>
<div class="table-responsive ek-card table-responsive-stack">
<table class="table align-middle">
  <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
  <tr>
    <td data-label="Order"><b class="text-nowrap"><?= e($o['order_number']) ?></b><div class="text-muted" style="font-size:.68rem"><?= e(fmt_date($o['created_at'])) ?></div></td>
    <td data-label="Customer"><span class="small fw-semibold"><?= e($o['buyer_name']) ?></span><div class="text-muted" style="font-size:.68rem"><?= e(pk_phone($o['ship_phone'] ?? '')) ?><br><?= e(mb_substr((string)$o['ship_address'], 0, 34)) ?>…</div></td>
    <td data-label="Total"><b class="text-success"><?= money($o['total']) ?></b></td>
    <td data-label="Payment"><?= status_badge($o['payment_status']) ?><div class="text-muted" style="font-size:.66rem"><?= strtoupper($o['payment_method']) ?></div></td>
    <td data-label="Status"><?= status_badge($o['status']) ?></td>
    <td data-label="Update">
      <form class="d-flex gap-1" method="post" action="<?= url('/seller/orders/status') ?>">
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= $o['id'] ?>">
        <select class="form-select form-select-sm" name="status" style="width:150px">
          <?php foreach (order_statuses() as $s): if (in_array($s, ['refund_requested'], true)) continue; ?>
            <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= order_status_label($s) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ek-sm">Save</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-truck"></i><h5>No orders here</h5></div><?php endif; ?>
