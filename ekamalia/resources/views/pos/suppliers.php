<div class="row g-4">
  <div class="col-lg-4">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Add Supplier</h5>
      <form method="post" action="<?= url('/pos/suppliers/save') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small fw-semibold">Name *</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Phone</label><input type="tel" class="form-control" name="phone"></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Company</label><input class="form-control" name="company"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Opening Balance (Rs)</label><input type="number" class="form-control" name="balance" value="0"></div>
        <button class="btn btn-ek w-100 rounded-pill">Save Supplier</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <h5 class="fw-bold mb-3">Suppliers (<?= count($suppliers) ?>)</h5>
    <?php foreach ($suppliers as $s): ?>
    <div class="ek-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div><b><?= e($s['name']) ?></b> <?= $s['company'] ? '<span class="text-muted small">' . e($s['company']) . '</span>' : '' ?>
        <div class="text-muted small"><?= e(pk_phone($s['phone'] ?? '')) ?></div></div>
      <b class="<?= (float)$s['balance'] > 0 ? 'text-danger' : 'text-success' ?>"><?= money($s['balance']) ?> <?= (float)$s['balance'] > 0 ? 'payable' : 'clear' ?></b>
    </div>
    <?php endforeach; ?>
    <?php if (!$suppliers): ?><p class="text-muted small">No suppliers yet.</p><?php endif; ?>
  </div>
</div>
