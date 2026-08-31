<h1 class="h4 fw-bold mb-4">Reports Queue</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['open' => 'Open', 'reviewed' => 'Reviewed', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php foreach ($reports as $r): ?>
<div class="admin-card p-3 mb-2">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div><span class="badge text-bg-danger text-uppercase"><?= e($r['item_type'] ?? 'item') ?></span>
      <b class="small ms-1"><?= e($r['reason'] ?? 'Reported') ?></b>
      <span class="badge text-bg-light border"><?= e($r['item_type']) ?> #<?= (int)$r['item_id'] ?></span>
      <?= status_badge($r['status']) ?></div>
    <div class="text-muted" style="font-size:.68rem">by <?= e($r['reporter_name'] ?? 'anonymous') ?> • <?= e(time_ago($r['created_at'])) ?></div>
  </div>
  <?php if ($r['details']): ?><p class="small text-muted mb-2 mt-1"><?= e($r['details']) ?></p><?php endif; ?>
  <div class="chip-actions d-flex flex-wrap">
    <?php if (in_array($r['status'], ['open', 'reviewed'], true)): ?>
      <button class="btn btn-sm btn-outline-secondary" onclick="adminAction('<?= url('/admin/reports/' . $r['id'] . '/update') ?>', {action:'reviewed'})">Mark Reviewed</button>
      <button class="btn btn-sm btn-outline-secondary" onclick="adminAction('<?= url('/admin/reports/' . $r['id'] . '/update') ?>', {action:'dismissed'})">Dismiss</button>
      <button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/reports/' . $r['id'] . '/update') ?>', {action:'hide_item'}, 'Hide the reported item?')">Hide Item</button>
      <button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/reports/' . $r['id'] . '/update') ?>', {action:'delete_item'}, 'DELETE the reported item?')">Delete Item</button>
      <button class="btn btn-sm btn-danger" onclick="adminAction('<?= url('/admin/reports/' . $r['id'] . '/update') ?>', {action:'ban_user'}, 'BAN the owner of this item?')">Ban Owner</button>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$reports): ?><div class="ek-empty"><i class="fa-regular fa-face-smile"></i><h5>Queue is clear</h5></div><?php endif; ?>
