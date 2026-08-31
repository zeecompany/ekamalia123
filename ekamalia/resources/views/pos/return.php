<div class="row g-4">
  <div class="col-lg-6">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass me-1 text-success"></i>Find Original Sale</h5>
      <form id="retFindForm" class="d-flex gap-2">
        <input class="form-control" id="retInvoice" value="<?= e($_GET['invoice'] ?? '') ?>" placeholder="INV-260829-0A16" required>
        <button class="btn btn-ek px-4">Find</button>
      </form>
      <div id="retResult" class="mt-3"></div>
    </div>
  </div>
  <div class="col-lg-6">
    <h5 class="fw-bold mb-3">Recent Returns</h5>
    <?php foreach ($returns as $r): ?>
    <div class="ek-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div><b class="text-danger"><?= e($r['return_no'] ?? ('RET-' . $r['id'])) ?></b>
        <div class="text-muted small"><?= e(fmt_date($r['created_at'])) ?> • <?= strtoupper($r['refund_method'] ?? 'cash') ?> • by <?= e($r['by_user'] ?? '') ?></div>
        <?php if ($r['reason']): ?><div class="small text-muted"><?= e($r['reason']) ?></div><?php endif; ?></div>
      <b class="text-danger">-<?= money($r['total']) ?></b>
    </div>
    <?php endforeach; ?>
    <?php if (!$returns): ?><p class="text-muted small">No returns yet.</p><?php endif; ?>
  </div>
</div>
<script>
const findForm = document.getElementById('retFindForm');
findForm.addEventListener('submit', e => {
  e.preventDefault();
  const no = document.getElementById('retInvoice').value.trim();
  const box = document.getElementById('retResult');
  ekPost('<?= url('/pos/returns/find') ?>', { invoice_no: no }).then(r => {
    if (!r.ok) { box.innerHTML = '<div class="alert alert-warning small mb-0">' + r.message + '</div>'; return; }
    const s = r.sale;
    let html = `<div class="p-3 bg-light rounded-3"><b>${s.invoice_no}</b> — Rs ${Number(s.total).toLocaleString()} <span class="text-muted small">(${s.date}, ${s.customer || 'Walk-in'})</span><div class="mt-2">`;
    for (const it of r.items) {
      const left = it.qty - it.returned;
      if (left <= 0) { html += `<label class="d-flex justify-content-between py-1 border-bottom small text-muted">${it.name} — fully returned</label>`; continue; }
      html += `<label class="d-flex align-items-center justify-content-between gap-2 py-1 border-bottom small">
        <span class="flex-grow-1">${it.name} <span class="text-muted">(sold ${it.qty}${it.returned ? ', returned ' + it.returned : ''})</span></span>
        <span class="d-flex align-items-center gap-1">
          <input type="checkbox" class="form-check-input m-0 ret-check" data-max="${left}" data-id="${it.id}">
          <input type="number" class="form-control form-control-sm ret-qty" style="width:64px" min="1" max="${left}" value="${left}" disabled>
        </span></label>`;
    }
    html += `<div class="mt-2"><label class="form-label small fw-semibold">Reason</label>
      <input class="form-control form-control-sm mb-2" id="retReason" placeholder="Why is the customer returning?">
      <div class="d-flex gap-2 mb-2">
        <select class="form-select form-select-sm" id="retMethod" style="max-width:160px"><option value="cash">Refund: Cash</option><option value="bank">Refund: Bank</option></select>
      </div>
      <button class="btn btn-danger w-100 rounded-pill" id="retBtn"><i class="fa-solid fa-rotate-left me-1"></i>Process Return</button></div></div>`;
    box.innerHTML = html;
    box.querySelectorAll('.ret-check').forEach(cb => cb.addEventListener('change', () => {
      const qty = cb.closest('label').querySelector('.ret-qty');
      qty.disabled = !cb.checked;
      if (cb.checked) qty.value = cb.dataset.max;
    }));
    document.getElementById('retBtn').addEventListener('click', () => {
      const items = [];
      box.querySelectorAll('.ret-check:checked').forEach(cb => items.push({ id: cb.dataset.id, qty: +cb.closest('label').querySelector('.ret-qty').value || 1 }));
      if (!items.length) { toast('Select at least one item', 'warning'); return; }
      ekPost('<?= url('/pos/returns/process') ?>', { sale_id: s.id, items: JSON.stringify(items), reason: document.getElementById('retReason').value, refund_method: document.getElementById('retMethod').value })
        .then(r2 => { toast(r2.message, r2.ok ? 'success' : 'danger'); if (r2.ok) setTimeout(() => location.reload(), 800); });
    });
  });
});
<?php if (!empty($_GET['invoice'])): ?>findForm.requestSubmit();<?php endif; ?>
</script>
