<div class="container py-4">
  <h1 class="h4 fw-bold mb-4"><i class="fa-solid fa-newspaper me-2 text-success"></i>Kamalia News &amp; Local Updates</h1>
  <div class="row g-4">
    <?php foreach ($news as $n): ?>
    <div class="col-md-6 col-lg-4">
      <a class="ek-card d-block h-100 overflow-hidden reveal" href="<?= url('/news/' . $n['slug']) ?>">
        <img src="<?= e($n['image'] ? upload_url($n['image']) : asset('img/news-default.svg')) ?>" class="w-100" style="height:180px;object-fit:cover" alt="" loading="lazy">
        <div class="p-3">
          <div class="text-muted" style="font-size:.7rem"><i class="fa-regular fa-clock me-1"></i><?= e(fmt_date($n['published_at'])) ?> • <?= (int)$n['views'] ?> views</div>
          <h5 class="fw-bold mt-1" style="font-size:1rem"><?= e($n['title']) ?></h5>
          <p class="text-muted small mb-0"><?= e(mb_substr(strip_tags((string)$n['excerpt']), 0, 120)) ?>…</p>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
    <?php if (!$news): ?><div class="ek-empty"><i class="fa-solid fa-newspaper"></i><h5>No news yet</h5></div><?php endif; ?>
  </div>
</div>
