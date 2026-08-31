<?php
/** Product card partial. Expects $p (product row with shop_name, image, shop_slug) */
$sale = !empty($p['sale_price']) && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price'];
$price = $sale ? (float)$p['sale_price'] : (float)$p['price'];
$disc = ($sale && (float)$p['price'] > 0) ? round(100 - ($price / (float)$p['price'] * 100)) : 0;
?>
<article class="ek-item-card reveal in">
  <a class="thumb" href="<?= url('/product/' . $p['slug']) ?>" aria-label="<?= e($p['name']) ?>">
    <img src="<?= e(img_or($p['image'] ?? '')) ?>" alt="<?= e($p['name']) ?>" loading="lazy" data-fade>
    <div class="flags">
      <?php if (!empty($p['is_featured'])): ?>
        <span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>
      <?php endif; ?>
      <?php if ($disc > 0): ?>
        <span class="ek-flag discount">-<?= $disc ?>% OFF</span>
      <?php endif; ?>
      <?php if (isset($p['stock']) && (int)$p['stock'] <= 0): ?>
        <span class="ek-flag urgent">Out of stock</span>
      <?php endif; ?>
    </div>
  </a>
  <div class="quick">
    <button class="ek-quick-btn <?= !empty($p['wishlisted']) ? 'active' : '' ?>" data-wishlist="product" data-id="<?= $p['id'] ?>" aria-label="Save to Wishlist" title="Wishlist">
      <i class="fa-solid fa-heart"></i>
    </button>
    <button class="ek-quick-btn" data-share="<?= e(url('/product/' . $p['slug'])) ?>" data-title="<?= e($p['name']) ?>" aria-label="Share product" title="Share">
      <i class="fa-solid fa-share-nodes"></i>
    </button>
  </div>
  <div class="body">
    <a class="title" href="<?= url('/product/' . $p['slug']) ?>" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></a>
    <div class="ek-price">
      <?= money($price) ?>
      <?php if ($sale): ?>
        <span class="old"><?= money($p['price']) ?></span>
      <?php endif; ?>
    </div>
    <div class="shop-row">
      <?php if (!empty($p['shop_name'])): ?>
        <a href="<?= url('/shop/' . $p['shop_slug']) ?>" class="text-truncate text-muted" style="max-width:130px">
          <i class="fa-solid fa-store me-1"></i><?= e($p['shop_name']) ?>
        </a>
        <?php if (!empty($p['shop_verified'])): ?>
          <i class="fa-solid fa-circle-check verified-tick" title="Verified Shop"></i>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (!empty($p['rating_count']) && (int)$p['rating_count'] > 0): ?>
        <span class="stars ms-auto"><i class="fa-solid fa-star"></i> <?= number_format((float)$p['rating_avg'], 1) ?></span>
      <?php endif; ?>
    </div>
    <div class="meta">
      <span class="text-truncate" style="max-width:110px"><i class="fa-solid fa-location-dot me-1"></i><?= e($p['city_name'] ?? 'Kamalia') ?></span>
      <button class="btn btn-sm btn-ek rounded-pill px-3 ms-auto d-inline-flex align-items-center gap-1" data-cart-add="<?= $p['id'] ?>" <?= (isset($p['stock']) && (int)$p['stock'] <= 0) ? 'disabled' : '' ?> title="Add to Cart">
        <i class="fa-solid fa-cart-plus"></i> <span class="d-none d-sm-inline" style="font-size:0.75rem">Add</span>
      </button>
    </div>
  </div>
</article>
