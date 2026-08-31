<div class="mb-4"><h1 class="h4 fw-bold mb-0">Payments &amp; Earnings</h1><p class="text-muted small mb-0">Money flow for <?= e($shop['name']) ?></p></div>
<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-money-bill-wave"></i></div><div><div class="val" style="font-size:1rem"><?= money($cod) ?></div><div class="lbl">COD Collected</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-regular fa-clock"></i></div><div><div class="val" style="font-size:1rem"><?= money($codPending) ?></div><div class="lbl">COD In Transit</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-building-columns"></i></div><div><div class="val" style="font-size:1rem"><?= money($bank) ?></div><div class="lbl">Bank Received</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-hourglass-half"></i></div><div><div class="val" style="font-size:1rem"><?= money($bankPending) ?></div><div class="lbl">Bank Awaiting Proof</div></div></div></div>
</div>
<?php $commissionAmt = round(($cod + $bank) * $commission / 100, 2); ?>
<div class="ek-card p-4 mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h6 class="fw-bold mb-1">Platform Commission</h6>
      <p class="text-muted small mb-0">Rate: <?= number_format($commission, 2) ?>% on completed sales (COD collected + bank verified)</p>
    </div>
    <div class="text-end">
      <div class="h5 fw-bold text-danger mb-0">-<?= money($commissionAmt) ?></div>
      <div class="text-success fw-bold">Net: <?= money($cod + $bank - $commissionAmt) ?></div>
    </div>
  </div>
</div>
<div class="ek-card p-0 overflow-hidden">
  <div class="table-responsive"><table class="table mb-0 responsive-table">
    <thead><tr><th>Order</th><th>Buyer</th><th>Method</th><th>Payment</th><th>Order Status</th><th>Amount</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $o): ?>
      <tr>
        <td data-label="Order"><a class="fw-bold text-decoration-none" href="<?= url('/seller/orders') ?>"><?= e($o['no']) ?></a><br><span class="text-muted" style="font-size:.7rem"><?= e(fmt_date($o['created_at'])) ?></span></td>
        <td data-label="Buyer"><?= e($o['buyer']) ?></td>
        <td data-label="Method"><span class="badge text-bg-light border"><?= strtoupper(str_replace('_', ' ', $o['payment_method'])) ?></span></td>
        <td data-label="Payment"><span class="badge <?= $o['payment_status'] === 'paid' || $o['payment_status'] === 'verified' ? 'text-bg-success' : ($o['payment_status'] === 'refunded' ? 'text-bg-danger' : 'text-bg-warning') ?>"><?= e(ucfirst($o['payment_status'])) ?></span></td>
        <td data-label="Order Status"><span class="badge text-bg-secondary"><?= e(ucfirst($o['status'])) ?></span></td>
        <td data-label="Amount"><b><?= money($o['total']) ?></b></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6"><div class="ek-empty py-4"><i class="fa-solid fa-wallet"></i><p class="mb-0">No orders yet.</p></div></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
