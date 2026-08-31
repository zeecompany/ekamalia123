<div class="container py-4" style="max-width:760px">
  <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-building-columns me-2 text-success"></i>Bank Payment — <?= e($order['order_number']) ?></h1>
  <p class="text-muted small">Amount to transfer: <b class="text-success fs-5"><?= money($order['total']) ?></b></p>

  <?php if ($payment && $payment['status'] === 'verified'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>Payment verified! Your order is being processed.</div>
  <?php else: ?>
  <div class="ek-card p-4 mb-3">
    <h5 class="fw-bold mb-2">Step 1 — Transfer the amount</h5>
    <p class="text-muted small"><?= e(setting('payment_instructions')) ?></p>
    <?php $showBanks = $banks ?: []; if ($payment && $payment['bank_account_id']) { $sel = array_values(array_filter($showBanks, fn($b) => $b['id'] == $payment['bank_account_id'])); $showBanks = $sel ?: $showBanks; } ?>
    <?php foreach ($showBanks as $b): ?>
    <div class="border rounded-4 p-3 mb-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <b><i class="fa-solid fa-building-columns text-success me-1"></i><?= e($b['bank_name']) ?></b>
          <div class="small">Account Title: <b><?= e($b['account_title']) ?></b></div>
          <div class="small">Account #: <b class="user-select-all"><?= e($b['account_number']) ?></b></div>
          <?php if ($b['iban']): ?><div class="small">IBAN: <b class="user-select-all"><?= e($b['iban']) ?></b></div><?php endif; ?>
          <?php if ($b['branch']): ?><div class="small text-muted">Branch: <?= e($b['branch']) ?></div><?php endif; ?>
        </div>
        <span class="badge text-bg-light border">Reference: <b><?= e($order['order_number']) ?></b></span>
      </div>
      <?php if ($b['instructions']): ?><div class="small text-muted mt-2"><?= e($b['instructions']) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="ek-card p-4">
    <h5 class="fw-bold mb-2">Step 2 — Upload your receipt</h5>
    <?php if ($payment && $payment['status'] === 'submitted'): ?>
      <div class="alert alert-warning mb-0">
        <i class="fa-solid fa-hourglass-half me-2"></i>Receipt received! We're verifying your payment of <b><?= money($order['total']) ?></b> — usually done within a few hours.
        <?php if ($payment['proof_image']): ?><hr class="my-2"><img src="<?= e(upload_url($payment['proof_image'])) ?>" style="max-height:180px;border-radius:12px" alt="Receipt"><?php endif; ?>
      </div>
    <?php else: ?>
    <form method="post" action="<?= url('/payment/' . $order['order_number']) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label fw-semibold">Transaction ID / Last 4 digits *</label>
          <input class="form-control" name="transaction_id" required placeholder="e.g. 839201 or TXN-99182"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Payment Receipt (screenshot) *</label>
          <input type="file" class="form-control" name="proof" accept="image/jpeg,image/png,image/webp" required></div>
      </div>
      <button class="btn btn-ek btn-lg w-100 rounded-pill mt-3"><i class="fa-solid fa-upload me-2"></i>Submit Payment Proof</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <a class="btn btn-link mt-3" href="<?= url('/order/' . $order['order_number']) ?>">View order status →</a>
</div>
