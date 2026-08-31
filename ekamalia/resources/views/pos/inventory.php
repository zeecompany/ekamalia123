<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-boxes-packing me-2 text-success"></i>Inventory &amp; Movements</h1>
    <?php if ($lowCount): ?><span class="badge text-bg-danger"><?= $lowCount ?> low-stock items</span><?php endif; ?>
  </div>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/pos/inventory') ?>"><i class="fa-solid fa-sliders me-1"></i>Quick Adjust</a>
</div>
<div class="row g-4">
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Products (<?= count($products) ?>)</h6>
    <div class="table-responsive ek-card table-responsive-stack" style="max-height:520px;overflow-y:auto">
    <table class="table align-middle">
      <thead class="sticky-top bg-white"><tr><th>Product</th><th>Stock</th><th>Status</th><th>Adjust</th></tr></thead>
      <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td data-label="Product"><b class="small"><?= e($p['name']) ?></b><div class="text-muted" style="font-size:.66rem"><?= e($p['sku'] ?: 'no SKU') ?> • <?= e($p['cat_name'] ?? '') ?></div></td>
        <td data-label="Stock"><b><?= (int)$p['stock'] ?></b> <span class="text-muted small">/ min <?= (int)$p['min_stock'] ?></span></td>
        <td data-label="Status"><?php if ((int)$p['stock'] <= 0): ?><span class="badge text-bg-danger">Out</span><?php elseif ((int)$p['stock'] <= (int)$p['min_stock']): ?><span class="badge text-bg-warning">Low</span><?php else: ?><span class="badge text-bg-success">OK</span><?php endif; ?></td>
        <td data-label="Adjust">
          <form class="d-flex gap-1 align-items-center flex-wrap" method="post" action="<?= url('/pos/inventory/adjust') ?>">
            <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <select class="form-select form-select-sm" name="type" style="width:112px">
              <option value="stock_in">Stock in</option><option value="stock_out">Stock out</option>
              <option value="damage">Damage</option><option value="adjustment">Adjustment</option>
            </select>
            <input type="number" class="form-control form-control-sm" name="quantity" min="1" style="width:68px" placeholder="Qty" required>
            <input class="form-control form-control-sm" name="note" placeholder="Note" style="width:110px">
            <button class="btn btn-ek-sm">Go</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <div class="col-lg-5">
    <h6 class="fw-bold mb-3">Recent Stock Movements</h6>
    <?php foreach ($movements as $m): ?>
    <div class="ek-card p-2 px-3 mb-2 d-flex justify-content-between align-items-center gap-2">
      <div class="small">
        <span class="badge text-bg-<?= in_array($m['type'], ['stock_in', 'return_in'], true) ? 'success' : (in_array($m['type'], ['damage', 'stock_out'], true) ? 'danger' : 'secondary') ?>"><?= e(str_replace('_', ' ', $m['type'])) ?></span>
        <?= e($m['product_name']) ?>
        <div class="text-muted" style="font-size:.66rem"><?= (int)$m['quantity'] > 0 ? '+' : '' ?><?= (int)$m['quantity'] ?> • <?= e($m['by_user'] ?? 'system') ?> • <?= e(time_ago($m['created_at'])) ?><?= $m['note'] ? ' • ' . e(mb_substr((string)$m['note'], 0, 30)) : '' ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$movements): ?><p class="text-muted small">No movements recorded yet.</p><?php endif; ?>
  </div>
</div>
