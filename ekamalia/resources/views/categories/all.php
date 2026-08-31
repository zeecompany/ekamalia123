<div class="container py-4">
  <nav aria-label="breadcrumb" class="ek-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
      <li class="breadcrumb-item active">All Categories</li>
    </ol>
  </nav>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">Browse All Categories</h1>
      <p class="text-muted mb-0">Discover products, vehicles, properties, services and jobs across Kamalia &amp; Toba Tek Singh.</p>
    </div>
    <a href="<?= url('/ads/create') ?>" class="btn btn-ek rounded-pill px-4">
      <i class="fa-solid fa-plus me-1"></i> Post Free Ad
    </a>
  </div>

  <div class="row g-4">
    <?php foreach ($parents as $c): ?>
    <div class="col-md-6 col-lg-4">
      <div class="ek-card p-4 h-100 d-flex flex-column">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="ic" style="width:52px;height:52px;border-radius:16px;background:var(--ek-primary-light);color:var(--ek-primary);font-size:1.4rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="<?= e($c['icon'] ?: 'fa-solid fa-tag') ?>"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <a class="fw-bold text-dark d-block text-truncate text-decoration-none" href="<?= url('/category/' . $c['slug']) ?>" style="font-size:1.05rem">
              <?= e($c['name']) ?>
            </a>
            <div class="text-muted" style="font-size:0.78rem">
              <span class="text-success fw-semibold"><?= (int)$c['product_count'] ?> products</span> • <?= (int)$c['ad_count'] ?> ads
            </div>
          </div>
        </div>

        <?php if (!empty($c['children'])): ?>
        <div class="d-flex flex-wrap gap-1 mt-auto pt-3 border-top">
          <?php foreach (array_slice($c['children'], 0, 8) as $ch): ?>
            <a class="chip" href="<?= url('/category/' . $ch['slug']) ?>">
              <?= e($ch['name']) ?>
              <span class="opacity-75 ms-1" style="font-size:0.72rem">(<?= (int)$ch['product_count'] + (int)$ch['ad_count'] ?>)</span>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
