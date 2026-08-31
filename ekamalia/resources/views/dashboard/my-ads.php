<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">My Classified Ads (<?= count($ads) ?>)</h1>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>"><i class="fa-solid fa-plus me-1"></i>Post New Ad</a>
</div>
<?php if ($ads): ?>
<div class="row g-3">
  <?php foreach ($ads as $a): ?>
  <div class="col-md-6 col-xl-4">
    <div class="ek-card overflow-hidden h-100 d-flex flex-column">
      <a href="<?= url('/ad/' . $a['slug']) ?>" class="position-relative d-block" style="height:170px;background:#f2f6f3">
        <img src="<?= e(img_or($a['image'])) ?>" style="width:100%;height:100%;object-fit:cover" alt="" loading="lazy">
        <?= $a['is_featured'] ? '<span class="ek-flag featured position-absolute m-2"><i class="fa-solid fa-star"></i> Featured</span>' : '' ?>
        <?= $a['is_urgent'] ? '<span class="ek-flag urgent position-absolute m-2" style="inset-inline-end:8px"><i class="fa-solid fa-fire"></i> Urgent</span>' : '' ?>
      </a>
      <div class="p-3 flex-grow-1 d-flex flex-column">
        <div class="d-flex justify-content-between gap-2 mb-1"><?= status_badge($a['status']) ?><?= $a['status_note'] ? '<i class="fa-solid fa-circle-info text-warning" title="' . e($a['status_note']) . '"></i>' : '' ?></div>
        <a class="fw-semibold text-decoration-none text-dark mb-1 d-block text-truncate" href="<?= url('/ad/' . $a['slug']) ?>"><?= e($a['title']) ?></a>
        <div class="text-success fw-bold mb-1"><?= $a['price'] > 0 ? money($a['price']) : 'Contact for price' ?></div>
        <div class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= e($a['city_name'] ?? '') ?> • <i class="fa-regular fa-eye me-1"></i><?= (int)$a['views'] ?> • <?= e(time_ago($a['created_at'])) ?></div>
        <div class="d-flex gap-1 flex-wrap mt-auto">
          <a class="btn btn-sm btn-ek-sm" href="<?= url('/ads/' . $a['id'] . '/edit') ?>"><i class="fa-solid fa-pen"></i> Edit</a>
          <form method="post" action="<?= url('/dashboard/my-ads/' . $a['id'] . '/action') ?>" class="d-inline"><?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $a['status'] === 'active' ? 'pause' : 'resume' ?>">
            <button class="btn btn-sm btn-outline-secondary"><?= $a['status'] === 'active' ? 'Pause' : 'Resume' ?></button>
          </form>
          <?php if ($a['status'] === 'active'): ?>
          <form method="post" action="<?= url('/dashboard/my-ads/' . $a['id'] . '/action') ?>" class="d-inline"><?= csrf_field() ?>
            <input type="hidden" name="action" value="sold"><button class="btn btn-sm btn-outline-success">Mark Sold</button>
          </form>
          <?php endif; ?>
          <form method="post" action="<?= url('/dashboard/my-ads/' . $a['id'] . '/action') ?>" class="d-inline" onsubmit="return confirm('Delete this ad?')"><?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="ek-empty"><i class="fa-solid fa-tag"></i><h5>No ads yet</h5><p class="text-muted small">Post your first ad free — it takes less than a minute.</p>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>">Post Free Ad</a></div>
<?php endif; ?>
