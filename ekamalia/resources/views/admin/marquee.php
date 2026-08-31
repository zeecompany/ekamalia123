<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Add Marquee Message</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/marquee') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Type</label><select class="form-select" name="type"><option value="announcement">📢 Announcement</option><option value="offer">🏷 Offer</option></select></div>
        <div class="mb-2"><label class="form-label">Text * (max 255)</label><input class="form-control" name="text" maxlength="255" required></div>
        <div class="mb-2"><label class="form-label">Link URL (optional)</label><input class="form-control" name="url"></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date"></div>
          <div class="col-6"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill">Save Message</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Messages (<?= count($marquees) ?>)</h6>
    <?php foreach ($marquees as $m): ?>
    <div class="admin-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div><span class="badge <?= $m['type'] === 'offer' ? 'text-bg-warning' : 'text-bg-success' ?>"><?= $m['type'] === 'offer' ? '🏷' : '📢' ?> <?= e($m['type']) ?></span>
        <span class="small ms-1"><?= e($m['text']) ?></span>
        <?php if ($m['url']): ?><a class="small ms-1" href="<?= e($m['url']) ?>" target="_blank"><i class="fa-solid fa-link"></i></a><?php endif; ?>
        <div class="text-muted" style="font-size:.66rem"><?= $m['start_date'] ? 'from ' . e($m['start_date']) : '' ?><?= $m['end_date'] ? ' till ' . e($m['end_date']) : '' ?></div></div>
      <div class="d-flex gap-1">
        <form method="post" action="<?= url('/admin/marquee/toggle') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><input type="hidden" name="status" value="<?= $m['status'] === 'active' ? 'inactive' : 'active' ?>">
          <button class="btn btn-sm btn-outline-warning"><?= $m['status'] === 'active' ? 'Disable' : 'Enable' ?></button></form>
        <form method="post" action="<?= url('/admin/marquee/' . $m['id'] . '/delete') ?>" onsubmit="return confirm('Delete message?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$marquees): ?><p class="text-muted small">No marquee messages yet.</p><?php endif; ?>
  </div>
</div>
