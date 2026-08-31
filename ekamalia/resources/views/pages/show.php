<div class="container py-5" style="max-width:860px">
  <div class="ek-card p-4 p-md-5">
    <?php if ($page['featured_image']): ?><img src="<?= e(upload_url($page['featured_image'])) ?>" class="w-100 rounded-4 mb-4" style="max-height:320px;object-fit:cover" alt=""><?php endif; ?>
    <h1 class="h3 fw-bold mb-3"><?= e($page['title']) ?></h1>
    <div class="page-content" style="line-height:2"><?= $page['content'] ?></div>
  </div>
</div>
