<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h4 fw-bold mb-0">My Classified Ads</h1><p class="text-muted small mb-0">Your listings in the classifieds section</p></div>
  <a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>"><i class="fa-solid fa-plus me-1"></i>Post New Ad</a>
</div>
<div class="row g-3 mb-4 row-cols-3">
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-tag"></i></div><div><div class="val"><?= (int)$stats['total'] ?></div><div class="lbl">Total Ads</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-circle-check"></i></div><div><div class="val"><?= (int)$stats['active'] ?></div><div class="lbl">Active</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-regular fa-eye"></i></div><div><div class="val"><?= number_format((int)$stats['views']) ?></div><div class="lbl">Total Views</div></div></div></div>
</div>
<?php foreach ($rows as $a): ?>
<div class="ek-card p-3 mb-2 d-flex flex-wrap gap-3 align-items-center">
  <img src="<?= img_or($a['image']) ?>" alt="" style="width:74px;height:56px;object-fit:cover;border-radius:10px" loading="lazy">
  <div class="flex-grow-1">
    <a class="fw-bold text-decoration-none text-dark" href="<?= url('/ad/' . $a['slug']) ?>"><?= e($a['title']) ?></a>
    <div class="text-muted small"><?= e($a['category_name'] ?: '—') ?> • <i class="fa-regular fa-eye"></i> <?= (int)$a['views'] ?> • <?= e(fmt_date($a['created_at'])) ?></div>
  </div>
  <b><?= $a['price'] > 0 ? money($a['price']) : '—' ?></b>
  <?php if ($a['status'] === 'active'): ?><span class="badge text-bg-success">Active</span>
  <?php elseif ($a['status'] === 'pending'): ?><span class="badge text-bg-warning">Pending</span>
  <?php elseif ($a['status'] === 'sold'): ?><span class="badge text-bg-info">Sold</span>
  <?php elseif ($a['status'] === 'expired'): ?><span class="badge text-bg-secondary">Expired</span>
  <?php else: ?><span class="badge text-bg-danger"><?= e(ucfirst($a['status'])) ?></span><?php endif; ?>
  <div class="d-flex gap-1">
    <a class="btn btn-sm btn-outline-success" href="<?= url('/ads/' . $a['id'] . '/edit') ?>"><i class="fa-regular fa-pen-to-square"></i></a>
    <?php if ($a['status'] === 'active'): ?>
    <form method="post" action="<?= url('/ads/' . $a['id'] . '/action') ?>" onsubmit="return confirm('Mark this ad as SOLD?')"><?= csrf_field() ?>
      <input type="hidden" name="action" value="sold"><button class="btn btn-sm btn-outline-dark">Sold</button></form>
    <?php elseif (in_array($a['status'], ['paused', 'sold', 'expired'], true)): ?>
    <form method="post" action="<?= url('/ads/' . $a['id'] . '/action') ?>"><?= csrf_field() ?>
      <input type="hidden" name="action" value="renew"><button class="btn btn-sm btn-outline-success">Renew</button></form>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$rows): ?><div class="ek-empty"><i class="fa-solid fa-tag"></i><h5>No classified ads yet</h5><p>Post your first ad — it's free for every Kamalia resident.</p><a class="btn btn-ek rounded-pill px-4" href="<?= url('/ads/create') ?>">Post an Ad</a></div><?php endif; ?>
