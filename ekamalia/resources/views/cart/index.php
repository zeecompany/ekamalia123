<div class="container py-4">
  <h1 class="h4 fw-bold mb-4 d-flex align-items-center gap-2">
    <i class="fa-solid fa-basket-shopping text-success"></i> <?= e(t('cart.title')) ?>
  </h1>

  <?php if (empty($groups)): ?>
    <div class="ek-empty">
      <div class="empty-icon"><i class="fa-solid fa-cart-shopping"></i></div>
      <h5><?= e(t('cart.empty')) ?></h5>
      <p>Discover thousands of quality products from top Kamalia stores and sellers.</p>
      <a class="btn btn-ek rounded-pill px-4 py-2" href="<?= url('/products') ?>">
        <i class="fa-solid fa-bag-shopping me-1"></i> Start Shopping
      </a>
    </div>
  <?php else: ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <?php foreach ($groups as $g): ?>
      <div class="ek-card p-4 mb-3">
        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-store text-success fs-5"></i>
            <a class="fw-bold text-dark text-decoration-none" href="<?= url('/shop/' . $g['shop_slug']) ?>"><?= e($g['shop_name']) ?></a>
          </div>
          <span class="badge text-bg-light text-muted fw-normal" style="font-size:0.75rem">Local Seller</span>
        </div>

        <?php foreach ($g['items'] as $it): ?>
        <div class="cart-line" data-cart-row="<?= $it['product_id'] ?>">
          <img src="<?= e(img_or($it['image'] ?? '')) ?>" alt="<?= e($it['name']) ?>">
          <div class="flex-grow-1 min-w-0">
            <a class="small fw-bold d-block text-truncate text-dark text-decoration-none mb-1" href="<?= url('/product/' . $it['slug']) ?>"><?= e($it['name']) ?></a>
            <div class="text-muted small mb-2">
              <?= money($it['unit_price']) ?> × <?= (int)$it['quantity'] ?> = <b class="text-success fw-bold"><?= money($it['line_total']) ?></b>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="qty-btn" data-cart-qty="-1" aria-label="Decrease quantity">−</button>
              <input class="ci-qty form-control form-control-sm text-center" style="width:52px;font-weight:700" value="<?= (int)$it['quantity'] ?>" readonly aria-label="Quantity">
              <button class="qty-btn" data-cart-qty="1" aria-label="Increase quantity">+</button>
              <button class="btn btn-link btn-sm text-danger p-0 ms-3 text-decoration-none" data-cart-remove="<?= $it['product_id'] ?>" title="Remove item">
                <i class="fa-regular fa-trash-can me-1"></i> Remove
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between pt-3 small">
          <span class="text-muted">Subtotal (this store):</span>
          <b class="text-dark fs-6"><?= money($g['subtotal']) ?></b>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="p-3 bg-light rounded-3 text-muted small d-flex align-items-center gap-2 border">
        <i class="fa-solid fa-circle-info text-success fs-5"></i>
        <span>Items from different stores are organized into separate packages for rapid delivery by respective sellers.</span>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="ek-card p-4 position-sticky" style="top:90px">
        <h5 class="fw-bold mb-3">Order Summary</h5>
        <div class="d-flex justify-content-between small mb-2">
          <span class="text-muted"><?= e(t('cart.subtotal')) ?></span>
          <b><?= money($totals['subtotal']) ?></b>
        </div>
        <div class="d-flex justify-content-between small mb-2">
          <span class="text-muted"><?= e(t('cart.shipping')) ?></span>
          <b><?= $totals['shipping'] > 0 ? money($totals['shipping']) : '<span class="text-success">FREE</span>' ?></b>
        </div>
        <?php if (!empty($totals['discount']) && $totals['discount'] > 0): ?>
          <div class="d-flex justify-content-between small mb-2 text-success">
            <span><?= e(t('cart.discount')) ?> (<?= e($totals['coupon']) ?>)</span>
            <b>-<?= money($totals['discount']) ?></b>
          </div>
        <?php endif; ?>

        <hr class="my-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="fw-bold text-dark"><?= e(t('cart.total')) ?></span>
          <span class="fw-bold fs-4 text-success"><?= money($totals['total']) ?></span>
        </div>

        <div class="input-group mb-2">
          <input class="form-control" id="couponInput" placeholder="<?= e(t('cart.coupon')) ?>" value="<?= e($totals['coupon'] ?? '') ?>">
          <button class="btn btn-outline-success" id="applyCoupon"><?= e(t('cart.apply')) ?></button>
        </div>
        <?php if (!empty($totals['coupon'])): ?>
          <button class="btn btn-link btn-sm text-danger p-0 mb-3 text-decoration-none" id="removeCoupon">Remove coupon code</button>
        <?php endif; ?>

        <a class="btn btn-ek btn-lg w-100 rounded-pill mt-2 d-flex align-items-center justify-content-center gap-2" href="<?= url('/checkout') ?>">
          <span><?= e(t('cart.checkout')) ?></span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
