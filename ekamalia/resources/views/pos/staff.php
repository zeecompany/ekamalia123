<div class="row g-4">
  <div class="col-lg-4">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Add Staff</h5>
      <p class="text-muted small">Grant POS access to an existing registered user. Roles preset the permissions.</p>
      <form id="staffAddForm">
        <div class="mb-2"><label class="form-label small fw-semibold">User *</label>
          <select class="form-select" name="user_id" required>
            <option value="">Select user…</option>
            <?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option><?php endforeach; ?>
          </select></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Role</label>
          <select class="form-select" name="role" id="staffRole">
            <option value="manager">Manager — almost everything</option>
            <option value="cashier">Cashier — sales & customers</option>
            <option value="inventory">Inventory Keeper — stock & purchases</option>
            <option value="sales">Sales — sales only</option>
          </select></div>
        <details class="mb-3">
          <summary class="small text-muted" style="cursor:pointer">Custom permissions (optional)</summary>
          <div class="row g-1 mt-2 small">
            <?php foreach (['sales', 'inventory', 'purchases', 'reports', 'customers', 'returns', 'expenses'] as $perm): ?>
            <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm ?>" id="perm-<?= $perm ?>"><label class="form-check-label" for="perm-<?= $perm ?>"><?= ucfirst($perm) ?></label></div></div>
            <?php endforeach; ?>
          </div>
        </details>
        <button class="btn btn-ek w-100 rounded-pill">Grant POS Access</button>
        <div class="text-muted mt-2" style="font-size:.68rem">Only the shop owner can manage staff.</div>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <h5 class="fw-bold mb-3">POS Team (<?= count($staff) ?>)</h5>
    <?php foreach ($staff as $s): ?>
    <div class="ek-card p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="kpi-ic ic-green" style="width:38px;height:38px"><i class="fa-solid fa-user-gear"></i></span>
        <div><b class="small"><?= e($s['name']) ?></b>
          <div class="text-muted" style="font-size:.68rem"><?= e($s['email']) ?> • role: <b><?= e($s['role']) ?></b> • perms: <?= e(implode(', ', json_decode((string)$s['permissions'], true) ?: [])) ?: '—' ?></div></div>
      </div>
      <div class="chip-actions d-flex flex-wrap align-items-center">
        <?= $s['status'] === 'active' ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Suspended</span>' ?>
        <?php if ($s['role'] !== 'owner' && (int)$s['user_id'] !== (int)user_id()): ?>
        <form class="d-inline" method="post" action="<?= url('/pos/staff/update') ?>"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
          <input type="hidden" name="action" value="<?= $s['status'] === 'active' ? 'suspend' : 'activate' ?>">
          <button class="btn btn-sm btn-outline-warning"><?= $s['status'] === 'active' ? 'Suspend' : 'Activate' ?></button></form>
        <form class="d-inline" method="post" action="<?= url('/pos/staff/update') ?>" onsubmit="return confirm('Remove POS access for <?= e($s['name']) ?>?')"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
          <input type="hidden" name="action" value="remove"><button class="btn btn-sm btn-outline-danger">Remove</button></form>
        <?php else: ?><span class="badge text-bg-dark">Owner</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$staff): ?><p class="text-muted small">No POS staff yet — add your cashier from the left.</p><?php endif; ?>
  </div>
</div>
<script>
document.getElementById('staffAddForm').addEventListener('submit', e => {
  e.preventDefault();
  const f = e.target;
  const data = { user_id: f.user_id.value, role: f.staffRole.value };
  const perms = [...f.querySelectorAll('input[name="permissions[]"]:checked')].map(c => c.value);
  if (perms.length) data['permissions[]'] = perms;
  ekPost('<?= url('/pos/staff/add') ?>', data).then(r => { toast(r.message, r.ok ? 'success' : 'danger'); if (r.ok) setTimeout(() => location.reload(), 600); });
});
</script>
