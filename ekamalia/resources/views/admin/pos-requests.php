<h1 class="h4 fw-bold mb-4">POS Access Management</h1>
<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link active" href="#pendingTab" data-bs-toggle="pill">Requests (<?= count(array_filter($requests, fn($r) => $r['status'] === 'pending')) ?> pending)</a></li>
  <li class="nav-item"><a class="nav-link" href="#activeTab" data-bs-toggle="pill">Active POS Users (<?= count($active) ?>)</a></li>
  <li class="nav-item"><a class="nav-link" href="#historyTab" data-bs-toggle="pill">All Requests</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane fade show active" id="pendingTab">
    <?php $pend = array_filter($requests, fn($r) => $r['status'] === 'pending'); ?>
    <?php foreach ($pend as $r): ?>
    <div class="admin-card p-3 mb-2">
      <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
        <div><b><?= e($r['business_name'] ?? $r['shop_name'] ?? 'Business') ?></b>
          <div class="small text-muted"><?= e($r['user_name']) ?> (<?= e($r['email']) ?>) • <?= e($r['shop_name'] ?? 'no shop') ?> • <?= e(time_ago($r['created_at'])) ?></div>
          <?php if ($r['note']): ?><div class="small mt-1"><?= e($r['note']) ?></div><?php endif; ?></div>
        <div class="chip-actions d-flex flex-wrap align-items-center gap-1">
          <select class="form-select form-select-sm" id="pkg-<?= $r['id'] ?>" style="width:150px">
            <?php foreach (qa('SELECT * FROM pos_packages') as $pk): ?><option value="<?= $pk['id'] ?>"><?= e($pk['name']) ?></option><?php endforeach; ?>
          </select>
          <input type="number" class="form-control form-control-sm" id="days-<?= $r['id'] ?>" value="365" style="width:80px" title="Days">
          <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/pos-requests/' . $r['id'] . '/update') ?>', {action:'approve', package_id: document.getElementById('pkg-<?= $r['id'] ?>').value, days: document.getElementById('days-<?= $r['id'] ?>').value})">Approve</button>
          <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/pos-requests/' . $r['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$pend): ?><div class="ek-empty"><i class="fa-solid fa-inbox"></i><h5>No pending requests</h5></div><?php endif; ?>
  </div>
  <div class="tab-pane fade" id="activeTab">
    <div class="table-responsive admin-card table-responsive-stack">
    <table class="table align-middle mb-0">
      <thead><tr><th>User</th><th>Shop</th><th>Role</th><th>Status</th><th>Expires</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($active as $a): ?>
      <tr>
        <td data-label="User" class="small"><b><?= e($a['name']) ?></b><div class="text-muted" style="font-size:.66rem"><?= e($a['email']) ?></div></td>
        <td data-label="Shop" class="small"><?= e($a['shop_name'] ?? '—') ?></td>
        <td data-label="Role"><span class="badge text-bg-success text-uppercase"><?= e($a['role']) ?></span></td>
        <td data-label="Status"><?= status_badge($a['status']) ?></td>
        <td data-label="Expires" class="small"><?= $a['expires_at'] ? e(fmt_date($a['expires_at'], 'd M Y')) : 'Never' ?></td>
        <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
          <?php if ($a['role'] !== 'owner'): ?>
            <button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/pos-requests/0/update') ?>', {action:'suspend'})" disabled title="Use shop-level suspend">Suspend</button>
          <?php endif; ?>
          <button class="btn btn-sm btn-outline-primary" onclick="const d=prompt('Extend by how many days?', '30'); if(d) { const f=document.createElement('form'); f.method='POST'; f.action='<?= url('/admin/pos-requests/' . ($a['pos_request_id'] ?? 0) . '/update') ?>'; f.innerHTML = '<?= csrf_field() ?>'+'<input name=action value=extend><input name=days value='+d+'>'; document.body.appendChild(f); f.submit(); }">Extend</button>
        </div></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <div class="tab-pane fade" id="historyTab">
    <div class="table-responsive admin-card table-responsive-stack">
    <table class="table align-middle mb-0">
      <thead><tr><th>Business</th><th>User</th><th>Package</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($requests as $r): if ($r['status'] === 'pending') continue; ?>
      <tr>
        <td data-label="Business" class="small"><b><?= e($r['business_name'] ?? $r['shop_name'] ?? '—') ?></b></td>
        <td data-label="User" class="small"><?= e($r['user_name']) ?></td>
        <td data-label="Package" class="small"><?= e($r['package_name'] ?? 'Default') ?></td>
        <td data-label="Status"><?= status_badge($r['status']) ?></td>
        <td data-label="Requested" class="small"><?= e(fmt_date($r['created_at'], 'd M Y')) ?></td>
        <td data-label="Actions">
          <?php if (in_array($r['status'], ['approved'], true)): ?>
            <button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/pos-requests/' . $r['id'] . '/update') ?>', {action:'suspend'}, 'Suspend POS for this user?')">Suspend</button>
          <?php elseif ($r['status'] === 'rejected'): ?>
            <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/pos-requests/' . $r['id'] . '/update') ?>', {action:'approve', days:365})">Approve</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
