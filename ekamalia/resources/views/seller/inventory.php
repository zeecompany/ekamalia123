<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h4 fw-bold mb-0">Inventory</h1><p class="text-muted small mb-0">Live stock levels and movement history</p></div>
  <span class="badge text-bg-warning"><?= count(array_filter($rows, fn($r) => (int)$r['stock'] <= (int)$r['min_stock'])) ?> low/out of stock</span>
</div>
<div class="ek-card p-0 overflow-hidden mb-4">
  <div class="table-responsive"><table class="table mb-0 responsive-table">
    <thead><tr><th>Product</th><th>Stock</th><th>Min</th><th>Status</th><th>Update</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $p): $low = (int)$p['stock'] <= (int)$p['min_stock']; ?>
      <tr>
        <td data-label="Product"><b><?= e($p['name']) ?></b></td>
        <td data-label="Stock"><b class="<?= $low ? 'text-danger' : '' ?>"><?= (int)$p['stock'] ?> <?= e($p['unit'] ?: '') ?></b></td>
        <td data-label="Min"><?= (int)$p['min_stock'] ?></td>
        <td data-label="Status"><?php if ((int)$p['stock'] <= 0): ?><span class="badge text-bg-danger">Out</span><?php elseif ($low): ?><span class="badge text-bg-warning">Low</span><?php else: ?><span class="badge text-bg-success">OK</span><?php endif; ?></td>
        <td data-label="Update">
          <form class="d-flex gap-1 flex-wrap inv-form" data-id="<?= (int)$p['id'] ?>">
            <select class="form-select form-select-sm" name="direction" style="width:92px"><option value="in">+ Add</option><option value="out">− Remove</option></select>
            <input type="number" class="form-control form-control-sm" name="quantity" min="1" style="width:70px" placeholder="Qty" required>
            <button class="btn btn-ek-sm">Apply</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5"><div class="ek-empty py-4"><i class="fa-solid fa-boxes-stacked"></i><p class="mb-0">No products yet.</p></div></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<div class="ek-card p-4">
  <h6 class="fw-bold mb-3">Recent Stock Movements</h6>
  <?php foreach ($moves as $m): ?>
  <div class="d-flex flex-wrap justify-content-between gap-2 py-2 border-bottom small">
    <span><b class="<?= (int)$m['quantity'] < 0 ? 'text-danger' : 'text-success' ?>"><?= (int)$m['quantity'] > 0 ? '+' : '' ?><?= (int)$m['quantity'] ?></b> <?= e($m['product_name']) ?></span>
    <span class="text-muted"><?= e($m['reason']) ?> • <?= e(fmt_date($m['created_at'])) ?><?= $m['by_user'] ? ' • ' . e($m['by_user']) : '' ?></span>
  </div>
  <?php endforeach; ?>
  <?php if (!$moves): ?><p class="text-muted small mb-0">No movements recorded yet.</p><?php endif; ?>
</div>
<script>
document.querySelectorAll('.inv-form').forEach(f => f.addEventListener('submit', e => {
  e.preventDefault();
  const d = { product_id: f.dataset.id, direction: f.direction.value, quantity: f.quantity.value };
  ekPost('<?= url('/seller/inventory/update') ?>', d).then(r => { toast(r.message, r.ok ? 'success' : 'danger'); if (r.ok) setTimeout(() => location.reload(), 600); });
}));
</script>
