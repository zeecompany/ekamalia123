<div class="container py-4" style="max-width:900px">
  <div class="ek-card overflow-hidden mb-4">
    <div style="height:190px;background:linear-gradient(120deg,#0B7A3E33,#F0B42944);position:relative">
      <?php if ($b['cover']): ?><img src="<?= e(upload_url($b['cover'])) ?>" style="width:100%;height:100%;object-fit:cover" alt=""><?php endif; ?>
    </div>
    <div class="p-4">
      <div class="d-flex flex-wrap align-items-center gap-3" style="margin-top:-56px">
        <img src="<?= e(img_or($b['logo'], 'assets/img/biz-logo.svg')) ?>" style="width:88px;height:88px;border-radius:22px;border:4px solid #fff;object-fit:cover" alt="">
        <div class="flex-grow-1 mt-3">
          <h1 class="h4 fw-bold mb-0"><?= e($b['name']) ?>
            <?php if ($b['is_verified']): ?><i class="fa-solid fa-circle-check verified-tick" title="Verified Business"></i><?php endif; ?></h1>
          <div class="text-muted small">
            <?php if ($b['cat_name']): ?><span class="badge text-bg-light border"><?= e($b['cat_name']) ?></span><?php endif; ?>
            <span class="ms-1"><i class="fa-solid fa-location-dot"></i> <?= e(trim(($b['area'] ? $b['area'] . ', ' : '') . ($b['city_name'] ?? ''))) ?></span>
            <?php if ($b['opening_hours']): ?><span class="ms-2"><i class="fa-regular fa-clock"></i> <?= e($b['opening_hours']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if ($b['phone']): ?><a class="btn btn-ek rounded-pill px-4" href="tel:<?= e(pk_phone($b['phone'])) ?>"><i class="fa-solid fa-phone me-2"></i><?= e(t('btn.call_now')) ?></a><?php endif; ?>
        <?php if ($b['whatsapp']): ?><a class="btn rounded-pill px-4 text-white" style="background:#25D366" target="_blank" rel="noopener" href="<?= e(wa_link($b['whatsapp'], 'Assalam-o-Alaikum! I found ' . $b['name'] . ' on eKamalia.')) ?>"><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp</a><?php endif; ?>
        <?php if ($b['website']): ?><a class="btn btn-outline-secondary rounded-pill px-4" target="_blank" rel="noopener" href="<?= e($b['website']) ?>"><i class="fa-solid fa-globe me-2"></i>Website</a><?php endif; ?>
        <button class="btn btn-outline-secondary rounded-pill px-3" data-share="<?= e(url('/business/' . $b['slug'])) ?>"><i class="fa-solid fa-share-nodes"></i></button>
        <?php if ($b['address']): ?><a class="btn btn-outline-secondary rounded-pill px-3" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= e(rawurlencode($b['address'] . ' Kamalia')) ?>"><i class="fa-solid fa-diamond-turn-right me-2"></i><?= e(t('btn.directions')) ?></a><?php endif; ?>
        <button class="btn btn-outline-danger rounded-pill px-3" data-report="business" data-id="<?= $b['id'] ?>"><i class="fa-regular fa-flag"></i></button>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="ek-card p-4 mb-3">
        <h5 class="fw-bold"><?= e(t('shop.about')) ?></h5>
        <p class="mb-0" style="line-height:1.9;white-space:pre-wrap"><?= e($b['description'] ?: 'No description yet.') ?></p>
      </div>
      <?php if ($photos): ?>
      <div class="row g-2 mb-3 row-cols-3">
        <?php foreach ($photos as $ph): ?><div class="col"><img src="<?= e(upload_url($ph['image'])) ?>" class="w-100 rounded-4" style="aspect-ratio:1;object-fit:cover" alt="" loading="lazy"></div><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="col-md-5">
      <div class="ek-card p-4">
        <h5 class="fw-bold mb-3">Reviews (<?= count($reviews) ?>)</h5>
        <?php if (auth()): ?>
        <form data-review-form method="post" action="<?= url('/review') ?>" class="border rounded-4 p-3 mb-3">
          <?= csrf_field() ?>
          <input type="hidden" name="item_type" value="business"><input type="hidden" name="item_id" value="<?= $b['id'] ?>">
          <div class="mb-2"><?php for ($i = 1; $i <= 5; $i++): ?><label class="me-1" style="cursor:pointer"><input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> class="d-none"><i class="fa-solid fa-star text-warning star-pick" data-v="<?= $i ?>"></i></label><?php endfor; ?></div>
          <textarea class="form-control form-control-sm mb-2" name="body" rows="2" required placeholder="Share your experience…"></textarea>
          <button class="btn btn-ek btn-sm rounded-pill px-4">Submit</button>
        </form>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
        <div class="border-bottom py-2">
          <span class="fw-semibold small"><?= e($r['user_name']) ?></span>
          <span class="stars ms-2"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></span>
          <p class="small mb-0 mt-1"><?= e($r['body']) ?></p>
        </div>
        <?php endforeach; ?>
        <?php if (!$reviews): ?><p class="text-muted small">No reviews yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($similar): ?>
  <section class="ek-section">
    <div class="ek-section-head"><h2>Nearby Businesses</h2></div>
    <div class="row g-3"><?php foreach ($similar as $b2): ?><div class="col-md-6"><?php $b = $b2; include views_path('partials/business-card'); ?></div><?php endforeach; ?></div>
  </section>
  <?php endif; ?>
</div>
<script>document.querySelectorAll('.star-pick').forEach(s => s.addEventListener('click', () => {
  const v = +s.dataset.v; document.querySelectorAll('.star-pick').forEach((x, i) => x.style.color = i < v ? '#f5b301' : '#d9dfdb'); }));</script>
