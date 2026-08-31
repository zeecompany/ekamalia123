<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card"><div class="ac-head"><h6>Add / Edit Bank Account</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/banks') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="bankId" value="">
        <div class="mb-2"><label class="form-label">Bank Name *</label><input class="form-control" name="bank_name" id="bankName" required></div>
        <div class="mb-2"><label class="form-label">Account Title *</label><input class="form-control" name="account_title" id="bankTitle" required></div>
        <div class="mb-2"><label class="form-label">Account Number *</label><input class="form-control" name="account_number" id="bankNo" required></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">IBAN</label><input class="form-control" name="iban" id="bankIban"></div>
          <div class="col-6"><label class="form-label">Branch</label><input class="form-control" name="branch" id="bankBranch"></div>
        </div>
        <div class="mb-2"><label class="form-label">Instructions (shown at checkout)</label><textarea class="form-control" name="instructions" id="bankInstr" rows="2"></textarea></div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Status</label><select class="form-select" name="status" id="bankStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
          <div class="col-6"><label class="form-label">Sort</label><input type="number" class="form-control" name="sort_order" id="bankSort" value="0"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill mt-3">Save Account</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Accounts (<?= count($banks) ?>)</h6>
    <?php foreach ($banks as $b): ?>
    <div class="admin-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-building-columns fs-4 text-success"></i>
        <div><b><?= e($b['bank_name']) ?></b> <?= $b['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?>
          <div class="small text-muted"><?= e($b['account_title']) ?> • <code><?= e($b['account_number']) ?></code><?= $b['iban'] ? ' • IBAN ' . e($b['iban']) : '' ?></div></div>
      </div>
      <div class="chip-actions d-flex flex-wrap">
        <button class="btn btn-sm btn-outline-secondary" onclick='editBank(<?= json_encode(["id" => (int)$b["id"], "n" => $b["bank_name"], "t" => $b["account_title"], "a" => $b["account_number"], "i" => $b["iban"], "br" => $b["branch"], "ins" => $b["instructions"], "s" => $b["status"], "so" => (int)$b["sort_order"]]) ?>)'>Edit</button>
        <form method="post" action="<?= url('/admin/banks/' . $b['id'] . '/delete') ?>" onsubmit="return confirm('Delete this bank account?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$banks): ?><p class="text-muted small">No accounts yet. Buyers can't pay by bank transfer until you add one.</p><?php endif; ?>
  </div>
</div>
<script>
function editBank(d) {
  document.getElementById('bankId').value = d.id;
  document.getElementById('bankName').value = d.n || '';
  document.getElementById('bankTitle').value = d.t || '';
  document.getElementById('bankNo').value = d.a || '';
  document.getElementById('bankIban').value = d.i || '';
  document.getElementById('bankBranch').value = d.br || '';
  document.getElementById('bankInstr').value = d.ins || '';
  document.getElementById('bankStatus').value = d.s || 'active';
  document.getElementById('bankSort').value = d.so || 0;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
