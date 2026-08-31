<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-map-location-dot me-2 text-success"></i>Kamalia Business Directory</h1>
      <p class="text-muted small mb-0">Doctors, schools, restaurants, workshops, artisans &amp; commercial services in Kamalia</p>
    </div>
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/businesses/create') ?>">
      <i class="fa-solid fa-plus me-1"></i> List Your Business Free
    </a>
  </div>

  <?php if (!empty($cats)): ?>
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="chip <?= !input('cat') ? 'active' : '' ?>" href="?<?= e(http_build_query(array_diff_key($_GET, ['cat' => 1]))) ?>">All Businesses</a>
    <?php foreach ($cats as $c): ?>
      <a class="chip <?= input('cat') == $c['id'] ? 'active' : '' ?>" href="?<?= e(http_build_query(array_merge($_GET, ['cat' => $c['id']]))) ?>">
        <i class="fa-solid fa-briefcase me-1"></i><?= e($c['name']) ?> <span class="opacity-75 ms-1">(<?= (int)$c['cnt'] ?>)</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form class="row g-2 mb-4" method="get">
    <input type="hidden" name="cat" value="<?= e((string)input('cat')) ?>">
    <div class="col-md-5">
      <input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search business name, category or service…">
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
      <button class="btn btn-ek w-100 rounded-pill"><i class="fa-solid fa-magnifying-glass me-1"></i> Search Directory</button>
    </div>
  </form>

  <?php if (!empty($businesses)): ?>
  <div class="row g-3 row-cols-1 row-cols-md-2">
    <?php foreach ($businesses as $b): ?>
      <div class="col"><?php include views_path('partials/business-card'); ?></div>
    <?php endforeach; ?>
  </div>
  <div class="mt-4"><?= paginate($total, $perPage) ?></div>
  <?php else: ?>
  <div class="ek-empty">
    <div class="empty-icon"><i class="fa-solid fa-map-location-dot"></i></div>
    <h5>No businesses listed in this category</h5>
    <p>Be the first to list your clinic, school, shop, or commercial service in Kamalia!</p>
    <a class="btn btn-ek rounded-pill px-4" href="<?= url('/businesses/create') ?>">
      <i class="fa-solid fa-plus me-1"></i> Add Your Business Now
    </a>
  </div>
  <?php endif; ?>
</div>
