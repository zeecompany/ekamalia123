<h1 class="h4 fw-bold mb-4">Products Moderation <span class="text-muted fs-6">(<?= number_format($total) ?>)</span></h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'published' => 'Published', 'hidden' => 'Hidden', 'out_of_stock' => 'Out of stock', 'draft' => 'Draft', 'archived' => 'Archived'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
  <form class="d-flex gap-2 ms-auto" method="get"><input type="hidden" name="status" value="<?= e($status) ?>"><input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Name or SKU…"><button class="btn btn-ek-sm">Search</button></form>
</div>
<?php if ($products): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Product</th><th>Shop</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): $img = qv('SELECT image FROM product_images WHERE product_id=? ORDER BY sort_order LIMIT 1', [$p['id']]); ?>
  <tr>
    <td data-label="Product"><div class="d-flex align-items-center gap-2">
      <img src="<?= e(img_or($img)) ?>" style="width:40px;height:40px;border-radius:8px;object-fit:cover" alt="">
      <div><b class="small"><?= e($p['name']) ?></b> <?= $p['is_featured'] ? '⭐' : '' ?>
        <div class="text-muted" style="font-size:.66rem"><?= e($p['cat_name'] ?? '') ?> • <?= (int)$p['views'] ?> views</div></div></div></td>
    <td data-label="Shop" class="small"><?= e($p['shop_name'] ?? '—') ?></td>
    <td data-label="Price"><b class="small"><?= money($p['sale_price'] ?: $p['price']) ?></b></td>
    <td data-label="Stock"><b class="small <?= (int)$p['stock'] <= 0 ? 'text-danger' : '' ?>"><?= (int)$p['stock'] ?></b></td>
    <td data-label="Status"><?= status_badge($p['status']) ?><?= $p['status_note'] ? '<div class="text-muted" style="font-size:.66rem">' . e($p['status_note']) . '</div>' : '' ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <?php if ($p['status'] === 'pending'): ?>
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'approve'})">Approve</button>
        <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      <?php elseif ($p['status'] === 'published'): ?>
        <button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'<?= $p['is_featured'] ? 'unfeature' : 'feature' ?>'})"><?= $p['is_featured'] ? 'Unfeature' : 'Feature' ?></button>
        <button class="btn btn-sm btn-outline-secondary" onclick="adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'hide'}, 'Hide this product?')">Hide</button>
      <?php elseif (in_array($p['status'], ['hidden', 'out_of_stock'], true)): ?>
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'restore'})">Restore</button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/products/' . $p['id'] . '/update') ?>', {action:'delete'}, 'Delete this product permanently?')"><i class="fa-regular fa-trash-can"></i></button>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if ($total > $perPage): ?>
<nav class="mt-3"><ul class="pagination pagination-sm">
  <?php $pages = (int)ceil($total / $perPage); for ($i = 1; $i <= min($pages, 30); $i++): ?>
  <li class="page-item <?= $i === (int)max(1, int_input('page', 1)) ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-box"></i><h5>No products found</h5></div><?php endif; ?>
