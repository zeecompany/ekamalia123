<h1 class="h4 fw-bold mb-4">Payment Verification</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['submitted' => 'To Verify', 'pending' => 'Awaiting Receipt', 'verified' => 'Verified', 'rejected' => 'Rejected', 'refunded' => 'Refunded'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php if ($payments): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>Order</th><th>Customer</th><th>Bank</th><th>Reference</th><th>Amount</th><th>Receipt</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($payments as $p): ?>
  <tr>
    <td data-label="Order"><a class="fw-bold small text-decoration-none" href="<?= url('/admin/orders/' . $p['order_id']) ?>"><?= e($p['order_number']) ?></a>
      <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($p['created_at'])) ?></div></td>
    <td data-label="Customer" class="small"><?= e($p['user_name']) ?></td>
    <td data-label="Bank" class="small"><?= e($p['bank_name'] ?? '—') ?></td>
    <td data-label="Reference" class="small"><code><?= e($p['transaction_id'] ?: '—') ?></code></td>
    <td data-label="Amount"><b><?= money($p['amount']) ?></b></td>
    <td data-label="Receipt"><?php if ($p['proof_image']): ?><a href="<?= e(upload_url($p['proof_image'])) ?>" target="_blank"><img src="<?= e(upload_url($p['proof_image'])) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #ddd" alt="receipt"></a><?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
    <td data-label="Actions">
      <?php if ($p['status'] === 'submitted'): ?>
      <div class="chip-actions d-flex flex-wrap">
        <button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/payments/' . $p['id'] . '/update') ?>', {action:'verify'})">Verify</button>
        <button class="btn btn-sm btn-outline-danger" onclick="const n=prompt('Reason:'); if(n!==null) adminAction('<?= url('/admin/payments/' . $p['id'] . '/update') ?>', {action:'reject', note:n})">Reject</button>
      </div>
      <?php else: ?><?= status_badge($p['status']) ?><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-circle-check"></i><h5>Nothing in this queue</h5></div><?php endif; ?>
