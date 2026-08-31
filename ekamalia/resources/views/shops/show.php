<?php $me = auth(); $isOwner = !empty($isOwner); ?>
<div class="container py-4">
  <div class="ek-card overflow-hidden mb-4">
    <div style="height:210px;background:linear-gradient(120deg,#0B7A3E33,#F0B42944);position:relative">
      <?php if ($shop['cover']): ?><img src="<?= e(upload_url($shop['cover'])) ?>" style="width:100%;height:100%;object-fit:cover" alt=""><?php endif; ?>
    </div>
    <div class="px-3 px-md-4 pb-4">
      <div class="d-flex flex-wrap align-items-end gap-3" style="margin-top:-44px">
        <img src="<?= e(img_or($shop['logo'], 'assets/img/shop-logo.svg')) ?>" style="width:96px;height:96px;border-radius:24px;border:4px solid #fff;object-fit:cover;box-shadow:var(--ek-shadow)" alt="">
        <div class="flex-grow-1 mt-3">
          <h1 class="h4 fw-bold mb-0"><?= e($shop['name']) ?>
            <?php if ($shop['is_verified']): ?><i class="fa-solid fa-circle-check verified-tick" title="<?= e(t('shop.verified')) ?>"></i><?php endif; ?>
            <?php if ($shop['is_featured']): ?><span class="badge text-bg-warning">Featured</span><?php endif; ?>
          </h1>
          <div class="text-muted small"><?= e(t('shop.owner')) ?>: <?= e($shop['owner_name']) ?> • <i class="fa-solid fa-location-dot"></i> <?= e(trim(($shop['area'] ? $shop['area'] . ', ' : '') . ($shop['city_name'] ?? ''))) ?></div>
        </div>
        <div class="d-flex gap-2 mt-2">
          <button class="btn btn-ek rounded-pill px-4 <?= $following ? 'btn-outline-success' : '' ?>" data-follow="<?= $shop['id'] ?>"><?= $following ? '<i class="fa-solid fa-check me-1"></i>Following' : '<i class="fa-solid fa-plus me-1"></i>Follow' ?></button>
          <button class="btn btn-outline-success rounded-pill px-3" data-chat-start="shop" data-id="<?= $shop['id'] ?>"><i class="fa-regular fa-comment-dots me-1"></i><?= e(t('btn.chat')) ?></button>
          <button class="btn btn-outline-secondary rounded-pill px-3" data-share="<?= e(url('/shop/' . $shop['slug'])) ?>"><i class="fa-solid fa-share-nodes"></i></button>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-4 mt-3 small text-muted">
        <span><b class="text-dark"><?= number_format((int)$total) ?></b> <?= e(t('shop.products')) ?></span>
        <span><i class="fa-solid fa-star text-warning"></i> <b class="text-dark"><?= number_format((float)$shop['rating_avg'], 1) ?></b> (<?= (int)$shop['rating_count'] ?>)</span>
        <span><b class="text-dark"><?= number_format((int)$shop['followers_count']) ?></b> Followers</span>
        <?php if ($shop['business_hours']): ?><span><i class="fa-regular fa-clock me-1"></i><?= e($shop['business_hours']) ?></span><?php endif; ?>
        <?php if ($shop['delivery_available']): ?><span class="text-success"><i class="fa-solid fa-truck me-1"></i>Delivery available (<?= money($shop['delivery_fee']) ?>)</span><?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($coupons): ?>
  <div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach ($coupons as $c): ?>
      <div class="chip" style="border-style:dashed"><i class="fa-solid fa-ticket text-danger"></i>
        <b><?= e($c['code']) ?></b> —
        <?= $c['type'] === 'percent' ? (int)$c['value'] . '% off' : ($c['type'] === 'fixed' ? money($c['value']) . ' off' : 'Free delivery') ?>
        <?php if ($c['min_order'] > 0): ?><span class="opacity-50">on <?= money($c['min_order']) ?>+</span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <aside class="col-lg-3 order-lg-2">
      <div class="ek-card p-3 filter-card">
        <h6 class="fw-bold mb-3">Shop Categories</h6>
        <div class="d-flex flex-column gap-1">
          <a class="px-2 py-1 rounded-3 small <?= !input('cat') ? 'bg-success-subtle text-success fw-semibold' : 'text-dark' ?>" href="<?= url('/shop/' . $shop['slug']) ?>">All products</a>
          <?php foreach ($shopCats as $sc): ?>
            <a class="px-2 py-1 rounded-3 small <?= input('cat') == $sc['id'] ? 'bg-success-subtle text-success fw-semibold' : 'text-dark' ?>" href="?cat=<?= $sc['id'] ?>"><?= e($sc['name']) ?></a>
          <?php endforeach; ?>
        </div>
        <hr>
        <h6 class="fw-bold mb-2"><?= e(t('shop.about')) ?></h6>
        <p class="small text-muted mb-2"><?= e($shop['description']) ?></p>
        <?php if ($shop['phone']): ?><div class="small mb-1"><i class="fa-solid fa-phone me-2 text-success"></i><?= e(pk_phone($shop['phone'])) ?></div><?php endif; ?>
        <?php if ($shop['whatsapp']): ?><a class="small d-block text-success" target="_blank" rel="noopener" href="<?= e(wa_link($shop['whatsapp'], 'Hello ' . $shop['name'])) ?>"><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp</a><?php endif; ?>
        <button class="btn btn-link btn-sm text-danger ps-0 mt-2" data-report="shop" data-id="<?= $shop['id'] ?>">Report this shop</button>
      </div>
    </aside>
    <div class="col-lg-9 order-lg-1">
      <form class="d-flex gap-2 mb-3" method="get">
        <input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search in this shop…">
        <select class="form-select" style="max-width:170px" name="sort">
          <option value="newest">Newest</option><option value="price_low">Price ↑</option><option value="price_high">Price ↓</option><option value="popular">Popular</option>
        </select>
        <button class="btn btn-ek rounded-pill px-4">Go</button>
      </form>
      <?php if ($products): ?>
      <div class="row g-3 row-cols-2 row-cols-md-3">
        <?php foreach ($products as $p): ?><div class="col"><?php include views_path('partials/product-card'); ?></div><?php endforeach; ?>
      </div>
      <div class="mt-4"><?= paginate($total, $perPage) ?></div>
      <?php else: ?><div class="ek-empty"><i class="fa-solid fa-box-open"></i><h5>No products yet</h5><p class="small">This shop hasn't listed products in this filter.</p></div><?php endif; ?>

      <div class="ek-card p-4 mt-4">
        <h5 class="fw-bold mb-3"><?= e(t('shop.reviews')) ?> (<?= count($reviews) ?>)</h5>
        <?php if ($me && !$isOwner && !q1('SELECT id FROM reviews WHERE user_id=? AND item_type="shop" AND item_id=?', [$me['id'], $shop['id']])): ?>
        <form data-review-form method="post" action="<?= url('/review') ?>" class="border rounded-4 p-3 mb-3">
          <?= csrf_field() ?>
          <input type="hidden" name="item_type" value="shop"><input type="hidden" name="item_id" value="<?= $shop['id'] ?>">
          <div class="mb-2"><?php for ($i = 1; $i <= 5; $i++): ?><label class="me-1" style="cursor:pointer"><input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> class="d-none"><i class="fa-solid fa-star text-warning star-pick" data-v="<?= $i ?>"></i></label><?php endfor; ?></div>
          <textarea class="form-control form-control-sm mb-2" name="body" rows="2" required placeholder="Share your experience with this shop…"></textarea>
          <button class="btn btn-ek btn-sm rounded-pill px-4">Submit</button>
        </form>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
        <div class="border-bottom py-2">
          <span class="fw-semibold small"><?= e($r['user_name']) ?></span>
          <span class="stars ms-2"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></span>
          <p class="small mb-1 mt-1"><?= e($r['body']) ?></p>
          <?php if ($r['seller_reply']): ?><div class="bg-light rounded-3 p-2 small"><b class="text-success">Reply:</b> <?= e($r['seller_reply']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (!$reviews): ?><p class="text-muted small mb-0">No reviews yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>window.EK_PAGE_TYPE = 'shop'; window.EK_PAGE_ID = <?= (int)$shop['id'] ?>;
document.querySelectorAll('.star-pick').forEach(s => s.addEventListener('click', () => {
  const v = +s.dataset.v;
  document.querySelectorAll('.star-pick').forEach((x, i) => x.style.color = i < v ? '#f5b301' : '#d9dfdb');
}));
</script>

