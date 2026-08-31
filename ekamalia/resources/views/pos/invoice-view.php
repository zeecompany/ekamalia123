<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h4 fw-bold mb-1"><?= e($sale['invoice_no']) ?> <?= $sale['is_hold'] ? '<span class="badge text-bg-warning">HOLD</span>' : status_badge($sale['status']) ?></h1>
    <div class="text-muted small"><?= e(fmt_date($sale['created_at'], 'd M Y, h:i A')) ?> • cashier <?= e($sale['cashier_name'] ?? '') ?></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-ek-sm" target="_blank" href="<?= url('/pos/invoice/' . $sale['id'] . '/print') ?>"><i class="fa-solid fa-print me-1"></i>Print</a>
    <a class="btn btn-outline-secondary btn-sm rounded-pill" href="<?= url('/pos/invoices') ?>">All invoices</a>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="ek-card p-0 overflow-hidden">
      <table class="table align-middle mb-0">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['name']) ?></td><td><?= (int)$it['quantity'] ?></td><td><?= money($it['price']) ?></td><td class="text-end"><b><?= money($it['total']) ?></b></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="p-3 border-top">
        <div class="row">
          <div class="col-md-6 small text-muted">Customer: <b class="text-dark"><?= e($sale['customer_name'] ?? 'Walk-in') ?></b><?= !empty($sale['customer_phone']) ? ' • ' . e($sale['customer_phone']) : '' ?></div>
          <div class="col-md-6 text-md-end">
            <div class="small">Subtotal <?= money($sale['subtotal']) ?><?= (float)$sale['discount'] ? ' • Discount -' . money($sale['discount']) : '' ?><?= (float)$sale['tax'] ? ' • Tax ' . money($sale['tax']) : '' ?></div>
            <div class="fs-4 fw-bold text-success"><?= money($sale['total']) ?></div>
            <div class="small text-muted"><?= strtoupper($sale['payment_method']) ?> • paid <?= money($sale['paid_amount']) ?><?= (float)$sale['change_amount'] ? ' • change ' . money($sale['change_amount']) : '' ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="ek-card p-4">
      <h6 class="fw-bold mb-3">Actions</h6>
      <a class="btn btn-ek w-100 rounded-pill mb-2" href="<?= url('/pos') ?>"><i class="fa-solid fa-cash-register me-1"></i>New Sale</a>
      <?php if ($sale['type'] === 'sale' && !$sale['is_hold']): ?>
      <a class="btn btn-outline-danger w-100 rounded-pill" href="<?= url('/pos/returns?invoice=' . $sale['invoice_no']) ?>"><i class="fa-solid fa-rotate-left me-1"></i>Return Items</a>
      <?php endif; ?>
    </div>
  </div>
</div>
