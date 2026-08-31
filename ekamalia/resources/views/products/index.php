<div class="container py-4" id="results">
  <nav aria-label="breadcrumb" class="ek-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
      <li class="breadcrumb-item active"><?= e($dealsOnly ? 'Deals & Offers' : ($cat['name'] ?? 'Products')) ?></li>
    </ol>
  </nav>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h4 fw-bold mb-1"><?= $dealsOnly ? '🔥 Deals & Special Offers' : e($cat['name'] ?? 'Product Marketplace') ?></h1>
      <p class="text-muted small mb-0">Shop directly from trusted local retailers and brand stores in Kamalia</p>
    </div>
    <a class="btn btn-outline-secondary rounded-pill btn-sm px-3" href="<?= url('/compare') ?>">
      <i class="fa-solid fa-scale-balanced me-1 text-success"></i> Compare (<?= count($_SESSION['compare'] ?? []) ?>)
    </a>
  </div>

  <div class="row g-4">
    <aside class="col-lg-3">
      <form class="ek-card p-4 filter-card" method="get">
        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <i class="fa-solid fa-filter text-success"></i> Filter Products
        </h6>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Keyword</label>
          <input type="text" class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search product name…">
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
        <?php if (!empty($brands)): ?>
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Brand</label>
          <select class="form-select" name="brand">
            <option value="">All Brands</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?= $b['id'] ?>" <?= ($_GET['brand'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
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
          <label class="form-label small fw-semibold text-muted">Sort</label>
          <select class="form-select" name="sort">
            <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="popular" <?= ($_GET['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
            <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Best Rated</option>
          </select>
        </div>
        <button class="btn btn-ek w-100 rounded-pill py-2">Apply Filters</button>
      </form>
    </aside>

    <div class="col-lg-9">
      <?php if (!empty($subcats)): ?>
      <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($subcats as $sc): ?>
          <a class="chip" href="?<?= e(http_build_query(array_merge($_GET, ['cat' => $sc['id']]))) ?>">
            <?= e($sc['name']) ?> <span class="opacity-75 ms-1">(<?= (int)$sc['cnt'] ?>)</span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small fw-semibold"><?= number_format($total) ?> products found</span>
      </div>

      <?php if ($products): ?>
      <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-3">
        <?php foreach ($products as $p): ?>
          <div class="col"><?php include views_path('partials/product-card'); ?></div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4"><?= paginate($total, $perPage) ?></div>
      <?php else: ?>
      <div class="ek-empty">
        <div class="empty-icon"><i class="fa-solid fa-box-open"></i></div>
        <h5>No products found</h5>
        <p>No products match your current search criteria. Check back soon or explore other categories!</p>
        <a class="btn btn-ek rounded-pill px-4" href="<?= url('/products') ?>">View All Products</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
