<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Add Advertisement</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/advertisements') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Title *</label><input class="form-control" name="title" required></div>
        <div class="mb-2"><label class="form-label">Click URL</label><input type="url" class="form-control" name="url"></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Position</label><select class="form-select" name="position">
            <option value="banner">Banner (top of listings)</option><option value="sidebar">Sidebar</option>
            <option value="category">Category page</option><option value="in_content">In content</option></select></div>
          <div class="col-6"><label class="form-label">Priority</label><input type="number" class="form-control" name="priority" value="0"></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Image *</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
          <div class="col-3"><label class="form-label">Start</label><input type="date" class="form-control" name="start_date"></div>
          <div class="col-3"><label class="form-label">End</label><input type="date" class="form-control" name="end_date"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill">Save Advertisement</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Running Ads (<?= count($ads) ?>)</h6>
    <?php foreach ($ads as $a): ?>
    <div class="admin-card p-3 mb-2 d-flex gap-3 align-items-center flex-wrap">
      <img src="<?= e(upload_url($a['image'])) ?>" style="width:110px;height:56px;object-fit:cover;border-radius:10px" alt="">
      <div class="flex-grow-1"><b class="small"><?= e($a['title']) ?></b>
        <div class="text-muted" style="font-size:.68rem"><?= e($a['position']) ?> • priority <?= (int)$a['priority'] ?> • CTR <?= number_format((float)$a['clicks'] / max(1, (int)$a['views']) * 100, 1) ?>% (<?= number_format((int)$a['clicks']) ?> clicks / <?= number_format((int)$a['views']) ?> views)</div>
        <div class="mt-1"><?= $a['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></div></div>
      <form method="post" action="<?= url('/admin/advertisements/toggle') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="status" value="<?= $a['status'] === 'active' ? 'inactive' : 'active' ?>">
        <button class="btn btn-sm btn-outline-warning"><?= $a['status'] === 'active' ? 'Disable' : 'Enable' ?></button></form>
      <form method="post" action="<?= url('/admin/advertisements/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Delete advertisement?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; ?>
    <?php if (!$ads): ?><p class="text-muted small">No advertisements yet.</p><?php endif; ?>
  </div>
</div>
