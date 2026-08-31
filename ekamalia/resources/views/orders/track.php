<div class="container py-5" style="max-width:560px">
  <div class="ek-card p-4">
    <h1 class="h4 fw-bold text-center mb-1"><i class="fa-solid fa-truck-fast me-2 text-success"></i>Track Your Order</h1>
    <p class="text-muted text-center small mb-4">Enter your order number (e.g. EK-XXXXXX)</p>
    <form method="post" action="<?= url('/track') ?>">
      <?= csrf_field() ?>
      <div class="input-group input-group-lg mb-3">
        <input class="form-control text-uppercase" name="order_number" value="<?= e($searchNo ?? '') ?>" placeholder="EK-XXXXXX" required>
        <button class="btn btn-ek px-4">Track</button>
      </div>
    </form>
    <?php if (isset($order)): ?>
      <?php if ($order): ?>
      <div class="border rounded-4 p-3 bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <b><?= e($order['order_number']) ?></b><?= status_badge($order['status']) ?>
        </div>
        <div class="text-muted small mt-1">Payment: <?= status_badge($order['payment_status']) ?> • Placed <?= e(fmt_date($order['created_at'])) ?></div>
        <hr>
        <div class="tl mt-2">
          <?php foreach ($history as $h): ?>
          <div class="tl-item done"><div class="dot"><i class="fa-solid fa-check" style="font-size:.55rem"></i></div>
            <div class="fw-semibold small"><?= e(order_status_label($h['status'])) ?></div>
            <div class="when"><?= e(fmt_date($h['created_at'])) ?><?= $h['note'] ? ' — ' . e($h['note']) : '' ?></div></div>
          <?php endforeach; ?>
        </div>
        <?php if (auth()): ?><a class="btn btn-outline-success btn-sm rounded-pill mt-2" href="<?= url('/order/' . $order['order_number']) ?>">Full order details</a><?php endif; ?>
      </div>
      <?php else: ?>
      <div class="alert alert-warning mb-0"><i class="fa-regular fa-face-frown me-2"></i>No order found with number <b><?= e($searchNo) ?></b>.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
