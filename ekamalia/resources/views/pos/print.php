<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= e($sale['invoice_no']) ?> — eKamalia POS</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Segoe UI',Tahoma,sans-serif; color:#111; font-size:12px; }
  .sheet { max-width:210mm; margin:0 auto; padding:24px; }
  .inv-head { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #0B7A3E; padding-bottom:12px; margin-bottom:14px; }
  .brand { font-size:22px; font-weight:800; color:#0B7A3E; }
  .brand small { display:block; font-size:10px; color:#555; font-weight:400; }
  .inv-meta { text-align:right; font-size:11px; }
  .inv-meta b { font-size:15px; }
  .badge-st { display:inline-block; padding:2px 10px; border-radius:20px; font-size:10px; font-weight:700; background:#e8f5ee; color:#0B7A3E; }
  .two-col { display:flex; gap:16px; margin-bottom:12px; }
  .two-col > div { flex:1; background:#f7f9f8; border-radius:8px; padding:10px; font-size:11px; }
  .two-col h4 { font-size:10px; text-transform:uppercase; color:#0B7A3E; margin-bottom:4px; }
  table { width:100%; border-collapse:collapse; margin-bottom:12px; }
  th { background:#0B7A3E; color:#fff; padding:6px 8px; text-align:left; font-size:10px; text-transform:uppercase; }
  td { padding:6px 8px; border-bottom:1px solid #e5e9e7; }
  .r { text-align:right; }
  tfoot td { font-weight:600; }
  .totals { margin-left:auto; width:260px; }
  .totals .grand td { background:#0B7A3E; color:#fff; font-size:14px; font-weight:800; border:none; }
  .foot { text-align:center; font-size:10px; color:#666; border-top:1px dashed #999; padding-top:8px; margin-top:14px; }
  /* Thermal 80mm */
  body.thermal { font-size:11px; width:80mm; }
  body.thermal .sheet { padding:6px; max-width:80mm; }
  body.thermal .inv-head, body.thermal .two-col { display:block; }
  body.thermal .inv-meta { text-align:left; margin-top:6px; }
  body.thermal .brand { font-size:16px; }
  body.thermal table { font-size:10px; }
  body.thermal th, body.thermal td { padding:3px 4px; }
  body.thermal .totals { width:100%; margin:0; }
  @media print { .no-print { display:none !important; } body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
  .toolbar { text-align:center; padding:12px; background:#f2f4f3; }
  .toolbar button { padding:8px 22px; border:none; border-radius:20px; background:#0B7A3E; color:#fff; font-weight:700; margin:0 4px; cursor:pointer; }
  .toolbar button.gray { background:#555; }
</style>
</head>
<body class="<?= $sale['is_thermal'] ? 'thermal' : '' ?>">
<div class="toolbar no-print">
  <button onclick="window.print()">🖨 Print</button>
  <button class="gray" onclick="location.href='<?= e(url('/pos/invoice/' . $sale['id'] . '?thermal=' . ($sale['is_thermal'] ? '0' : '1'))) ?>'"><?= $sale['is_thermal'] ? 'A4 / Full' : 'Thermal 80mm' ?></button>
  <button class="gray" onclick="location.href='<?= e(url('/pos/invoices?type=' . $sale['type'])) ?>'">Back to POS</button>
</div>
<div class="sheet">
  <div class="inv-head">
    <div class="brand"><?= e($shop['name']) ?><small><?= e($shop['address'] ?? 'Kamalia') ?><?= $shop['phone'] ? ' • ' . e($shop['phone']) : '' ?></small></div>
    <div class="inv-meta">
      <b><?= $sale['type'] === 'quotation' ? 'QUOTATION' : 'SALES INVOICE' ?></b><br>
      No: <b><?= e($sale['invoice_no']) ?></b><br>
      Date: <?= e(fmt_date($sale['created_at'], 'd M Y, h:i A')) ?><br>
      <span class="badge-st"><?= e(strtoupper($sale['payment_method'])) ?><?= $sale['is_hold'] ? ' • HOLD' : '' ?></span>
    </div>
  </div>
  <div class="two-col">
    <div><h4>Billed To</h4><?= $customer ? '<b>' . e($customer['name']) . '</b><br>' . e($customer['phone'] ?? '') . '<br>' . e($customer['address'] ?? '') : 'Walk-in Customer' ?></div>
    <div><h4>Served By</h4><b><?= e($cashier['name']) ?></b><br>Terminal: eKamalia POS</div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Item</th><th class="r">Qty</th><th class="r">Price</th><th class="r">Total</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i => $it): ?>
      <tr><td><?= $i + 1 ?></td><td><?= e($it['name']) ?></td><td class="r"><?= (int)$it['quantity'] ?></td><td class="r"><?= number_format((float)$it['unit_price']) ?></td><td class="r"><?= number_format((float)$it['line_total']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <table class="totals">
    <tr><td>Subtotal</td><td class="r">Rs <?= number_format((float)$sale['subtotal']) ?></td></tr>
    <?php if ((float)$sale['discount'] > 0): ?><tr><td>Discount</td><td class="r">- Rs <?= number_format((float)$sale['discount']) ?></td></tr><?php endif; ?>
    <?php if ((float)$sale['tax'] > 0): ?><tr><td>Tax (<?= e($sale['tax_percent']) ?>%)</td><td class="r">Rs <?= number_format((float)$sale['tax']) ?></td></tr><?php endif; ?>
    <tr class="grand"><td>TOTAL</td><td class="r">Rs <?= number_format((float)$sale['total']) ?></td></tr>
    <tr><td>Paid (<?= e($sale['payment_method']) ?>)</td><td class="r">Rs <?= number_format((float)$sale['paid_amount']) ?></td></tr>
    <?php if ((float)$sale['change_amount'] > 0): ?><tr><td>Change</td><td class="r">Rs <?= number_format((float)$sale['change_amount']) ?></td></tr><?php endif; ?>
  </table>
  <div class="foot">
    <b>شکریہ! Thanks for shopping with <?= e($shop['name']) ?></b><br>
    Powered by eKamalia.com — Kamalia Ka Apna Digital Bazaar<br>
    Goods once sold: <?= e($shop['return_policy'] ?? '7-day check warranty applies') ?>
  </div>
</div>
</body>
</html>
