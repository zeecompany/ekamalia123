<h1 class="h4 fw-bold mb-4">Shops Management</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended', 'rejected' => 'Rejected'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
  <form class="d-flex gap-2 ms-auto" method="get"><input type="hidden" name="status" value="<?= e($status) ?>"><input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Shop name…"><button class="btn btn-ek-sm">Search</button></form>
</div>
<?php if ($shops): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Shop</th><th>Owner</th><th>City</th><th>Package</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($shops as $s): ?>
  <tr>
    <td data-label="Shop"><div class="d-flex align-items-center gap-2">
      <img src="<?= e($s['logo'] ? upload_url($s['logo']) : asset('img/shop-logo.svg')) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover" alt="">
      <div><b class="small"><?= e($s['name']) ?></b>
        <div class="text-muted" style="font-size:.68rem"><?= $s['is_verified'] ? '✅ ' : '' ?><?= $s['is_featured'] ? '⭐ ' : '' ?><a href="<?= url('/shop/' . $s['slug']) ?>" target="_blank">/shop/<?= e($s['slug']) ?></a></div></div></div></td>
    <td data-label="Owner" class="small"><a class="text-decoration-none" href="<?= url('/admin/users/' . $s['user_id']) ?>"><?= e($s['owner_name']) ?></a><div class="text-muted" style="font-size:.68rem"><?= e($s['owner_email']) ?></div></td>
    <td data-label="City" class="small"><?= e($s['city_name'] ?? '—') ?></td>
    <td data-label="Package" class="small"><?= e($s['package_name'] ?? 'Free') ?><?= !empty($s['package_expires_at']) ? '<div class="text-muted" style="font-size:.66rem">till ' . e(fmt_date($s['package_expires_at'], 'd M Y')) . '</div>' : '' ?></td>
    <td data-label="Status"><?= status_badge($s['status']) ?><?= $s['status_note'] ? '<div class="text-muted" style="font-size:.66rem">' . e($s['status_note']) . '</div>' : '' ?></td>
    <td data-label="Joined" class="small text-nowrap"><?= e(fmt_date($s['created_at'], 'd M Y')) ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <?php if ($s['status'] === 'pending'): ?>
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'approve'})">Approve</button>
        <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      <?php elseif ($s['status'] === 'approved'): ?>
        <button class="btn btn-sm btn-outline-warning" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'suspend', note:n})">Suspend</button>
      <?php else: ?>
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'approve'})">Approve</button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-primary" onclick="adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'<?= $s['is_verified'] ? 'unverify' : 'verify' ?>'})"><?= $s['is_verified'] ? 'Unverify' : 'Verify' ?></button>
      <button class="btn btn-sm btn-outline-dark" onclick="adminAction('<?= url('/admin/shops/' . $s['id'] . '/update') ?>', {action:'<?= $s['is_featured'] ? 'unfeature' : 'feature' ?>'})"><?= $s['is_featured'] ? 'Unfeature' : 'Feature' ?></button>
      <button class="btn btn-sm btn-outline-secondary" onclick="assignPkg(<?= (int)$s['id'] ?>)">Package</button>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-store"></i><h5>No shops found</h5></div><?php endif; ?>
<div class="modal fade" id="pkgModal" tabindex="-1"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content rounded-4">
  <div class="modal-body p-4">
    <h6 class="fw-bold mb-3">Assign Package</h6>
    <label class="form-label small">Package</label>
    <select class="form-select mb-2" id="pkgSelect"><?php foreach (qa('SELECT id,name,price FROM packages') as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= money($p['price']) ?>)</option><?php endforeach; ?></select>
    <label class="form-label small">Days</label>
    <input type="number" class="form-control mb-3" id="pkgDays" value="30">
    <button class="btn btn-ek w-100 rounded-pill" id="pkgSave">Assign</button>
  </div>
</div></div></div>
<script>
function assignPkg(shopId) {
  new bootstrap.Modal('#pkgModal').show();
  document.getElementById('pkgSave').onclick = () => {
    adminAction('<?= url('/admin/shops') ?>/' + shopId + '/update', { action: 'package', package_id: document.getElementById('pkgSelect').value, days: document.getElementById('pkgDays').value });
  };
}
</script>
