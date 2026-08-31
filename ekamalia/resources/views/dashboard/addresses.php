<h1 class="h4 fw-bold mb-4">Delivery Addresses</h1>
<div class="row g-4">
  <div class="col-lg-7">
    <?php if ($addresses): foreach ($addresses as $a): ?>
    <div class="ek-card p-3 mb-2 d-flex align-items-start gap-3">
      <i class="fa-solid fa-location-dot text-success mt-1"></i>
      <div class="flex-grow-1">
        <b class="small"><?= e($a['label'] ?: 'Address') ?></b> <?php if ($a['is_default']): ?><span class="badge text-bg-success">Default</span><?php endif; ?>
        <div class="small text-muted"><?= e($a['name']) ?> • <?= e(pk_phone($a['phone'])) ?><br><?= e($a['address']) ?>, <?= e($a['city_name'] ?? '') ?></div>
      </div>
      <form method="post" action="<?= url('/dashboard/addresses/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Remove this address?')"><?= csrf_field() ?>
        <button class="btn btn-link btn-sm text-danger p-0"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; else: ?><p class="text-muted small">No addresses saved yet.</p><?php endif; ?>
  </div>
  <div class="col-lg-5">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Add New Address</h5>
      <form method="post" action="<?= url('/dashboard/addresses') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small fw-semibold">Label</label><input class="form-control form-control-sm" name="label" placeholder="Home / Shop"></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Receiver name *</label><input class="form-control form-control-sm" name="name" required></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Phone *</label><input class="form-control form-control-sm" name="phone" placeholder="03XX-XXXXXXX" required></div>
        <div class="mb-2"><label class="form-label small fw-semibold">City</label>
          <select class="form-select form-select-sm" name="city_id"><option value="">Select</option>
          <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Area</label><input class="form-control form-control-sm" name="area"></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Complete address *</label><textarea class="form-control form-control-sm" name="address" rows="2" required></textarea></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_default" value="1" checked><label class="form-check-label small">Set as default</label></div>
        <button class="btn btn-ek rounded-pill px-4 w-100">Save Address</button>
      </form>
    </div>
  </div>
</div>
