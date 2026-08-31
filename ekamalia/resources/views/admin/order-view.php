<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h4 fw-bold mb-0"><?= e($order['order_number']) ?> <?= status_badge($order['status']) ?> <?= status_badge($order['payment_status']) ?></h1>
    <div class="text-muted small">Group <?= e($order['group_number'] ?? $order['order_number']) ?> • placed <?= e(fmt_date($order['created_at'])) ?> by
      <a href="<?= url('/admin/users/' . $order['user_id']) ?>"><?= e($order['buyer_name']) ?></a> (<?= e($order['buyer_email']) ?>, <?= e(pk_phone($order['buyer_phone2'] ?? $order['ship_phone'])) ?>)</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm rounded-pill" href="<?= url('/admin/orders') ?>">All orders</a>
    <a class="btn btn-ek-sm" href="<?= url('/order/' . $order['order_number']) ?>" target="_blank"><i class="fa-solid fa-eye me-1"></i>Customer view</a>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="admin-card mb-3"><div class="ac-head"><h6>Items (<?= count($items) ?>) — Shop: <?= e($order['shop_name'] ?? 'Direct') ?></h6></div><div class="ac-body p-0">
      <table class="table mb-0 align-middle">
        <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><a class="text-decoration-none text-dark small fw-semibold" href="<?= url('/admin/products') ?>"><?= e($it['product_name'] ?? 'Item #' . $it['product_id']) ?></a></td>
          <td><?= (int)$it['quantity'] ?></td>
          <td><?= money($it['unit_price']) ?></td>
          <td class="text-end"><b><?= money($it['line_total']) ?></b></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="ac-body border-top">
        <div class="row">
          <div class="col-md-6"><div class="small"><b>Ship to:</b><br><?= e($order['ship_name']) ?><br><?= e($order['ship_phone']) ?><br><?= e($order['ship_address']) ?>, <?= e($order['ship_city']) ?></div></div>
          <div class="col-md-6 text-md-end">
            <div class="small">Subtotal: <?= money($order['subtotal']) ?><br>Shipping: <?= money($order['delivery_fee']) ?>
              <?= (float)$order['discount'] > 0 ? '<br>Discount' . ($order['coupon_code'] ? ' (' . e($order['coupon_code']) . ')' : '') . ': -' . money($order['discount']) : '' ?>
            </div>
            <div class="fs-5 fw-bold text-success mt-1">Total: <?= money($order['total']) ?></div>
          </div>
        </div>
      </div>
    </div></div>

    <div class="admin-card"><div class="ac-head"><h6>Status Timeline</h6></div><div class="ac-body p-0">
      <ul class="list-unstyled p-3 mb-0">
        <?php foreach ($history as $h): ?>
        <li class="d-flex gap-3 pb-3">
          <span class="flex-shrink-0 mt-1"><span class="badge text-bg-light border"><?= e(order_status_label($h['status'])) ?></span></span>
          <div class="small"><?= e($h['note'] ?? '') ?><div class="text-muted" style="font-size:.68rem"><?= e(fmt_date($h['created_at'])) ?><?= $h['by_name'] ? ' • by ' . e($h['by_name']) : '' ?></div></div>
        </li>
        <?php endforeach; ?>
        <?php if (!$history): ?><li class="text-muted small">No history.</li><?php endif; ?>
      </ul>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="admin-card mb-3"><div class="ac-head"><h6>Update Status</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/orders/' . $order['id'] . '/status') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <select class="form-select mb-2" name="status">
          <?php foreach (order_statuses() as $s): ?><option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= order_status_label($s) ?></option><?php endforeach; ?>
        </select>
        <input class="form-control mb-2" name="note" placeholder="Note (optional)">
        <button class="btn btn-ek w-100 rounded-pill">Update Order</button>
        <div class="text-muted mt-2" style="font-size:.68rem">Cancelling/returning refunds & restocks automatically; buyer is notified.</div>
      </form>
    </div></div>
    <?php if ($payment): ?>
    <div class="admin-card"><div class="ac-head"><h6>Payment — <?= strtoupper($payment['method']) ?></h6><?= status_badge($payment['status']) ?></div><div class="ac-body">
      <div class="small mb-2">
        <?php if ($payment['bank_name']): ?><b><?= e($payment['bank_name']) ?></b><br><?php endif; ?>
        <?php if ($payment['transaction_id']): ?>Ref: <b><?= e($payment['transaction_id']) ?></b><br><?php endif; ?>
        Amount: <b><?= money($payment['amount']) ?></b> • <?= e(fmt_date($payment['created_at'])) ?>
      </div>
      <?php if ($payment['proof_image']): ?>
        <a href="<?= e(upload_url($payment['proof_image'])) ?>" target="_blank"><img src="<?= e(upload_url($payment['proof_image'])) ?>" class="img-fluid rounded-3 border mb-2" style="max-height:220px" alt="Receipt"></a>
      <?php endif; ?>
      <?php if ($payment['status'] === 'submitted'): ?>
      <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm flex-fill" onclick="adminAction('<?= url('/admin/payments/' . $payment['id'] . '/update') ?>', {action:'verify'})"><i class="fa-solid fa-check me-1"></i>Verify</button>
        <button class="btn btn-outline-danger btn-sm flex-fill" onclick="const n=prompt('Rejection reason:'); if(n!==null) adminAction('<?= url('/admin/payments/' . $payment['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      </div>
      <?php else: ?><div class="text-muted" style="font-size:.7rem"><?= $payment['note'] ? e($payment['note']) : '' ?></div><?php endif; ?>
    </div></div>
    <?php endif; ?>
    <?php if (!empty($order['note'])): ?><div class="alert alert-warning small mt-3"><b>Buyer note:</b> <?= e($order['note']) ?></div><?php endif; ?>
  </div>
</div>
