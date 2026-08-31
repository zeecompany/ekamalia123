<h1 class="h4 fw-bold mb-4">POS Reports</h1>
<form class="row g-2 mb-4" method="get">
  <div class="col-md-3"><label class="form-label small fw-semibold">From</label><input type="date" class="form-control" name="from" value="<?= e($from) ?>"></div>
  <div class="col-md-3"><label class="form-label small fw-semibold">To</label><input type="date" class="form-control" name="to" value="<?= e($to) ?>"></div>
  <div class="col-md-2 d-flex align-items-end"><button class="btn btn-ek w-100 rounded-pill">Apply</button></div>
  <div class="col-md-4 d-flex align-items-end"><a class="btn btn-outline-success rounded-pill" href="?<?= e(http_build_query(['from' => $from, 'to' => $to, 'export' => 'csv'])) ?>"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</a></div>
</form>
<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-sack-dollar"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($summary['sales']) ?></div><div class="lbl">Net Sales</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-arrow-trend-up"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($summary['profit']) ?></div><div class="lbl">Profit</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-receipt"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($summary['expenses']) ?></div><div class="lbl">Expenses</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-rotate-left"></i></div><div><div class="val" style="font-size:1.05rem"><?= money($summary['returns']) ?></div><div class="lbl">Returns</div></div></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-6"><div class="ek-card p-4"><h6 class="fw-bold mb-3">By Payment Method</h6>
    <?php foreach ($byPayment as $bp): ?>
    <div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-uppercase fw-semibold"><?= e($bp['payment_method']) ?></span><span><?= (int)$bp['n'] ?> sales</span><b><?= money($bp['total']) ?></b></div>
    <?php endforeach; ?>
    <?php if (!$byPayment): ?><p class="text-muted small mb-0">No sales in range.</p><?php endif; ?></div></div>
  <div class="col-lg-6"><div class="ek-card p-4"><h6 class="fw-bold mb-3">Top Products</h6>
    <?php foreach ($topProducts as $tp): ?>
    <div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-truncate"><?= e($tp['name']) ?></span><span><?= (int)$tp['q'] ?> sold</span><b class="text-success"><?= money($tp['amt']) ?></b></div>
    <?php endforeach; ?>
    <?php if (!$topProducts): ?><p class="text-muted small mb-0">No sales in range.</p><?php endif; ?></div></div>
</div>
