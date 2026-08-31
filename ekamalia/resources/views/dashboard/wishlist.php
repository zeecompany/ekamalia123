<h1 class="h4 fw-bold mb-4"><i class="fa-solid fa-heart text-danger me-2"></i><?= e(t('nav.wishlist')) ?></h1>
<?php if ($products): ?>
<h5 class="fw-bold mb-3">Products</h5>
<div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
  <?php foreach ($products as $p): ?><div class="col"><?php include views_path('partials/product-card'); ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($ads): ?>
<h5 class="fw-bold mb-3">Saved Ads</h5>
<div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
  <?php foreach ($ads as $a): ?><div class="col"><?php include views_path('partials/ad-card'); ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!$products && !$ads): ?>
<div class="ek-empty"><i class="fa-regular fa-heart"></i><h5>Your wishlist is empty</h5><p class="small">Tap the ♥ on any product or ad to save it here.</p><a class="btn btn-ek rounded-pill px-4" href="/products">Explore Products</a></div>
<?php endif; ?>
