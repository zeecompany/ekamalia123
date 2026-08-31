<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Assalam-o-Alaikum, <?= e(explode(' ', $me['name'])[0]) ?>! 👋</h1>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>"><i class="fa-solid fa-plus me-1"></i>Post Ad</a>
</div>

<div class="row g-3 mb-4 row-cols-2 row-cols-lg-4">
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-box"></i></div><div><div class="val"><?= $stats['orders'] ?></div><div class="lbl">Total Orders</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-truck"></i></div><div><div class="val"><?= $stats['active_orders'] ?></div><div class="lbl">Active Orders</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-tag"></i></div><div><div class="val"><?= $stats['ads'] ?></div><div class="lbl">My Ads</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-red"><i class="fa-solid fa-heart"></i></div><div><div class="val"><?= $stats['wishlist'] ?></div><div class="lbl">Wishlist</div></div></div></div>
</div>

<?php if ($posStatus && $posStatus['status'] === 'pending'): ?>
<div class="alert alert-warning d-flex align-items-center gap-2"><i class="fa-solid fa-cash-register"></i> Your POS request is under review — we'll notify you once approved.</div>
<?php elseif ($posStatus && $posStatus['status'] === 'approved' && pos_access()): ?>
<?php elseif (!$posStatus && $myShop): ?>
<div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
  <span><i class="fa-solid fa-cash-register me-2"></i>Run your shop like a pro — apply for <b>Premium POS</b> (billing, inventory, reports).</span>
  <a class="btn btn-sm btn-warning rounded-pill fw-bold" href="<?= url('/pos/request') ?>">Request POS</a>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="ek-card p-4 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Orders</h5>
        <a class="ek-link-all" href="<?= url('/dashboard/orders') ?>">View all <i class="fa-solid fa-angle-right"></i></a>
      </div>
      <?php foreach ($recentOrders as $o): ?>
      <a class="d-flex align-items-center gap-3 py-2 border-bottom text-decoration-none text-dark" href="<?= url('/order/' . $o['order_number']) ?>">
        <img src="<?= e(img_or($o['first_image'] ?? '')) ?>" style="width:48px;height:48px;border-radius:10px;object-fit:cover" alt="">
        <div class="flex-grow-1 min-w-0">
          <div class="small fw-semibold"><?= e($o['order_number']) ?> • <?= e($o['shop_name'] ?: 'eKamalia') ?></div>
          <div class="text-muted" style="font-size:.72rem"><?= e(time_ago($o['created_at'])) ?> • <?= (int)$o['items_count'] ?> items</div>
        </div>
        <div class="text-end"><b class="small text-success"><?= money($o['total']) ?></b><div><?= status_badge($o['status']) ?></div></div>
      </a>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><div class="ek-empty py-4"><i class="fa-solid fa-box"></i><p class="small">No orders yet — <a href="<?= url('/products') ?>">start shopping</a>!</p></div><?php endif; ?>
    </div>

    <?php if ($recentlyViewed): ?>
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3"><i class="fa-regular fa-clock me-1"></i>Recently Viewed</h5>
      <div class="d-flex gap-3 flex-wrap">
        <?php foreach ($recentlyViewed as $rv): ?>
        <a class="text-decoration-none text-dark text-center" style="width:86px" href="<?= $rv['url'] ?>">
          <img src="<?= e($rv['image']) ?>" style="width:80px;height:80px;border-radius:14px;object-fit:cover" alt="" loading="lazy">
          <div class="text-truncate small mt-1"><?= e($rv['title']) ?></div>
          <div class="text-muted" style="font-size:.66rem"><?= e($rv['type']) ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-5">
    <div class="ek-card p-4 mb-3">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-heart me-1 text-danger"></i>Wishlist</h5>
      <div class="d-flex gap-2 flex-wrap">
        <?php foreach (array_slice($wishlist, 0, 4) as $p2): $p = $p2; ?>
        <a href="<?= url('/product/' . $p['slug']) ?>" style="width:72px"><img src="<?= e(img_or($p['image'] ?? '')) ?>" style="width:70px;height:70px;border-radius:12px;object-fit:cover" alt="" loading="lazy"></a>
        <?php endforeach; ?>
        <?php if (!$wishlist): ?><p class="text-muted small mb-0">Nothing saved yet.</p><?php endif; ?>
      </div>
      <a class="btn btn-outline-success btn-sm rounded-pill mt-3" href="<?= url('/dashboard/wishlist') ?>">View wishlist (<?= $stats['wishlist'] ?>)</a>
    </div>
    <?php if (!$myShop): ?>
    <div class="ek-card p-4 mb-3 text-center" style="background:linear-gradient(135deg,#0B7A3E,#14915a);color:#fff;border:0">
      <i class="fa-solid fa-shop fs-3 mb-2"></i>
      <h5 class="fw-bold">Turn your side hustle into a shop</h5>
      <p class="small mb-3" style="opacity:.9">Free shop registration for Kamalia sellers — approval within 24h.</p>
      <a href="<?= url('/shops/create') ?>" class="btn btn-warning rounded-pill px-4 fw-bold">Create Your Shop</a>
    </div>
    <?php endif; ?>
  </div>
</div>
