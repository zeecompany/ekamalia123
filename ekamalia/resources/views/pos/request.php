<div class="container py-5" style="max-width:720px">
  <?php if ($request && $request['status'] === 'pending'): ?>
    <div class="ek-card p-5 text-center">
      <div style="font-size:60px" class="text-warning mb-3"><i class="fa-solid fa-hourglass-half"></i></div>
      <h1 class="h4 fw-bold">POS Request Under Review</h1>
      <p class="text-muted">Your Premium POS request<?= $request['package_name'] ? ' for the <b>' . e($request['package_name']) . '</b> package' : '' ?> was submitted on <?= e(fmt_date($request['created_at'])) ?>.<br>Our team will activate your access soon — you'll get a notification &amp; email.</p>
    </div>
  <?php elseif ($request && $request['status'] === 'approved' && pos_access()): redirect('/pos'); ?>
  <?php else: ?>
  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:84px;height:84px;border-radius:26px;background:var(--ek-gradient);color:#fff;font-size:2.2rem;box-shadow:var(--ek-shadow-lg)"><i class="fa-solid fa-cash-register"></i></div>
    <h1 class="h3 fw-bold">eKamalia Premium POS</h1>
    <p class="text-muted">Professional billing &amp; inventory system for Kamalia businesses — <b>free for approved sellers</b>.</p>
  </div>
  <div class="row g-3 mb-4 row-cols-2 row-cols-md-4">
    <?php foreach ([['fa-bolt', 'Fast Billing', 'Barcode + search checkout'], ['fa-boxes-stacked', 'Inventory', 'Stock movements & alerts'], ['fa-chart-line', 'Reports', 'Sales, profit & expenses'], ['fa-users', 'Customers', 'Ledgers & receivables']] as $f): ?>
    <div class="col"><div class="ek-card p-3 text-center h-100"><i class="fa-solid <?= $f[0] ?> fs-4 text-success mb-2"></i><div class="fw-bold small"><?= $f[1] ?></div><div class="text-muted" style="font-size:.7rem"><?= $f[2] ?></div></div></div>
    <?php endforeach; ?>
  </div>
  <?php if ($request && $request['status'] === 'rejected'): ?>
    <div class="alert alert-warning small">Your previous request was declined. <?= e($request['admin_note'] ?? '') ?> You may apply again.</div>
  <?php endif; ?>
  <div class="ek-card p-4">
    <h5 class="fw-bold mb-3">Request POS Access</h5>
    <form method="post" action="<?= url('/pos/request') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label fw-semibold">Business Name *</label><input class="form-control" name="business_name" value="<?= e(old('business_name', my_shop()['name'] ?? '')) ?>" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Package</label>
          <select class="form-select" name="package_id"><option value="">Default (free starter)</option>
          <?php foreach ($packages as $pk): ?><option value="<?= $pk['id'] ?>"><?= e($pk['name']) ?> — <?= $pk['price'] > 0 ? money($pk['price']) : 'Free' ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label fw-semibold">Tell us about your business</label>
          <textarea class="form-control" name="note" rows="3" placeholder="What do you sell? How many daily sales?"><?= old('note') ?></textarea></div>
      </div>
      <button class="btn btn-ek btn-lg w-100 rounded-pill mt-3"><i class="fa-solid fa-paper-plane me-2"></i>Submit POS Request</button>
    </form>
  </div>
  <?php endif; ?>
</div>
