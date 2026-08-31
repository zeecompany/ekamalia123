<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-rotate-left me-2 text-danger"></i>Sales Returns</h1>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/pos/returns') ?>"><i class="fa-solid fa-plus me-1"></i>New Return</a>
</div>
<?php if ($returns): ?>
<div class="table-responsive ek-card table-responsive-stack">
<table class="table align-middle">
  <thead><tr><th>Return</th><th>Original Invoice</th><th>Date</th><th>Reason</th><th class="text-end">Amount</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($returns as $r): ?>
  <tr>
    <td data-label="Return"><b class="text-danger"><?= e($r['return_no'] ?? '#' . $r['id']) ?></b></td>
    <td data-label="Invoice" class="small"><?= e($r['invoice_no']) ?></td>
    <td data-label="Date" class="small"><?= e(fmt_date($r['created_at'], 'd M, h:i A')) ?><div class="text-muted" style="font-size:.66rem">by <?= e($r['by_user'] ?? '') ?></div></td>
    <td data-label="Reason" class="small"><?= e($r['reason'] ?? '—') ?></td>
    <td data-label="Amount" class="text-end"><b class="text-danger">-<?= money($r['total'] ?? $r['refund_amount'] ?? 0) ?></b></td>
    <td><a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= url('/pos/invoice/' . $r['sale_id'] . '/print') ?>"><i class="fa-solid fa-print"></i></a></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-rotate-left"></i><h5>No returns yet</h5><a class="btn btn-ek rounded-pill px-4" href="<?= url('/pos/returns') ?>">Process a Return</a></div><?php endif; ?>
