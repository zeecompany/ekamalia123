<div class="row g-4">
  <div class="col-lg-5">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Create Coupon</h5>
      <form method="post" action="<?= url('/seller/coupons/save') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small fw-semibold">Code *</label><input class="form-control text-uppercase" name="code" placeholder="EID20" required></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label small fw-semibold">Type</label>
            <select class="form-select" name="type"><option value="percent">Percent %</option><option value="fixed">Fixed Rs</option><option value="free_delivery">Free Delivery</option></select></div>
          <div class="col-6"><label class="form-label small fw-semibold">Value</label><input type="number" class="form-control" name="value" min="0"></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label small fw-semibold">Min Order (Rs)</label><input type="number" class="form-control" name="min_order" value="0"></div>
          <div class="col-6"><label class="form-label small fw-semibold">Max Uses</label><input type="number" class="form-control" name="max_uses" value="100"></div>
        </div>
        <div class="mb-3"><label class="form-label small fw-semibold">Expires On</label><input type="date" class="form-control" name="expires_at"></div>
        <button class="btn btn-ek w-100 rounded-pill">Create Coupon</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <h5 class="fw-bold mb-3">My Coupons (<?= count($coupons) ?>)</h5>
    <?php foreach ($coupons as $c): ?>
    <div class="ek-card p-3 mb-2 d-flex align-items-center gap-3 flex-wrap">
      <i class="fa-solid fa-ticket fs-4 text-danger"></i>
      <div class="flex-grow-1">
        <b><?= e($c['code']) ?></b> <?= status_badge($c['status']) ?>
        <div class="text-muted small">
          <?= $c['type'] === 'percent' ? (int)$c['value'] . '% off' : ($c['type'] === 'fixed' ? money($c['value']) . ' off' : 'Free delivery') ?>
          • min <?= money($c['min_order']) ?> • used <?= (int)$c['used'] ?><?= $c['max_uses'] ? '/' . (int)$c['max_uses'] : '' ?>
          <?= $c['expires_at'] ? '• till ' . e(fmt_date($c['expires_at'], 'd M Y')) : '' ?>
        </div>
      </div>
      <form method="post" action="<?= url('/seller/coupons/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Remove coupon?')"><?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; ?>
    <?php if (!$coupons): ?><p class="text-muted small">No coupons yet — create one to attract buyers!</p><?php endif; ?>
  </div>
</div>
