<?php
$flow = ['pending' => 'Order Placed', 'confirmed' => 'Seller Confirmed', 'processing' => 'Preparing', 'packed' => 'Packed', 'dispatched' => 'Dispatched', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered'];
$curIdx = array_search($order['status'], array_keys($flow), true);
$isEnd = in_array($order['status'], ['cancelled', 'returned', 'refunded'], true);
?>
<div class="container py-4" style="max-width:860px">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h1 class="h4 fw-bold mb-0">Order <?= e($order['order_number']) ?></h1>
      <div class="text-muted small">Placed <?= e(fmt_date($order['created_at'])) ?> • <?= e($order['shop_name'] ?: 'eKamalia') ?></div>
    </div>
    <?= status_badge($order['status']) ?>
  </div>

  <?php if ($siblings): ?>
  <div class="alert alert-light border small d-flex flex-wrap gap-2 align-items-center">
    <i class="fa-solid fa-layer-group me-1"></i> Multi-shop order <b><?= e($order['group_number']) ?></b>:
    <?php foreach ($siblings as $sib): ?><a href="<?= url('/order/' . $sib['order_number']) ?>" class="chip"> <?= e($sib['order_number']) ?> <?= status_badge($sib['status']) ?></a><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="ek-card p-4 mb-3">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-route me-1"></i>Order Tracking</h5>
        <?php if ($isEnd): ?>
          <div class="alert alert-<?= $order['status'] === 'cancelled' ? 'secondary' : 'warning' ?> mb-0">
            <b><?= order_status_label($order['status']) ?></b> — <?= e($history ? (string)end($history)['note'] : '') ?>
          </div>
        <?php else: ?>
        <div class="tl">
          <?php foreach ($flow as $key => $label):
              $idx = array_search($key, array_keys($flow), true); ?>
          <div class="tl-item <?= $idx < $curIdx ? 'done' : ($idx == $curIdx ? 'now' : '') ?>">
            <div class="dot"><i class="fa-solid <?= $idx <= $curIdx ? 'fa-check' : 'fa-circle' ?>" style="font-size:.55rem"></i></div>
            <div class="fw-semibold small"><?= $label ?></div>
            <?php $step = null; foreach ($history as $h) if ($h['status'] === $key) $step = $h; ?>
            <?php if ($step): ?><div class="when"><i class="fa-regular fa-clock me-1"></i><?= e(fmt_date($step['created_at'])) ?><?= $step['note'] ? ' — ' . e($step['note']) : '' ?></div><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="ek-card p-4 mb-3">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-box me-1"></i>Items (<?= count($items) ?>)</h5>
        <?php foreach ($items as $it): ?>
        <div class="d-flex gap-3 align-items-center py-2 border-bottom">
          <img src="<?= e(img_or($it['image'] ?? '')) ?>" style="width:56px;height:56px;border-radius:10px;object-fit:cover" alt="">
          <div class="flex-grow-1">
            <?php if ($it['product_slug']): ?><a class="small fw-semibold" href="<?= url('/product/' . $it['product_slug']) ?>"><?= e($it['name']) ?></a>
            <?php else: ?><span class="small fw-semibold"><?= e($it['name']) ?></span><?php endif; ?>
            <div class="text-muted" style="font-size:.74rem"><?= money($it['price']) ?> × <?= (int)$it['quantity'] ?></div>
          </div>
          <b class="small"><?= money($it['total']) ?></b>
        </div>
        <?php endforeach; ?>
        <div class="small mt-3">
          <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
          <?php if ($order['discount'] > 0): ?><div class="d-flex justify-content-between text-success"><span>Discount<?= $order['coupon_code'] ? ' (' . e($order['coupon_code']) . ')' : '' ?></span><span>-<?= money($order['discount']) ?></span></div><?php endif; ?>
          <div class="d-flex justify-content-between"><span class="text-muted">Delivery</span><span><?= $order['shipping_fee'] > 0 ? money($order['shipping_fee']) : 'FREE' ?></span></div>
          <div class="d-flex justify-content-between fw-bold fs-5 mt-1"><span>Total</span><span class="text-success"><?= money($order['total']) ?></span></div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="ek-card p-4 mb-3">
        <h6 class="fw-bold mb-2"><i class="fa-solid fa-money-bill-wave me-1"></i>Payment</h6>
        <div class="small">
          <div class="d-flex justify-content-between py-1"><span class="text-muted">Method</span><b><?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Bank Transfer' ?></b></div>
          <div class="d-flex justify-content-between py-1"><span class="text-muted">Status</span><?= status_badge($order['payment_status']) ?></div>
          <?php if ($payment && $payment['transaction_id']): ?><div class="d-flex justify-content-between py-1"><span class="text-muted">Transaction</span><span class="text-truncate" style="max-width:130px"><?= e($payment['transaction_id']) ?></span></div><?php endif; ?>
        </div>
        <?php if ($order['payment_method'] === 'bank_transfer' && in_array($order['payment_status'], ['pending', 'rejected'], true)): ?>
          <a class="btn btn-warning w-100 rounded-pill mt-2" href="<?= url('/payment/' . $order['order_number']) ?>"><i class="fa-solid fa-upload me-2"></i><?= $order['payment_status'] === 'rejected' ? 'Re-upload Receipt' : 'Upload Payment Receipt' ?></a>
        <?php endif; ?>
      </div>

      <div class="ek-card p-4 mb-3">
        <h6 class="fw-bold mb-2"><i class="fa-solid fa-location-dot me-1"></i>Delivery Address</h6>
        <div class="small">
          <b><?= e($order['ship_name']) ?></b><br><?= e(pk_phone($order['ship_phone'] ?? '')) ?><br>
          <?= e($order['ship_address']) ?>, <?= e($order['ship_area'] ? $order['ship_area'] . ', ' : '') . e($order['ship_city']) ?>
          <?php if ($order['note']): ?><div class="text-muted mt-2"><i class="fa-regular fa-note-sticky me-1"></i><?= e($order['note']) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="ek-card p-4">
        <h6 class="fw-bold mb-2">Actions</h6>
        <div class="d-grid gap-2">
          <?php if ($isBuyer && in_array($order['status'], ['pending', 'confirmed'], true)): ?>
            <button class="btn btn-outline-danger btn-sm rounded-pill" onclick="orderAction('cancel')">Cancel Order</button>
          <?php endif; ?>
          <?php if ($isBuyer && $order['status'] === 'delivered'): ?>
            <button class="btn btn-success btn-sm rounded-pill" onclick="orderAction('received')"><i class="fa-solid fa-check me-1"></i>I received this order</button>
            <button class="btn btn-outline-warning btn-sm rounded-pill" onclick="orderAction('refund')">Request Refund</button>
          <?php endif; ?>
          <?php if ($order['shop_id']): ?>
            <a class="btn btn-outline-success btn-sm rounded-pill" href="<?= url('/shops/' . $order['shop_id'] . '/chat') ?>"><i class="fa-regular fa-comment-dots me-1"></i>Contact Seller</a>
          <?php endif; ?>
          <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print Invoice</button>
        </div>
        <div class="text-muted mt-3" style="font-size:.72rem">Estimated delivery: <?= e($order['estimated_delivery'] ?: '2-3 working days') ?></div>
      </div>
    </div>
  </div>
</div>
<script>
function orderAction(act) {
  let msg = act === 'cancel' ? 'Cancel this order?' : act === 'refund' ? 'Request a refund for this order?' : 'Confirm you received this order?';
  if (!ekConfirm(msg)) return;
  ekPost('<?= url('/order/' . $order['order_number']) ?>/' + act, {}).then(r => { toast(r.message || 'Done', r.ok ? 'success' : 'danger'); if (r.ok) location.reload(); });
}
</script>
