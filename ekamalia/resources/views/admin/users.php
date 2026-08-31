<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Users <span class="text-muted fs-6">(<?= number_format($total) ?>)</span></h1>
  <form class="d-flex gap-2" method="get">
    <input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Name, email or phone…">
    <select class="form-select form-select-sm" name="status" style="width:130px">
      <option value="">All</option>
      <?php foreach (['active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended', 'banned' => 'Banned'] as $k => $v): ?>
      <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-ek-sm">Filter</button>
  </form>
</div>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>User</th><th>Contact</th><th>City</th><th>Activity</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
  <tr>
    <td data-label="User"><a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="<?= url('/admin/users/' . $u['id']) ?>">
      <img src="<?= e($u['avatar'] ? upload_url($u['avatar']) : asset('img/avatar-default.svg')) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover" alt="">
      <div><b class="small"><?= e($u['name']) ?></b><?= $u['role'] === 'admin' ? ' <span class="badge text-bg-dark">ADMIN</span>' : '' ?><div class="text-muted" style="font-size:.68rem">#<?= $u['id'] ?></div></div></a></td>
    <td data-label="Contact" class="small"><?= e($u['email']) ?><div class="text-muted" style="font-size:.68rem"><?= e(pk_phone($u['phone'] ?? '')) ?></div></td>
    <td data-label="City" class="small"><?= e($u['city_name'] ?? '—') ?></td>
    <td data-label="Activity" class="small"><?= (int)$u['orders_count'] ?> orders<div class="text-muted" style="font-size:.68rem"><?= (int)$u['shops_count'] ?> shops</div></td>
    <td data-label="Status"><?php
      $cls = ['active' => 'success', 'pending' => 'warning', 'suspended' => 'secondary', 'banned' => 'danger'][$u['status']] ?? 'light';
      $ver = !empty($u['is_verified']);
      ?><span class="badge text-bg-<?= $cls ?>"><?= e($u['status']) ?></span><?= $ver ? ' <i class="fa-solid fa-circle-check text-primary" title="Verified"></i>' : '' ?></td>
    <td data-label="Joined" class="small text-nowrap"><?= e(fmt_date($u['created_at'], 'd M Y')) ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <?php if ($u['status'] !== 'banned'): ?>
        <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'ban'}, 'Ban <?= e($u['name']) ?>?')">Ban</button>
      <?php else: ?>
        <button class="btn btn-sm btn-outline-success" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'unban'})">Unban</button>
      <?php endif; ?>
      <?= $u['is_verified'] ? '' : '<button class="btn btn-sm btn-outline-primary" onclick="adminAction(\'' . url('/admin/users/' . $u['id'] . '/update') . '\', {action:\'verify\'})">Verify</button>' ?>
      <?php if ($u['role'] === 'admin' && (int)$u['id'] !== user_id()): ?>
        <button class="btn btn-sm btn-outline-secondary" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'remove_admin'}, 'Remove admin rights?')">Demote</button>
      <?php elseif ($u['role'] !== 'admin'): ?>
        <button class="btn btn-sm btn-outline-dark" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'make_admin'}, 'Grant ADMIN rights to <?= e($u['name']) ?>?')">Make Admin</button>
      <?php endif; ?>
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
