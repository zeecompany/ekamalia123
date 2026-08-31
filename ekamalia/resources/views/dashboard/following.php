<h1 class="h4 fw-bold mb-4">Following</h1>
<h5 class="fw-bold mb-3">Shops (<?= count($shops) ?>)</h5>
<?php if ($shops): ?>
<div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-xl-3 mb-4">
  <?php foreach ($shops as $s): ?><div class="col"><?php include views_path('partials/shop-card'); ?></div><?php endforeach; ?>
</div>
<?php else: ?><p class="text-muted small">Follow your favorite shops to get notified about new products & offers.</p><?php endif; ?>
<h5 class="fw-bold mb-3">Saved Searches (<?= count($savedSearches) ?>)</h5>
<?php if ($savedSearches): ?>
<div class="ek-card p-3">
  <?php foreach ($savedSearches as $ss): $params = json_decode($ss['params'], true) ?: []; ?>
  <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
    <div><b class="small"><?= e($ss['name']) ?></b>
      <div class="text-muted" style="font-size:.72rem"><?= e(urldecode(http_build_query($params))) ?></div></div>
    <a class="btn btn-sm btn-outline-success rounded-pill" href="<?= url('/search') ?>?<?= e(http_build_query($params)) ?>">Run</a>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?><p class="text-muted small">Save a search from the ads or search page to get alerts about new matches.</p><?php endif; ?>
