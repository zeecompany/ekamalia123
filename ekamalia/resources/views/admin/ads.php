<h1 class="h4 fw-bold mb-4">Classified Ads Moderation <span class="text-muted fs-6">(<?= number_format($total) ?>)</span></h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'active' => 'Active', 'paused' => 'Paused', 'sold' => 'Sold', 'rejected' => 'Rejected', 'expired' => 'Expired'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
  <form class="d-flex gap-2 ms-auto" method="get"><input type="hidden" name="status" value="<?= e($status) ?>"><input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Ad title…"><button class="btn btn-ek-sm">Search</button></form>
</div>
<?php if ($ads): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Ad</th><th>Posted By</th><th>City</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($ads as $a): ?>
  <tr>
    <td data-label="Ad"><div class="d-flex align-items-center gap-2">
      <img src="<?= e(img_or($a['image'] ?? '')) ?>" style="width:40px;height:40px;border-radius:8px;object-fit:cover" alt="">
      <div><b class="small"><?= e($a['title']) ?></b>
        <div class="text-muted" style="font-size:.66rem"><?= $a['is_featured'] ? '⭐ ' : '' ?><?= $a['is_urgent'] ? '🔴 ' : '' ?><?= (int)$a['views'] ?> views</div></div></div></td>
    <td data-label="Posted By" class="small"><a class="text-decoration-none" href="<?= url('/admin/users/' . $a['user_id']) ?>"><?= e($a['user_name']) ?></a></td>
    <td data-label="City" class="small"><?= e($a['city_name'] ?? '—') ?></td>
    <td data-label="Price" class="small"><?= $a['price'] > 0 ? money($a['price']) : '—' ?></td>
    <td data-label="Status"><?= status_badge($a['status']) ?><?= $a['status_note'] ? '<div class="text-muted" style="font-size:.66rem">' . e($a['status_note']) . '</div>' : '' ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <?php if ($a['status'] === 'pending'): ?>
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'approve'})">Approve</button>
        <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      <?php else: ?>
        <button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'feature', days:7}, 'Feature for 7 days?')">Feature 7d</button>
        <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'urgent', days:3}, 'Mark urgent 3 days?')">Urgent 3d</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'pause'}, 'Pause this ad?')">Pause</button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/ads/' . $a['id'] . '/update') ?>', {action:'delete'}, 'Delete this ad?')"><i class="fa-regular fa-trash-can"></i></button>
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
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-tag"></i><h5>No ads found</h5></div><?php endif; ?>
