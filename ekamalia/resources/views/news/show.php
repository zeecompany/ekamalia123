<div class="container py-4" style="max-width:820px">
  <nav aria-label="breadcrumb" class="ek-breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li><li class="breadcrumb-item"><a href="<?= url('/news') ?>">News</a></li></ol></nav>
  <article class="ek-card p-4">
    <h1 class="h3 fw-bold"><?= e($n['title']) ?></h1>
    <div class="text-muted small mb-3"><i class="fa-regular fa-clock me-1"></i><?= e(fmt_date($n['published_at'])) ?> <?= $n['author'] ? '• by ' . e($n['author']) : '' ?> • <?= (int)$n['views'] ?> views</div>
    <?php if ($n['image']): ?><img src="<?= e(upload_url($n['image'])) ?>" class="w-100 rounded-4 mb-3" style="max-height:380px;object-fit:cover" alt=""><?php endif; ?>
    <div style="line-height:2"><?= $n['content'] ?></div>
    <hr>
    <button class="btn btn-outline-success btn-sm rounded-pill" data-share="<?= e(url('/news/' . $n['slug'])) ?>"><i class="fa-solid fa-share-nodes me-1"></i>Share</button>
    <a class="btn btn-outline-secondary btn-sm rounded-pill" target="_blank" rel="noopener" href="https://wa.me/?text=<?= e(rawurlencode($n['title'] . ' — ' . url('/news/' . $n['slug']))) ?>"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
  </article>
  <?php if ($related): ?>
  <h5 class="fw-bold mt-4 mb-3">More Updates</h5>
  <div class="row g-3 row-cols-1 row-cols-md-2">
    <?php foreach ($related as $r): ?>
    <a class="ek-card p-3 d-flex gap-3 align-items-center" href="<?= url('/news/' . $r['slug']) ?>">
      <img src="<?= e($r['image'] ? upload_url($r['image']) : asset('img/news-default.svg')) ?>" style="width:70px;height:70px;border-radius:12px;object-fit:cover" alt="" loading="lazy">
      <div class="fw-semibold small"><?= e($r['title']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
