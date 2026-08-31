<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Audit Logs</h1>
  <form class="d-flex gap-2" method="get"><input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Filter by action…"><button class="btn btn-ek-sm">Filter</button></form>
</div>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Action</th><th>Entity</th><th>Details</th><th>User</th><th>IP</th><th>Time</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $l): ?>
  <tr>
    <td data-label="Action"><code class="small"><?= e($l['action']) ?></code></td>
    <td data-label="Entity" class="small"><?= e($l['entity_type'] ?? '') ?><?= $l['entity_id'] ? ' #' . (int)$l['entity_id'] : '' ?></td>
    <td data-label="Details" class="small text-muted"><?= e(mb_substr((string)($l['details'] ?? ''), 0, 60)) ?></td>
    <td data-label="User" class="small"><?= e($l['user_name'] ?? 'system') ?></td>
    <td data-label="IP" class="small text-muted"><?= e($l['ip'] ?? '') ?></td>
    <td data-label="Time" class="small text-nowrap"><?= e(fmt_date($l['created_at'], 'd M H:i')) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if (!$logs): ?><div class="ek-empty"><i class="fa-solid fa-clipboard-list"></i><h5>No log entries</h5></div><?php endif; ?>
