<div class="row g-4">
  <div class="col-lg-4">
    <div class="admin-card"><div class="ac-head"><h6>Add / Edit Category</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/categories') ?>" enctype="multipart/form-data" id="catForm">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="catId" value="">
        <div class="mb-2"><label class="form-label">Name *</label><input class="form-control" name="name" id="catName" required></div>
        <div class="mb-2"><label class="form-label">Slug (auto if empty)</label><input class="form-control" name="slug" id="catSlug"></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Type</label>
            <select class="form-select" name="type" id="catType">
              <option value="both">Marketplace + Directory</option><option value="product">Products only</option>
              <option value="ad">Classifieds only</option><option value="business">Directory only</option>
            </select></div>
          <div class="col-6"><label class="form-label">Parent</label>
            <select class="form-select" name="parent_id" id="catParent"><option value="">— Top level —</option>
            <?php foreach ($parents as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['type']) ?>)</option><?php endforeach; ?></select></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">Font Awesome Icon</label><input class="form-control" name="icon" id="catIcon" placeholder="fa-mobile-screen"></div>
          <div class="col-6"><label class="form-label">Image</label><input type="file" class="form-control" name="image" accept="image/*"></div>
        </div>
        <div class="mb-2"><label class="form-label">Description</label><input class="form-control" name="description" id="catDesc"></div>
        <div class="row g-2 mb-3">
          <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" id="catFeat" value="1"><label class="form-check-label small" for="catFeat">Featured</label></div></div>
          <div class="col-4"><label class="form-label">Sort</label><input type="number" class="form-control" name="sort_order" id="catSort" value="0"></div>
          <div class="col-4"><label class="form-label">Status</label><select class="form-select" name="status" id="catStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill">Save Category</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-8">
    <div class="d-flex gap-2 flex-wrap mb-3">
      <a class="chip <?= $type === '' ? 'active' : '' ?>" href="<?= url('/admin/categories') ?>">All (<?= count($cats) ?>)</a>
      <?php foreach (['both' => 'Both', 'product' => 'Products', 'ad' => 'Classifieds', 'business' => 'Directory'] as $k => $v): ?>
      <a class="chip <?= $type === $k ? 'active' : '' ?>" href="?type=<?= $k ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>
    <div class="table-responsive admin-card table-responsive-stack">
    <table class="table align-middle mb-0">
      <thead><tr><th>Category</th><th>Type</th><th>Parent</th><th>Counts</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($cats as $c): ?>
      <tr>
        <td data-label="Category"><b class="small"><?= e($c['name']) ?></b>
          <div class="text-muted" style="font-size:.66rem"><i class="fa-solid <?= e($c['icon'] ?: 'fa-tag') ?>"></i> /<?= e($c['slug']) ?> <?= $c['is_featured'] ? '• ⭐ featured' : '' ?> <?= $c['status'] !== 'active' ? '• ' . e($c['status']) : '' ?></div></td>
        <td data-label="Type"><span class="badge text-bg-light border"><?= e($c['type']) ?></span></td>
        <td data-label="Parent" class="small"><?= e($c['parent_name'] ?? '—') ?></td>
        <td data-label="Counts" class="small"><?= (int)$c['products_count'] ?>P / <?= (int)$c['ads_count'] ?>A</td>
        <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
          <button class="btn btn-sm btn-outline-secondary" onclick='editCat(<?= json_encode(["id" => (int)$c["id"], "n" => $c["name"], "s" => $c["slug"], "t" => $c["type"], "p" => $c["parent_id"], "i" => $c["icon"], "d" => $c["description"], "f" => (int)$c["is_featured"], "so" => (int)$c["sort_order"], "st" => $c["status"]]) ?>)'>Edit</button>
          <form method="post" action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Delete category <?= e($c['name']) ?>?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
        </div></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <div class="admin-card mt-4"><div class="ac-head"><h6>Brands</h6></div><div class="ac-body">
      <form class="row g-2 align-items-end mb-3" method="post" action="<?= url('/admin/brands') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="col-md-4"><label class="form-label">Brand Name</label><input class="form-control" name="name" required></div>
        <div class="col-md-4"><label class="form-label">Logo</label><input type="file" class="form-control" name="logo" accept="image/*"></div>
        <div class="col-md-2"><button class="btn btn-ek w-100 rounded-pill">Add Brand</button></div>
      </form>
      <div class="d-flex gap-2 flex-wrap">
        <?php foreach (qa('SELECT * FROM brands ORDER BY name') as $br): ?>
        <span class="chip"><i class="fa-solid fa-copyright text-muted me-1"></i><?= e($br['name']) ?>
          <form class="d-inline ms-1" method="post" action="<?= url('/admin/brands/' . $br['id'] . '/delete') ?>" onsubmit="return confirm('Delete brand?')"><?= csrf_field() ?><button class="btn btn-link btn-sm text-danger p-0">×</button></form></span>
        <?php endforeach; ?>
        <?php if (!qa('SELECT 1 FROM brands LIMIT 1')): ?><span class="text-muted small">No brands yet.</span><?php endif; ?>
      </div>
    </div></div>
  </div>
</div>
<script>
function editCat(d) {
  document.getElementById('catId').value = d.id;
  document.getElementById('catName').value = d.n;
  document.getElementById('catSlug').value = d.s || '';
  document.getElementById('catType').value = d.t;
  document.getElementById('catParent').value = d.p || '';
  document.getElementById('catIcon').value = d.i || '';
  document.getElementById('catDesc').value = d.d || '';
  document.getElementById('catFeat').checked = !!d.f;
  document.getElementById('catSort').value = d.so || 0;
  document.getElementById('catStatus').value = d.st || 'active';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
