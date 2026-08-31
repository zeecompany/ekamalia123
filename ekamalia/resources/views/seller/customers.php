<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h4 fw-bold mb-0">Customers</h1><p class="text-muted small mb-0">Everyone who ordered from <?= e($shop['name']) ?></p></div>
  <div class="text-muted small"><i class="fa-regular fa-comment-dots me-1"></i><?= (int)$inquiries ?> customers have chat threads with you</div>
</div>
<div class="ek-card p-0 overflow-hidden">
  <div class="table-responsive"><table class="table mb-0 responsive-table">
    <thead><tr><th>Customer</th><th>Contact</th><th>Orders</th><th>Total Spent</th><th>Last Order</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td data-label="Customer"><b><?= e($r['name']) ?></b></td>
        <td data-label="Contact"><span class="small"><?= e($r['email']) ?></span><br><a class="small" href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a></td>
        <td data-label="Orders"><span class="badge text-bg-light border"><?= (int)$r['orders_count'] ?></span></td>
        <td data-label="Total Spent"><b class="text-success"><?= money($r['spent']) ?></b></td>
        <td data-label="Last Order"><span class="text-muted small"><?= $r['last_order'] ? e(fmt_date($r['last_order'])) : '—' ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5"><div class="ek-empty py-4"><i class="fa-solid fa-users"></i><p class="mb-0">No customers yet — your first order will appear here.</p></div></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
