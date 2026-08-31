<div class="container py-4">
  <div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Search eKamalia Marketplace</h1>
    <p class="text-muted small">Search classified ads, online stores, products, services and local businesses</p>
  </div>

  <form class="row g-2 mb-4" method="get" role="search">
    <div class="col-md-5">
      <input class="form-control form-control-lg" name="q" value="<?= e($q) ?>" placeholder="Search anything in Kamalia…">
    </div>
    <div class="col-md-3">
      <select class="form-select form-select-lg" name="type">
        <?php foreach (['all' => 'All Categories & Listings', 'products' => 'Shop Products', 'ads' => 'Classified Ads', 'shops' => 'Online Shops', 'businesses' => 'Business Directory'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select form-select-lg" name="city">
        <option value="">All Areas</option>
        <?php foreach ($cities as $c): ?>
          <option value="<?= e($c['slug']) ?>" <?= ($_GET['city'] ?? '') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-ek btn-lg w-100 rounded-pill d-flex align-items-center justify-content-center gap-2">
        <i class="fa-solid fa-magnifying-glass"></i> <span>Search</span>
      </button>
    </div>
  </form>

  <?php if ($q !== '' || input('city') || input('cat')): ?>
    <?php if ($total === 0): ?>
      <div class="ek-empty">
        <div class="empty-icon"><i class="fa-regular fa-face-frown"></i></div>
        <h5>No results found for "<?= e($q) ?>"</h5>
        <p class="small">Try checking your spelling, using broader search keywords, or explore our <a href="<?= url('/categories') ?>" class="text-success fw-semibold">All Categories</a> directory.</p>
      </div>
    <?php else: ?>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="text-muted small fw-semibold"><?= number_format($total) ?> matching results found</span>
      </div>

      <?php if (!empty($products)): ?>
        <div class="mb-5">
          <div class="ek-section-head mb-3">
            <div class="head-info">
              <span class="section-pill"><i class="fa-solid fa-bag-shopping"></i> Shopping</span>
              <h5 class="fw-bold mb-0">Shop Products</h5>
            </div>
          </div>
          <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-4">
            <?php foreach ($products as $p): ?>
              <div class="col"><?php include views_path('partials/product-card'); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($ads)): ?>
        <div class="mb-5">
          <div class="ek-section-head mb-3">
            <div class="head-info">
              <span class="section-pill"><i class="fa-solid fa-tag"></i> Classifieds</span>
              <h5 class="fw-bold mb-0">Classified Ads</h5>
            </div>
          </div>
          <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-4">
            <?php foreach ($ads as $a): ?>
              <div class="col"><?php include views_path('partials/ad-card'); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($shops)): ?>
        <div class="mb-5">
          <div class="ek-section-head mb-3">
            <div class="head-info">
              <span class="section-pill"><i class="fa-solid fa-store"></i> Stores</span>
              <h5 class="fw-bold mb-0">Verified Stores</h5>
            </div>
          </div>
          <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">
            <?php foreach ($shops as $s): ?>
              <div class="col"><?php include views_path('partials/shop-card'); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($businesses)): ?>
        <div class="mb-5">
          <div class="ek-section-head mb-3">
            <div class="head-info">
              <span class="section-pill"><i class="fa-solid fa-briefcase"></i> Directory</span>
              <h5 class="fw-bold mb-0">Local Businesses</h5>
            </div>
          </div>
          <div class="row g-3 row-cols-1 row-cols-md-2">
            <?php foreach ($businesses as $b): ?>
              <div class="col"><?php include views_path('partials/business-card'); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (auth()): ?>
      <form class="mt-4 text-center" method="post" action="<?= url('/search/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="name" value="<?= e($q ?: 'My search') ?>">
        <button class="btn btn-outline-success rounded-pill px-4 btn-sm fw-semibold">
          <i class="fa-regular fa-bell me-1"></i> Save search alert
        </button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  <?php else: ?>
    <div class="ek-section-head mb-3">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-layer-group"></i> Explore</span>
        <h5 class="fw-bold mb-0">Popular Categories</h5>
      </div>
    </div>
    <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-5">
      <?php foreach (array_slice($cats, 0, 10) as $c): ?>
        <div class="col">
          <a class="ek-cat-card" href="<?= url('/category/' . $c['slug']) ?>">
            <div class="ic"><i class="fa-solid <?= e($c['icon'] ?: 'fa-tag') ?>"></i></div>
            <div class="nm"><?= e($c['name']) ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
