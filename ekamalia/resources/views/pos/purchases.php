<div class="row g-4">
  <div class="col-lg-5">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Record Purchase</h5>
      <form method="post" action="<?= url('/pos/purchases/save') ?>" id="purForm">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small fw-semibold">Supplier *</label>
          <select class="form-select" name="supplier_id" required><option value="">Select supplier</option>
          <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
        <label class="form-label small fw-semibold">Items</label>
        <div id="purItems"></div>
        <button type="button" class="btn btn-sm btn-outline-success rounded-pill mb-3" id="addPurItem">+ Add item</button>
        <div class="row g-2">
          <div class="col-6"><label class="form-label small fw-semibold">Paid Now (Rs)</label><input type="number" class="form-control" name="paid_amount" value="0" min="0"></div>
          <div class="col-6"><label class="form-label small fw-semibold">Note</label><input class="form-control" name="note"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill mt-3">Save Purchase</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <h5 class="fw-bold mb-3">Purchase History</h5>
    <?php foreach ($purchases as $p): ?>
    <div class="ek-card p-3 mb-2 d-flex flex-wrap justify-content-between gap-2">
      <div><b>#<?= (int)$p['id'] ?></b> — <?= e($p['supplier_name'] ?? '') ?>
        <div class="text-muted small"><?= e(fmt_date($p['created_at'])) ?><?= $p['note'] ? ' • ' . e($p['note']) : '' ?></div></div>
      <div class="text-end"><b class="text-success"><?= money($p['total']) ?></b>
        <div class="text-muted small">paid <?= money($p['paid_amount']) ?><?= (float)$p['balance'] > 0 ? ' • <span class="text-danger">due ' . money($p['balance']) . '</span>' : '' ?></div></div>
    </div>
    <?php endforeach; ?>
    <?php if (!$purchases): ?><p class="text-muted small">No purchases recorded.</p><?php endif; ?>
  </div>
</div>
<script>
document.getElementById('addPurItem').addEventListener('click', () => {
  const wrap = document.getElementById('purItems');
  const row = document.createElement('div');
  row.className = 'pur-row d-flex gap-1 mb-1';
  row.innerHTML = `<input class="form-control form-control-sm" name="item_product[]" placeholder="Product ID" style="width:90px">
    <input type="number" class="form-control form-control-sm" name="item_qty[]" placeholder="Qty" min="1" style="width:80px">
    <input type="number" step="0.01" class="form-control form-control-sm" name="item_cost[]" placeholder="Cost" min="0">
    <button type="button" class="btn btn-sm text-danger" onclick="this.parentNode.remove()">×</button>`;
  wrap.appendChild(row);
});
document.getElementById('purForm').addEventListener('submit', e => {
  if (!document.querySelector('.pur-row')) { e.preventDefault(); toast('Add at least one item', 'warning'); }
});
</script>
