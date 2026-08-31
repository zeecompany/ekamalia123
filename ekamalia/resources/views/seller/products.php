<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Products</h1>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/seller/products/create') ?>"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
</div>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-5"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search name or SKU…"></div>
  <div class="col-md-4"><select class="form-select" name="status"><option value="">All statuses</option>
    <?php foreach (['published' => 'Published', 'pending' => 'Pending', 'draft' => 'Draft', 'hidden' => 'Hidden', 'out_of_stock' => 'Out of stock', 'archived' => 'Archived'] as $k => $v): ?>
      <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><button class="btn btn-ek w-100 rounded-pill">Filter</button></div>
</form>
<?php if ($products): ?>
<div class="table-responsive ek-card table-responsive-stack">
<table class="table align-middle">
  <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): $img = qv('SELECT image FROM product_images WHERE product_id=? ORDER BY sort_order LIMIT 1', [$p['id']]); ?>
  <tr>
    <td data-label="Product"><div class="d-flex gap-2 align-items-center">
      <img src="<?= e(img_or($img)) ?>" style="width:44px;height:44px;border-radius:8px;object-fit:cover" alt="">
      <div><span class="small fw-semibold d-block text-truncate" style="max-width:200px"><?= e($p['name']) ?></span>
      <span class="text-muted" style="font-size:.68rem"><?= e($p['cat_name'] ?? '') ?><?= $p['is_featured'] ? ' • ⭐ Featured' : '' ?></span></div></div></td>
    <td data-label="Price"><b class="small"><?= money($p['sale_price'] ?: $p['price']) ?></b><?= $p['sale_price'] ? '<div class="text-muted text-decoration-line-through" style="font-size:.68rem">' . money($p['price']) . '</div>' : '' ?></td>
    <td data-label="Stock"><span class="badge text-bg-<?= $p['stock'] <= $p['min_stock'] ? 'danger' : 'success' ?>"><?= (int)$p['stock'] ?></span></td>
    <td data-label="Status"><?= status_badge($p['status']) ?><?= $p['status_note'] ? '<div class="text-muted" style="font-size:.66rem">' . e($p['status_note']) . '</div>' : '' ?></td>
    <td data-label="Views" class="small"><?= number_format((int)$p['views']) ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <a class="btn btn-sm btn-ek-sm" href="<?= url('/seller/products/' . $p['id'] . '/edit') ?>"><i class="fa-solid fa-pen"></i></a>
      <form method="post" action="<?= url('/seller/products/' . $p['id'] . '/action') ?>" class="d-inline"><?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $p['status'] === 'published' ? 'hide' : 'publish' ?>">
        <button class="btn btn-sm btn-outline-secondary" title="<?= $p['status'] === 'published' ? 'Hide' : 'Publish' ?>"><i class="fa-solid <?= $p['status'] === 'published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i></button></form>
      <form method="post" action="<?= url('/seller/products/' . $p['id'] . '/action') ?>" class="d-inline" onsubmit="return confirm('Delete this product?')"><?= csrf_field() ?>
        <input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
      <?php if (!$p['is_featured']): ?>
      <form method="post" action="<?= url('/seller/products/' . $p['id'] . '/action') ?>" class="d-inline"><?= csrf_field() ?>
        <input type="hidden" name="action" value="feature"><button class="btn btn-sm btn-outline-warning" title="Request feature"><i class="fa-regular fa-star"></i></button></form>
      <?php endif; ?>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-box-open"></i><h5>No products yet</h5><a class="btn btn-ek rounded-pill px-4" href="<?= url('/seller/products/create') ?>">Add your first product</a></div><?php endif; ?>
