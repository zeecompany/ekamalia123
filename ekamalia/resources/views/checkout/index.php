<div class="container py-4">
  <h1 class="h4 fw-bold mb-4"><i class="fa-solid fa-credit-card me-2 text-success"></i><?= e(t('checkout.title')) ?></h1>
  <form method="post" action="<?= url('/checkout') ?>">
    <?= csrf_field() ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="ek-card p-4 mb-3">
          <h5 class="fw-bold mb-3"><i class="fa-solid fa-truck me-1"></i><?= e(t('checkout.address')) ?></h5>
          <?php if ($addresses): ?>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($addresses as $i => $ad): ?>
            <label class="chip" style="cursor:pointer">
              <input type="radio" name="pick_address" class="d-none pick-addr" data-name="<?= e($ad['name']) ?>" data-phone="<?= e($ad['phone']) ?>" data-address="<?= e($ad['address']) ?>" data-city="<?= (int)$ad['city_id'] ?>" data-area="<?= e($ad['area'] ?? '') ?>" <?= $i === 0 ? 'checked' : '' ?>>
              <i class="fa-solid fa-location-dot text-success"></i> <?= e($ad['label'] ?: 'Address') ?>: <?= e($ad['name']) ?>, <?= e(mb_substr($ad['address'], 0, 40)) ?>…
            </label>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Receiver Name *</label><input class="form-control" name="ship_name" id="shipName" value="<?= e($addresses[0]['name'] ?? auth()['name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Mobile Number *</label><input class="form-control" name="ship_phone" id="shipPhone" value="<?= e($addresses[0]['phone'] ?? auth()['phone']) ?>" placeholder="03XX-XXXXXXX" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">City *</label>
              <select class="form-select" name="ship_city_id" id="shipCity" required>
                <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= ($addresses[0]['city_id'] ?? ($c['is_primary'] ? $c['id'] : 0)) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Area</label><input class="form-control" name="ship_area" id="shipArea" value="<?= e($addresses[0]['area'] ?? '') ?>"></div>
            <div class="col-12"><label class="form-label fw-semibold">Complete Address *</label><textarea class="form-control" name="ship_address" id="shipAddress" rows="2" required><?= e($addresses[0]['address'] ?? '') ?></textarea></div>
            <div class="col-12"><label class="form-label fw-semibold">Order Note <span class="text-muted fw-normal">(optional)</span></label><input class="form-control" name="note" placeholder="e.g. call before delivery"></div>
          </div>
          <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="save_address" value="1" checked><label class="form-check-label small">Save this address for future orders</label></div>
        </div>

        <div class="ek-card p-4 mb-3">
          <h5 class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave me-1"></i><?= e(t('checkout.payment')) ?></h5>
          <?php if (setting('cod_enabled') === '1'): ?>
          <label class="d-flex gap-3 border rounded-4 p-3 mb-2 payment-opt" style="cursor:pointer">
            <input type="radio" name="payment_method" value="cod" class="mt-1" checked>
            <div><b><i class="fa-solid fa-hand-holding-dollar me-1 text-success"></i><?= e(t('checkout.cod')) ?></b>
              <div class="text-muted small">Pay cash when your order arrives.</div></div>
          </label>
          <?php endif; ?>
          <?php if (setting('bank_transfer_enabled') === '1' && $banks): ?>
          <label class="d-flex gap-3 border rounded-4 p-3 payment-opt" style="cursor:pointer">
            <input type="radio" name="payment_method" value="bank_transfer" class="mt-1">
            <div><b><i class="fa-solid fa-building-columns me-1 text-success"></i><?= e(t('checkout.bank')) ?></b>
              <div class="text-muted small">Transfer to our account &amp; upload receipt. Verified quickly.</div>
              <select class="form-select form-select-sm mt-2" name="bank_account_id">
                <?php foreach ($banks as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['bank_name']) ?> — <?= e($b['account_title']) ?></option><?php endforeach; ?>
              </select></div>
          </label>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="ek-card p-4 position-sticky" style="top:90px">
          <h5 class="fw-bold mb-3">Your Order</h5>
          <?php foreach ($groups as $g): ?>
            <div class="small mb-2"><b class="text-success"><i class="fa-solid fa-store me-1"></i><?= e($g['shop_name']) ?></b>
              <span class="text-muted">(<?= count($g['items']) ?> items)</span></div>
          <?php endforeach; ?>
          <hr>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted"><?= e(t('cart.subtotal')) ?></span><b><?= money($totals['subtotal']) ?></b></div>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted"><?= e(t('cart.shipping')) ?></span><b><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'FREE' ?></b></div>
          <?php if ($totals['discount'] > 0): ?><div class="d-flex justify-content-between small mb-1 text-success"><span><?= e(t('cart.discount')) ?></span><b>-<?= money($totals['discount']) ?></b></div><?php endif; ?>
          <hr>
          <div class="d-flex justify-content-between mb-3"><span class="fw-bold">Total</span><span class="fw-bold fs-4 text-success"><?= money($totals['total']) ?></span></div>
          <button class="btn btn-ek btn-lg w-100 rounded-pill"><?= e(t('checkout.place_order')) ?> <i class="fa-solid fa-check ms-1"></i></button>
          <p class="text-muted text-center mt-2 mb-0" style="font-size:.7rem">By placing the order you agree to eKamalia <a href="<?= url('/page/terms') ?>">Terms</a>.</p>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
document.querySelectorAll('.pick-addr').forEach(r => r.addEventListener('change', () => {
  document.getElementById('shipName').value = r.dataset.name;
  document.getElementById('shipPhone').value = r.dataset.phone;
  document.getElementById('shipAddress').value = r.dataset.address;
  document.getElementById('shipCity').value = r.dataset.city;
  document.getElementById('shipArea').value = r.dataset.area;
}));
</script>
