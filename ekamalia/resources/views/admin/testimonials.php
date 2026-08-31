<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Add Testimonial</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/testimonials') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Name *</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label">Comment *</label><textarea class="form-control" name="comment" rows="3" required></textarea></div>
        <div class="row g-2 mb-2">
          <div class="col-4"><label class="form-label">Rating</label><select class="form-select" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> ★</option><?php endfor; ?></select></div>
          <div class="col-8"><label class="form-label">Business</label><input class="form-control" name="business"></div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label">Photo</label><input type="file" class="form-control" name="photo" accept="image/*"></div>
          <div class="col-6"><label class="form-label">Sort</label><input type="number" class="form-control" name="sort_order" value="0"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill">Save Testimonial</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Testimonials (<?= count($items) ?>)</h6>
    <?php foreach ($items as $t): ?>
    <div class="admin-card p-3 mb-2 d-flex gap-3 align-items-center flex-wrap">
      <img src="<?= e($t['photo'] ? upload_url($t['photo']) : asset('img/avatar-default.svg')) ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover" alt="">
      <div class="flex-grow-1"><b class="small"><?= e($t['name']) ?></b> <?= $t['business'] ? '<span class="text-muted small">(' . e($t['business']) . ')</span>' : '' ?>
        <div class="stars small"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $t['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></div>
        <div class="text-muted small"><?= e(mb_substr((string)$t['comment'], 0, 90)) ?>…</div></div>
      <?= $t['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?>
      <form method="post" action="<?= url('/admin/testimonials/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Delete testimonial?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; ?>
    <?php if (!$items): ?><p class="text-muted small">No testimonials yet.</p><?php endif; ?>
  </div>
</div>
