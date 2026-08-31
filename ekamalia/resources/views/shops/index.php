<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-store me-2 text-success"></i>Verified Online Shops in Kamalia</h1>
      <p class="text-muted small mb-0">Browse trusted local shops and order directly with home delivery in Kamalia</p>
    </div>
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/shops/create') ?>">
      <i class="fa-solid fa-plus me-1"></i> Create Your Shop — Free
    </a>
  </div>

  <form class="row g-2 mb-4" method="get">
    <div class="col-md-5">
      <input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search shop name or category…">
    </div>
    <div class="col-md-4">
      <select class="form-select" name="city">
        <option value="">All Areas</option>
        <?php foreach (qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order') as $c): ?>
          <option value="<?= e($c['slug']) ?>" <?= ($_GET['city'] ?? '') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-ek w-100 rounded-pill"><i class="fa-solid fa-magnifying-glass me-1"></i> Search Shops</button>
    </div>
  </form>

  <?php if (!empty($shops)): ?>
  <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">
    <?php foreach ($shops as $s): ?>
      <div class="col"><?php include views_path('partials/shop-card'); ?></div>
    <?php endforeach; ?>
  </div>
  <div class="mt-4"><?= paginate($total, $perPage) ?></div>
  <?php else: ?>
  <div class="ek-empty">
    <div class="empty-icon"><i class="fa-solid fa-store"></i></div>
    <h5>No shops found</h5>
    <p>Be the first business owner to open a verified online store on eKamalia!</p>
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/shops/create') ?>">
      <i class="fa-solid fa-plus me-1"></i> Create Your Shop Now
    </a>
  </div>
  <?php endif; ?>
</div>
