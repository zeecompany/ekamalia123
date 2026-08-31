<link rel="stylesheet" href="<?= asset('css/pos.css') ?>">
<div class="pos-grid">
  <div class="pos-products">
    <form class="d-flex gap-2 mb-3 flex-wrap" method="get" id="posSearchForm">
      <div class="input-group" style="max-width:420px">
        <span class="input-group-text bg-white"><i class="fa-solid fa-barcode"></i></span>
        <input class="form-control" name="q" id="barcodeInput" value="<?= e($q) ?>" placeholder="Scan barcode or search product…" autofocus autocomplete="off">
      </div>
      <button class="btn btn-ek px-3">Search</button>
      <a class="btn btn-outline-secondary" href="<?= url('/pos') ?>" title="Clear"><i class="fa-solid fa-xmark"></i></a>
    </form>
    <div class="d-flex gap-2 flex-wrap mb-3">
      <a class="chip <?= !$catId ? 'active' : '' ?>" href="?<?= e(http_build_query(array_diff_key($_GET, ['cat' => 1]))) ?>">All</a>
      <?php foreach ($cats as $c): ?><a class="chip <?= $catId == $c['id'] ? 'active' : '' ?>" href="?<?= e(http_build_query(array_merge($_GET, ['cat' => $c['id']]))) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
    </div>
    <div class="prod-grid" id="prodGrid">
      <?php foreach ($products as $pr): ?>
      <div class="prod-tile <?= (int)$pr['stock'] <= 0 ? 'out' : '' ?>" data-product='<?= json_encode(["id" => (int)$pr["id"], "name" => $pr["name"], "price" => (float)($pr["sale_price"] ?: $pr["price"]), "stock" => (int)$pr["stock"], "image" => img_or($pr["image"] ?? "")]) ?>'>
        <img src="<?= e(img_or($pr['image'] ?? '')) ?>" alt="" loading="lazy">
        <div class="n"><?= e($pr['name']) ?></div>
        <div class="p"><?= money($pr['sale_price'] ?: $pr['price']) ?></div>
        <div class="s"><?= (int)$pr['stock'] ?> in stock</div>
      </div>
      <?php endforeach; ?>
      <?php if (!$products): ?><div class="text-muted p-4 w-100 text-center">No products found. Add products from your seller dashboard first.</div><?php endif; ?>
    </div>
  </div>

  <div class="pos-cart">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
      <b><i class="fa-solid fa-cart-shopping me-1 text-success"></i> Current Sale</b>
      <div class="d-flex gap-1">
        <button class="pos-sec-btn" id="btnHold" title="Hold sale"><i class="fa-solid fa-pause"></i> Hold</button>
        <button class="pos-sec-btn" onclick="clearCart()" title="Clear"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>
    <?php if ($holds): ?>
    <div class="px-3 py-2 bg-light border-bottom">
      <div class="small fw-semibold text-muted mb-1">Held sales (resume)</div>
      <div class="d-flex gap-1 flex-wrap">
        <?php foreach ($holds as $h): ?>
        <form method="post" action="<?= url('/pos/holds/' . $h['id'] . '/resume') ?>"><?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-warning rounded-pill"><?= e($h['invoice_no']) ?> (<?= $h['items'] ?>)</button></form>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="cart-lines" id="cartLines"><div class="text-center text-muted py-5 small" id="cartEmpty">Cart is empty — click products to add</div></div>
    <div class="p-3 border-top">
      <div class="row g-2 mb-2">
        <div class="col-6"><label class="form-label small mb-0">Customer</label>
          <select class="form-select form-select-sm" id="posCustomer">
            <option value="">Walk-in customer</option>
            <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?><?= $c['phone'] ? ' — ' . e($c['phone']) : '' ?></option><?php endforeach; ?>
          </select></div>
        <div class="col-3"><label class="form-label small mb-0">Disc. Rs</label><input type="number" class="form-control form-control-sm" id="posDiscount" value="0" min="0"></div>
        <div class="col-3"><label class="form-label small mb-0">Tax %</label><input type="number" class="form-control form-control-sm" id="posTax" value="0" min="0"></div>
      </div>
      <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Subtotal</span><b id="posSubtotal">Rs 0</b></div>
      <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Discount + Tax</span><b id="posAdj">Rs 0</b></div>
      <div class="d-flex justify-content-between fs-5 fw-bold mb-2"><span>Total</span><span class="text-success" id="posTotal">Rs 0</span></div>
      <div class="d-flex gap-1 mb-2" role="group" aria-label="Payment method">
        <?php foreach ([['cash', 'Cash'], ['bank', 'Bank'], ['card', 'Card'], ['other', 'Other']] as $pm): ?>
          <button type="button" class="pos-sec-btn pay-method <?= $pm[0] === 'cash' ? 'active border-success text-success' : '' ?>" data-v="<?= $pm[0] ?>"><?= $pm[1] ?></button>
        <?php endforeach; ?>
      </div>
      <button class="pos-pay-btn" id="btnCharge" disabled><i class="fa-solid fa-money-bill-wave me-2"></i>CHARGE <span id="btnTotal">Rs 0</span></button>
      <button class="pos-sec-btn mt-2 w-100" id="btnQuote"><i class="fa-solid fa-file-lines me-1"></i> Save as Quotation</button>
    </div>
  </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-body p-4">
        <h5 class="fw-bold mb-3 text-center">Receive Payment</h5>
        <div class="text-center mb-3"><div class="text-muted small">Total due</div><div class="fs-3 fw-bold text-success" id="payDue">Rs 0</div></div>
        <label class="form-label small fw-semibold">Amount received</label>
        <input type="number" class="form-control form-control-lg text-center mb-2" id="payReceived">
        <div class="text-center mb-3">Change: <b id="payChange" class="text-warning fs-5">Rs 0</b></div>
        <button class="pos-pay-btn" id="btnConfirmPay">✓ Complete Sale</button>
        <button class="btn btn-light w-100 mt-2 rounded-pill" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
window.POS_CONFIG = { checkoutUrl: <?= json_encode(url('/pos/checkout')) ?>, invoiceUrl: <?= json_encode(url('/pos/invoices?type=sale')) ?> };
window.POS_PRODUCTS = [<?php foreach ($products as $pr): echo json_encode(['id' => (int)$pr['id'], 'name' => $pr['name'], 'price' => (float)($pr['sale_price'] ?: $pr['price']), 'stock' => (int)$pr['stock'], 'barcode' => (string)($pr['barcode'] ?? ''), 'sku' => (string)($pr['sku'] ?? ''), 'image' => img_or($pr['image'] ?? '')]) . ','; endforeach; ?>];
window.POS_RESUME = <?= json_encode($resume ?? null) ?: 'null' ?>;
</script>
