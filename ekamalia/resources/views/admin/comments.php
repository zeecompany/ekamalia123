<h1 class="h4 fw-bold mb-4">Comments Moderation</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'visible' => 'Visible', 'hidden' => 'Hidden', 'deleted' => 'Deleted'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php foreach ($comments as $c): ?>
<div class="admin-card p-3 mb-2">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div class="small"><b><?= e($c['user_name']) ?></b> <span class="badge text-bg-light border"><?= e($c['item_type']) ?> #<?= (int)$c['item_id'] ?></span>
      <?= $c['parent_id'] ? '<span class="badge text-bg-secondary">reply</span>' : '' ?></div>
    <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($c['created_at'])) ?></div>
  </div>
  <p class="small mb-2 mt-1"><?= e($c['body']) ?></p>
  <div class="chip-actions d-flex flex-wrap">
    <?php if ($c['status'] !== 'visible'): ?><button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/comments/' . $c['id'] . '/update') ?>', {action:'restore'})">Restore</button><?php endif; ?>
    <?php if ($c['status'] === 'visible'): ?><button class="btn btn-sm btn-outline-warning" onclick="adminAction('<?= url('/admin/comments/' . $c['id'] . '/update') ?>', {action:'hide'})">Hide</button><?php endif; ?>
    <button class="btn btn-sm btn-outline-dark" onclick="adminAction('<?= url('/admin/comments/' . $c['id'] . '/update') ?>', {action:'delete'}, 'Delete comment?')">Delete</button>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$comments): ?><div class="ek-empty"><i class="fa-regular fa-comments"></i><h5>No comments</h5></div><?php endif; ?>
