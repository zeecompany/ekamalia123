<?php $me = auth(); $isOwner = $me && (int)$me['id'] === (int)$ad['user_id']; ?>
<div class="container py-4">
  <nav aria-label="breadcrumb" class="ek-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= url('/ads') ?>">Ads</a></li>
      <?php if ($cat): ?><li class="breadcrumb-item"><a href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a></li><?php endif; ?>
      <li class="breadcrumb-item active text-truncate" style="max-width:220px"><?= e($ad['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="gallery-main mb-2">
        <img src="<?= e($images ? upload_url($images[0]['image']) : asset('img/placeholder.svg')) ?>" alt="<?= e($ad['title']) ?>" id="galleryMain">
      </div>
      <?php if (count($images) > 1): ?>
      <div class="row g-2 row-cols-4 row-cols-md-6 gallery-thumbs mb-3">
        <?php foreach ($images as $i => $im): ?>
          <div class="col"><img src="<?= e(upload_url($im['image'])) ?>" data-full="<?= e(upload_url($im['image'])) ?>" class="<?= $i === 0 ? 'active' : '' ?>" alt="Photo <?= $i + 1 ?>" loading="lazy"></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="ek-card p-4 mb-3">
        <div class="d-flex flex-wrap gap-2 mb-2">
          <?php if ($ad['is_featured']): ?><span class="ek-flag featured"><i class="fa-solid fa-star"></i> FEATURED</span><?php endif; ?>
          <?php if ($ad['is_urgent']): ?><span class="ek-flag urgent">URGENT</span><?php endif; ?>
          <?php if ($ad['status'] === 'sold'): ?><span class="badge text-bg-dark">SOLD</span><?php endif; ?>
        </div>
        <h1 class="h4 fw-bold"><?= e($ad['title']) ?></h1>
        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-2">
          <span><i class="fa-regular fa-clock me-1"></i><?= e(t('ad.posted')) ?>: <?= e(time_ago($ad['created_at'])) ?></span>
          <span><i class="fa-solid fa-eye me-1"></i><?= number_format((int)$ad['views']) ?> <?= e(t('ad.views')) ?></span>
          <span><i class="fa-solid fa-location-dot me-1"></i><?= e(trim(($ad['area'] ? $ad['area'] . ', ' : '') . ($ad['city_name'] ?? ''))) ?></span>
        </div>
        <hr>
        <div style="line-height:1.9;white-space:pre-wrap"><?= e($ad['description']) ?></div>
        <div class="row g-3 mt-3">
          <div class="col-sm-6"><div class="spec-row"><span class="k"><?= e(t('ad.condition')) ?></span><b><?= e(ucfirst($ad['condition'])) ?></b></div></div>
          <div class="col-sm-6"><div class="spec-row"><span class="k">Type</span><b><?= e(ucfirst($ad['seller_type'])) ?></b></div></div>
          <?php if ($subcat): ?><div class="col-sm-6"><div class="spec-row"><span class="k">Subcategory</span><b><?= e($subcat['name']) ?></b></div></div><?php endif; ?>
          <div class="col-sm-6"><div class="spec-row"><span class="k">Delivery</span><b><?= $ad['delivery_available'] ? 'Available' : 'Pickup only' ?></b></div></div>
          <?php if ($ad['negotiable']): ?><div class="col-sm-6"><div class="spec-row"><span class="k">Price</span><b><?= e(t('ad.negotiable')) ?></b></div></div><?php endif; ?>
        </div>
      </div>

      <?php include views_path('partials/comments-block'); ?>
    </div>

    <div class="col-lg-4">
      <div class="ek-card p-4 mb-3">
        <div class="price-tag mb-1"><?= (float)$ad['price'] > 0 ? money($ad['price']) : 'Contact for price' ?></div>
        <div class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i><?= e(trim(($ad['area'] ? $ad['area'] . ', ' : '') . ($ad['city_name'] ?? 'Kamalia'))) ?></div>
        <div class="d-grid gap-2">
          <?php if ($ad['phone']): ?><a class="btn btn-ek rounded-pill" href="tel:<?= e(pk_phone($ad['phone'])) ?>"><i class="fa-solid fa-phone me-2"></i><?= e(t('btn.call_now')) ?> <?= e(pk_phone($ad['phone'])) ?></a><?php endif; ?>
          <?php if ($ad['whatsapp']): ?><a class="btn btn rounded-pill text-white" style="background:#25D366" target="_blank" rel="noopener" href="<?= e(wa_link($ad['whatsapp'], 'Assalam-o-Alaikum! I am interested in your ad: ' . $ad['title'] . ' ' . url('/ad/' . $ad['slug']))) ?>"><i class="fa-brands fa-whatsapp me-2"></i><?= e(t('btn.whatsapp')) ?></a><?php endif; ?>
          <?php if (!$isOwner): ?>
            <button class="btn btn-outline-success rounded-pill" data-chat-start="ad" data-id="<?= $ad['id'] ?>"><i class="fa-regular fa-comment-dots me-2"></i><?= e(t('btn.chat')) ?></button>
          <?php endif; ?>
        </div>
        <hr>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill <?= $wishlisted ? 'active text-danger' : '' ?>" data-wishlist="ad" data-id="<?= $ad['id'] ?>"><i class="fa-solid fa-heart me-1"></i><?= $wishlisted ? e(t('btn.saved')) : e(t('btn.save')) ?></button>
          <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-like="ad" data-id="<?= $ad['id'] ?>"><i class="fa-<?= $liked ? 'solid' : 'regular' ?> fa-heart me-1 like-icon"></i><span class="like-count"><?= (int)$ad['likes_count'] ?></span></button>
          <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-share="<?= e(url('/ad/' . $ad['slug'])) ?>" data-title="<?= e($ad['title']) ?>"><i class="fa-solid fa-share-nodes me-1"></i></button>
        </div>
        <button class="btn btn-link btn-sm text-danger mt-2 w-100" data-report="ad" data-id="<?= $ad['id'] ?>"><i class="fa-regular fa-flag me-1"></i>Report this ad</button>
      </div>

      <div class="ek-card p-4 mb-3">
        <h6 class="fw-bold mb-3"><i class="fa-regular fa-user me-1"></i> <?= e(t('ad.seller')) ?></h6>
        <div class="d-flex align-items-center gap-2 mb-2">
          <img src="<?= asset('img/avatar-default.svg') ?>" style="width:44px;height:44px;border-radius:50%" alt="">
          <div>
            <div class="fw-semibold"><?= e($ad['user_name']) ?> <?php if ($ad['user_verified']): ?><i class="fa-solid fa-circle-check verified-tick"></i><?php endif; ?></div>
            <div class="text-muted" style="font-size:.72rem">Member since <?= e(fmt_date($ad['user_since'], 'M Y')) ?> • <?= (int)$sellerAds ?> active ads</div>
          </div>
        </div>
      </div>

      <div class="ek-card p-3">
        <div class="text-muted small mb-2"><i class="fa-solid fa-shield-halved text-success me-1"></i> Safety tips</div>
        <ul class="small text-muted ps-3 mb-0" style="line-height:1.9">
          <li>Meet in a public place in Kamalia</li>
          <li>Check the item before paying</li>
          <li>Beware of deals too good to be true</li>
          <li>Never send advance money to strangers</li>
        </ul>
      </div>
    </div>
  </div>

  <?php if ($similar): ?>
  <section class="ek-section">
    <div class="ek-section-head"><h2>Similar Ads Near You</h2></div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper"><?php foreach ($similar as $a): ?><div class="swiper-slide h-auto"><?php include views_path('partials/ad-card'); ?></div><?php endforeach; ?></div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>
</div>

<script>window.EK_PAGE_TYPE = 'ad'; window.EK_PAGE_ID = <?= (int)$ad['id'] ?>;</script>
