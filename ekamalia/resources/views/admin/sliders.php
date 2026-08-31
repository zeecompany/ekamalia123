<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Add Hero Slide</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/sliders') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title"></div>
        <div class="mb-2"><label class="form-label">Subtitle</label><input class="form-control" name="subtitle"></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Button Text</label><input class="form-control" name="button_text"></div>
          <div class="col-6"><label class="form-label">Button URL</label><input class="form-control" name="button_url"></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Desktop Image *</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
          <div class="col-6"><label class="form-label">Mobile Image</label><input type="file" class="form-control" name="mobile_image" accept="image/*"></div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-4"><label class="form-label">Animation</label><select class="form-select" name="animation"><option value="fade">Fade</option><option value="slide">Slide</option><option value="zoom">Zoom</option></select></div>
          <div class="col-4"><label class="form-label">Start</label><input type="date" class="form-control" name="start_date"></div>
          <div class="col-4"><label class="form-label">End</label><input type="date" class="form-control" name="end_date"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill">Add Slide</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Current Slides (<?= count($sliders) ?>)</h6>
    <?php foreach ($sliders as $s): ?>
    <div class="admin-card p-3 mb-2 d-flex gap-3 align-items-center flex-wrap">
      <img src="<?= e(upload_url($s['image'])) ?>" style="width:110px;height:56px;object-fit:cover;border-radius:10px" alt="">
      <div class="flex-grow-1"><b class="small"><?= e($s['title'] ?? 'Untitled') ?></b>
        <div class="text-muted" style="font-size:.68rem"><?= e($s['subtitle'] ?? '') ?><?= $s['button_text'] ? ' • [' . e($s['button_text']) . ']' : '' ?></div>
        <div class="mt-1"><?= $s['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?>
          <span class="text-muted" style="font-size:.66rem">sort <?= (int)$s['sort_order'] ?></span></div></div>
      <form class="d-flex gap-1 align-items-center" method="post" action="<?= url('/admin/sliders/toggle') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>">
        <input type="hidden" name="status" value="<?= $s['status'] === 'active' ? 'inactive' : 'active' ?>">
        <button class="btn btn-sm btn-outline-warning"><?= $s['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
      </form>
      <form method="post" action="<?= url('/admin/sliders/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('Delete slide?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; ?>
    <?php if (!$sliders): ?><p class="text-muted small">No slides — the hero will show a default gradient.</p><?php endif; ?>
  </div>
</div>
