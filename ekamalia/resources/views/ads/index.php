<?php $isCatPage = !empty($cat); ?>
<div class="container py-4" id="results">
  <nav aria-label="breadcrumb" class="ek-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
      <li class="breadcrumb-item active"><?= e($cat['name'] ?? 'Classified Ads') ?></li>
    </ol>
  </nav>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h4 fw-bold mb-1"><?= e($cat['name'] ?? 'Classified Ads in Kamalia') ?></h1>
      <p class="text-muted small mb-0">Buy and sell verified items directly from locals in Kamalia &amp; nearby areas</p>
    </div>
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>">
      <i class="fa-solid fa-plus me-1"></i> <?= e(t('btn.post_ad')) ?>
    </a>
  </div>

  <?php if (!empty($subcats)): ?>
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="chip <?= !input('sub') ? 'active' : '' ?>" href="?<?= e(http_build_query(array_diff_key($_GET, ['sub' => 1]))) ?>">All</a>
    <?php foreach ($subcats as $sc): ?>
      <a class="chip <?= input('sub') == $sc['id'] ? 'active' : '' ?>" href="?<?= e(http_build_query(array_merge($_GET, ['sub' => $sc['id']]))) ?>">
        <?= e($sc['name']) ?> <span class="opacity-75 ms-1">(<?= (int)$sc['cnt'] ?>)</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <aside class="col-lg-3">
      <form class="ek-card p-4 filter-card" method="get">
        <?php if ($isCatPage): ?><input type="hidden" name="keep" value="1"><?php endif; ?>
        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <i class="fa-solid fa-filter text-success"></i> Filter Ads
        </h6>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Keyword</label>
          <input type="text" class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="e.g. iPhone, Civic, Sofa">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">City / Area</label>
          <select class="form-select" name="city">
            <option value="">All Areas</option>
            <?php foreach (qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order') as $c): ?>
              <option value="<?= e($c['slug']) ?>" <?= ($_GET['city'] ?? '') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Min Rs</label>
            <input type="number" class="form-control" name="min_price" value="<?= e($_GET['min_price'] ?? '') ?>" min="0" placeholder="0">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Max Rs</label>
            <input type="number" class="form-control" name="max_price" value="<?= e($_GET['max_price'] ?? '') ?>" min="0" placeholder="Any">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Condition</label>
          <select class="form-select" name="condition">
            <option value="">Any Condition</option>
            <?php foreach (['new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($_GET['condition'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="featured" id="chkFeatured" value="1" <?= !empty($_GET['featured']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="chkFeatured">Featured Ads Only</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="negotiable" id="chkNegotiable" value="1" <?= !empty($_GET['negotiable']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="chkNegotiable">Negotiable Price</label>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="delivery" id="chkDelivery" value="1" <?= !empty($_GET['delivery']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="chkDelivery">Delivery Available</label>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Sort By</label>
          <select class="form-select" name="sort">
            <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest first</option>
            <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="popular" <?= ($_GET['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most viewed</option>
          </select>
        </div>
        <button class="btn btn-ek w-100 rounded-pill py-2">Apply Filters</button>
      </form>

      <?php if (auth()): ?>
      <form class="ek-card p-3 mt-3 text-center" method="post" action="<?= url('/search/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="name" value="<?= e($cat['name'] ?? 'Ads search') ?>">
        <button class="btn btn-outline-success btn-sm w-100 rounded-pill"><i class="fa-regular fa-bell me-1"></i> Save search alerts</button>
      </form>
      <?php endif; ?>
    </aside>

    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small fw-semibold"><?= number_format($total) ?> ads found</span>
      </div>

      <?php if ($ads): ?>
      <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-3">
        <?php foreach ($ads as $a): ?>
          <div class="col"><?php include views_path('partials/ad-card'); ?></div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4"><?= paginate($total, $perPage) ?></div>
      <?php else: ?>
      <div class="ek-empty">
        <div class="empty-icon"><i class="fa-solid fa-tag"></i></div>
        <h5>No listings found</h5>
        <p>There are currently no ads matching your filters. Be the first to post an ad in this category!</p>
        <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>">
          <i class="fa-solid fa-plus me-1"></i> Post the First Ad
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
