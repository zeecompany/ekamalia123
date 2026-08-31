<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-file-invoice me-2 text-success"></i><?= $type === 'quotation' ? 'Quotations' : 'Sales Invoices' ?></h1>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/pos') ?>"><i class="fa-solid fa-cash-register me-1"></i>New Sale</a>
</div>
<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="type" value="<?= e($type) ?>">
  <div class="col-md-4"><input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Invoice # or customer…"></div>
  <div class="col-md-3"><input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>"></div>
  <div class="col-md-3"><input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>"></div>
  <div class="col-md-2"><button class="btn btn-ek w-100 rounded-pill">Filter</button></div>
</form>
<?php if ($sales): ?>
<div class="table-responsive ek-card table-responsive-stack">
<table class="table align-middle">
  <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($sales as $s): ?>
  <tr>
    <td data-label="Invoice"><b><?= e($s['invoice_no']) ?></b><?= $s['is_hold'] ? ' <span class="badge text-bg-warning">HOLD</span>' : '' ?></td>
    <td data-label="Date" class="small"><?= e(fmt_date($s['created_at'], 'd M, h:i A')) ?></td>
    <td data-label="Customer" class="small"><?= e($s['customer_name'] ?? 'Walk-in') ?></td>
    <td data-label="Total"><b class="text-success"><?= money($s['total']) ?></b></td>
    <td data-label="Payment"><span class="badge text-bg-light border"><?= strtoupper($s['payment_method']) ?></span></td>
    <td data-label="Actions"><div class="chip-actions d-flex flex-wrap">
      <a class="btn btn-sm btn-ek-sm" target="_blank" href="<?= url('/pos/invoice/' . $s['id'] . '/print') ?>"><i class="fa-solid fa-print"></i></a>
      <?php if ($s['is_hold']): ?>
      <form method="post" action="<?= url('/pos/holds/' . $s['id'] . '/resume') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-warning" title="Resume"><i class="fa-solid fa-play"></i></button></form>
      <?php endif; ?>
      <?php if ($type === 'sale'): ?>
      <a class="btn btn-sm btn-outline-danger" href="<?= url('/pos/returns?invoice=' . $s['invoice_no']) ?>" title="Return items"><i class="fa-solid fa-rotate-left"></i></a>
      <?php endif; ?>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-file-invoice"></i><h5>No <?= $type ?> records</h5></div><?php endif; ?>
