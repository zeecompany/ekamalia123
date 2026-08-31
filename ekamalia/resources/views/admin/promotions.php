<h1 class="h4 fw-bold mb-4">Promotion Requests</h1>
<?php if ($requests): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Type</th><th>Item</th><th>Requested By</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($requests as $r): ?>
  <tr>
    <td data-label="Type"><span class="badge text-bg-warning text-uppercase"><?= e(str_replace('_', ' ', $r['type'])) ?></span></td>
    <td data-label="Item" class="small"><b><?= e($r['ad_title'] ?? $r['product_name'] ?? 'Item #' . $r['item_id']) ?></b></td>
    <td data-label="Requested By" class="small"><?= e($r['user_name']) ?><div class="text-muted" style="font-size:.66rem"><?= e(time_ago($r['created_at'])) ?></div></td>
    <td data-label="Status"><?= status_badge($r['status']) ?><?= $r['admin_note'] ? '<div class="text-muted" style="font-size:.66rem">' . e($r['admin_note']) . '</div>' : '' ?></td>
    <td data-label="Actions">
      <?php if ($r['status'] === 'pending'): ?>
      <div class="chip-actions d-flex flex-wrap">
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/promotions/' . $r['id'] . '/update') ?>', {action:'approve', days:7}, 'Approve for 7 days?')">Approve 7d</button>
        <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/promotions/' . $r['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      </div>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-rocket"></i><h5>No promotion requests</h5></div><?php endif; ?>
