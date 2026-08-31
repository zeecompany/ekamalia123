<?php /** Shop card. Expects $s */ ?>
<article class="ek-shop-card reveal in">
  <div class="cover">
    <img src="<?= e(img_or($s['cover'] ?? '', 'assets/img/shop-cover.svg')) ?>" alt="" loading="lazy" data-fade>
  </div>
  <img class="logo" src="<?= e(img_or($s['logo'] ?? '', 'assets/img/shop-logo.svg')) ?>" alt="<?= e($s['name']) ?>" loading="lazy">
  <div class="body">
    <a class="fw-bold text-truncate d-block text-dark mb-1" href="<?= url('/shop/' . $s['slug']) ?>" style="font-size:0.98rem">
      <?= e($s['name']) ?>
      <?php if (!empty($s['is_verified'])): ?>
        <i class="fa-solid fa-circle-check verified-tick" title="Verified Store"></i>
      <?php endif; ?>
    </a>
    <div class="text-muted small mb-2 text-truncate">
      <i class="fa-solid fa-user me-1"></i><?= e(t('shop.owner')) ?>: <?= e($s['owner_name'] ?: 'Local Seller') ?>
    </div>
    <div class="stats">
      <span><b><?= number_format((int)qv('SELECT COUNT(*) FROM products WHERE shop_id=? AND status="published"', [$s['id']])) ?></b> Prods</span>
      <span><i class="fa-solid fa-star text-warning"></i> <b><?= number_format((float)($s['rating_avg'] ?? 0), 1) ?></b></span>
      <span><b><?= number_format((int)($s['followers_count'] ?? 0)) ?></b> Followers</span>
    </div>
    <div class="d-flex gap-2 justify-content-center">
      <a class="btn btn-ek btn-sm rounded-pill px-3 flex-fill" href="<?= url('/shop/' . $s['slug']) ?>">
        <?= e(t('btn.visit_shop')) ?>
      </a>
      <button class="btn btn-sm rounded-pill px-3 flex-fill <?= !empty($s['following']) ? 'btn-outline-success' : 'btn-outline-dark' ?>" data-follow="<?= $s['id'] ?>">
        <?= !empty($s['following']) ? '<i class="fa-solid fa-check me-1"></i>Following' : '<i class="fa-solid fa-plus me-1"></i>Follow' ?>
      </button>
    </div>
  </div>
</article>
