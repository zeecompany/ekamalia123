<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold mb-0"><?= e(t('nav.notifications')) ?></h1>
  <form method="post" action="<?= url('/notifications/read-all') ?>"><?= csrf_field() ?><button class="btn btn-outline-success btn-sm rounded-pill">Mark all read</button></form>
</div>
<?php foreach ($notifications as $n): $n['is_read'] = 1; ?>
<a class="ek-card p-3 mb-2 d-flex gap-3 align-items-start text-decoration-none text-dark <?= $n['is_read'] ? '' : 'border-success' ?>" href="<?= e($n['url'] ?: '#') ?>">
  <i class="fa-solid fa-bell text-success mt-1"></i>
  <div class="flex-grow-1">
    <div class="fw-semibold small"><?= e($n['title']) ?></div>
    <div class="text-muted small"><?= e($n['body']) ?></div>
    <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($n['created_at'])) ?></div>
  </div>
</a>
<?php endforeach; ?>
<?php if (!$notifications): ?><div class="ek-empty"><i class="fa-regular fa-bell"></i><h5>No notifications</h5></div><?php endif; ?>
