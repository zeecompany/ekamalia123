<h1 class="h4 fw-bold mb-4">Business Directory Moderation</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php if ($businesses): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Business</th><th>Category</th><th>City</th><th>Owner</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($businesses as $b): ?>
  <tr>
    <td data-label="Business"><div class="d-flex align-items-center gap-2">
      <img src="<?= e($b['logo'] ? upload_url($b['logo']) : asset('img/biz-logo.svg')) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover" alt="">
      <div><b class="small"><?= e($b['name']) ?></b> <?= $b['is_verified'] ? '✅' : '' ?> <?= $b['is_featured'] ? '⭐' : '' ?>
        <div class="text-muted" style="font-size:.66rem"><a href="<?= url('/business/' . $b['slug']) ?>" target="_blank">/business/<?= e($b['slug']) ?></a></div></div></div></td>
    <td data-label="Category" class="small"><?= e($b['cat_name'] ?? '—') ?></td>
    <td data-label="City" class="small"><?= e($b['city_name'] ?? '—') ?></td>
    <td data-label="Owner" class="small"><?= $b['user_id'] ? '<a class="text-decoration-none" href="' . url('/admin/users/' . $b['user_id']) . '">#' . (int)$b['user_id'] . '</a>' : '<span class="text-muted">claimed soon</span>' ?></td>
    <td data-label="Status"><?= status_badge($b['status']) ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <?php if ($b['status'] !== 'approved'): ?><button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/businesses/' . $b['id'] . '/update') ?>', {action:'approve'})">Approve</button><?php endif; ?>
      <?php if ($b['status'] === 'pending'): ?><button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/businesses/' . $b['id'] . '/update') ?>', {action:'reject'})">Reject</button><?php endif; ?>
      <button class="btn btn-sm btn-outline-primary" onclick="adminAction('<?= url('/admin/businesses/' . $b['id'] . '/update') ?>', {action:'verify'})">Verify</button>
      <button class="btn btn-sm btn-outline-dark" onclick="adminAction('<?= url('/admin/businesses/' . $b['id'] . '/update') ?>', {action:'feature'})">Feature</button>
      <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/businesses/' . $b['id'] . '/update') ?>', {action:'delete'}, 'Delete business?')"><i class="fa-regular fa-trash-can"></i></button>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-map-location-dot"></i><h5>No businesses</h5></div><?php endif; ?>
