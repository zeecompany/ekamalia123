<div class="container py-4">
  <div class="text-center mb-4">
    <h1 class="h3 fw-bold mb-1">eKamalia Cities</h1>
    <p class="text-muted mb-0">Serving Kamalia first — and cities across Punjab &amp; Pakistan.</p>
  </div>
  <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3">
    <?php foreach ($cities as $c): ?>
    <div class="col">
      <a class="ek-card p-3 d-flex align-items-center gap-3 text-decoration-none text-dark h-100 <?= $c['is_primary'] ? 'border-success' : '' ?>" href="<?= url('/search?city=' . e($c['slug'])) ?>">
        <span class="kpi-ic <?= $c['is_primary'] ? 'ic-gold' : 'ic-green' ?>" style="width:44px;height:44px"><i class="fa-solid fa-city"></i></span>
        <div class="flex-grow-1">
          <b><?= e($c['name']) ?></b> <?= $c['is_primary'] ? '<span class="badge text-bg-warning">Home</span>' : '' ?>
          <div class="text-muted small"><?= (int)$c['ads_count'] ?> ads • <?= (int)$c['shops_count'] ?> shops • <?= (int)$c['biz_count'] ?> businesses</div>
        </div>
        <i class="fa-solid fa-angle-right text-muted"></i>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
