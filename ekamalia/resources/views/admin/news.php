<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Publish News / Update</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/news') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Title *</label><input class="form-control" name="title" required></div>
        <div class="mb-2"><label class="form-label">Excerpt</label><textarea class="form-control" name="excerpt" rows="2" maxlength="400"></textarea></div>
        <div class="mb-2"><label class="form-label">Content (HTML)</label><textarea class="form-control" name="content" rows="8"></textarea></div>
        <div class="mb-2"><label class="form-label">Image *</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="status" value="draft" id="ndraft"><label class="form-check-label small" for="ndraft">Save as draft</label></div>
        <button class="btn btn-ek w-100 rounded-pill">Publish</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Articles (<?= count($news) ?>)</h6>
    <?php foreach ($news as $n): ?>
    <div class="admin-card p-3 mb-2 d-flex gap-3 align-items-center flex-wrap">
      <?php if ($n['image']): ?><img src="<?= e(upload_url($n['image'])) ?>" style="width:76px;height:52px;object-fit:cover;border-radius:8px" alt=""><?php endif; ?>
      <div class="flex-grow-1"><b class="small"><?= e($n['title']) ?></b>
        <div class="text-muted" style="font-size:.66rem"><?= e(fmt_date($n['published_at'] ?? $n['created_at'], 'd M Y')) ?> • <a href="<?= url('/news/' . $n['slug']) ?>" target="_blank">/news/<?= e($n['slug']) ?></a></div></div>
      <?= $n['status'] === 'published' ? '<span class="badge text-bg-success">Published</span>' : '<span class="badge text-bg-secondary">Draft</span>' ?>
      <form method="post" action="<?= url('/admin/news/' . $n['id'] . '/delete') ?>" onsubmit="return confirm('Delete article?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div>
    <?php endforeach; ?>
    <?php if (!$news): ?><p class="text-muted small">No articles yet.</p><?php endif; ?>
  </div>
</div>
