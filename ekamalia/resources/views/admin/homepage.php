<h1 class="h4 fw-bold mb-1">Homepage Builder</h1>
<p class="text-muted mb-4">Enable, disable and reorder homepage sections. Changes apply instantly.</p>
<form method="post" action="<?= url('/admin/homepage') ?>">
  <?= csrf_field() ?>
  <div class="admin-card" style="max-width:640px"><div class="ac-body p-0">
    <?php foreach ($sections as $s): ?>
    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
      <span class="badge text-bg-light border"><i class="fa-solid fa-grip-vertical"></i></span>
      <div class="flex-grow-1">
        <b class="small"><?= e(ucwords(str_replace('_', ' ', $s['section_key']))) ?></b>
        <div class="text-muted" style="font-size:.66rem"><?= e($s['title'] ?? '') ?></div>
      </div>
      <input type="number" class="form-control form-control-sm" name="sections[<?= $s['id'] ?>][sort]" value="<?= (int)$s['sort_order'] ?>" style="width:76px" title="Sort order">
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" name="sections[<?= $s['id'] ?>][enabled]" value="1" id="sec-<?= $s['id'] ?>" <?= $s['is_enabled'] ? 'checked' : '' ?>>
        <label class="form-check-label small" for="sec-<?= $s['id'] ?>"><?= $s['is_enabled'] ? 'On' : 'Off' ?></label>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <button class="btn btn-ek rounded-pill px-5 mt-3">Save Homepage</button>
</form>
