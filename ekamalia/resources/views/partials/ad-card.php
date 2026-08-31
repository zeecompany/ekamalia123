<?php
/** Ad (classified) card partial. Expects $a */
?>
<article class="ek-item-card reveal in">
  <a class="thumb" href="<?= url('/ad/' . $a['slug']) ?>" aria-label="<?= e($a['title']) ?>">
    <img src="<?= e(img_or($a['image'] ?? '')) ?>" alt="<?= e($a['title']) ?>" loading="lazy" data-fade>
    <div class="flags">
      <?php if (!empty($a['is_featured'])): ?>
        <span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>
      <?php endif; ?>
      <?php if (!empty($a['is_urgent'])): ?>
        <span class="ek-flag urgent"><i class="fa-solid fa-fire"></i> Urgent</span>
      <?php endif; ?>
      <?php if (($a['condition'] ?? '') === 'new'): ?>
        <span class="ek-flag discount">New</span>
      <?php endif; ?>
    </div>
  </a>
  <div class="quick">
    <button class="ek-quick-btn <?= !empty($a['wishlisted']) ? 'active' : '' ?>" data-wishlist="ad" data-id="<?= $a['id'] ?>" aria-label="Save ad" title="Save ad">
      <i class="fa-solid fa-heart"></i>
    </button>
    <button class="ek-quick-btn" data-share="<?= e(url('/ad/' . $a['slug'])) ?>" data-title="<?= e($a['title']) ?>" aria-label="Share ad" title="Share">
      <i class="fa-solid fa-share-nodes"></i>
    </button>
  </div>
  <div class="body">
    <a class="title" href="<?= url('/ad/' . $a['slug']) ?>" title="<?= e($a['title']) ?>"><?= e($a['title']) ?></a>
    <div class="ek-price">
      <?= (float)$a['price'] > 0 ? money($a['price']) : 'Contact for price' ?>
      <?php if (!empty($a['negotiable'])): ?>
        <span class="old text-muted fw-normal" style="text-decoration:none;font-size:0.75rem">(Negotiable)</span>
      <?php endif; ?>
    </div>
    <div class="meta">
      <span class="text-truncate" style="max-width:140px"><i class="fa-solid fa-location-dot me-1"></i><?= e(trim(($a['area'] ? $a['area'] . ', ' : '') . ($a['city_name'] ?? 'Kamalia'))) ?></span>
      <span class="ms-auto"><i class="fa-regular fa-clock me-1"></i><?= e(time_ago($a['created_at'])) ?></span>
    </div>
  </div>
</article>
