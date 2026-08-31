<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6><?= isset($editPage) ? 'Edit Page' : 'Add CMS Page' ?></h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/pages') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($editPage['id'] ?? 0) ?>">
        <div class="mb-2"><label class="form-label">Title *</label><input class="form-control" name="title" value="<?= e($editPage['title'] ?? '') ?>" required></div>
        <div class="mb-2"><label class="form-label">Content (HTML allowed)</label><textarea class="form-control" name="content" rows="10"><?= e($editPage['content'] ?? '') ?></textarea></div>
        <div class="mb-2"><label class="form-label">Featured Image</label><input type="file" class="form-control" name="featured_image" accept="image/*"></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title" value="<?= e($editPage['seo_title'] ?? '') ?>"></div>
          <div class="col-6"><label class="form-label">SEO Description</label><input class="form-control" name="seo_description" value="<?= e($editPage['seo_description'] ?? '') ?>"></div>
        </div>
        <div class="d-flex gap-3 mb-3">
          <div class="form-check"><input class="form-check-input" type="checkbox" name="show_in_footer" value="1" id="pif" <?= ($editPage['show_in_footer'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label small" for="pif">Show in footer</label></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="status" value="active" id="pst" <?= ($editPage['status'] ?? 'active') === 'active' ? 'checked' : '' ?>><label class="form-check-label small" for="pst">Active</label></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill"><?= isset($editPage) ? 'Save Changes' : 'Create Page' ?></button>
        <?php if (isset($editPage)): ?><a class="btn btn-outline-secondary w-100 rounded-pill mt-2" href="<?= url('/admin/pages') ?>">Cancel</a><?php endif; ?>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Pages (<?= count($pages) ?>)</h6>
    <?php foreach ($pages as $p): ?>
    <div class="admin-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div><b class="small"><?= e($p['title']) ?></b>
        <div class="text-muted" style="font-size:.66rem"><a href="<?= url('/page/' . $p['slug']) ?>" target="_blank">/page/<?= e($p['slug']) ?></a> <?= $p['show_in_footer'] ? '• footer' : '' ?></div></div>
      <div class="d-flex gap-1 align-items-center">
        <?= $p['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">' . e($p['status']) . '</span>' ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>"><i class="fa-solid fa-pen"></i></a>
        <form method="post" action="<?= url('/admin/pages/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Delete page?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
