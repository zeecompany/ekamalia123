<?php /** Business directory card. Expects $b */ ?>
<article class="ek-biz-card reveal in">
  <img class="logo" src="<?= e(img_or($b['logo'] ?? '', 'assets/img/biz-logo.svg')) ?>" alt="<?= e($b['name']) ?>" loading="lazy">
  <div class="flex-grow-1 min-w-0">
    <a class="fw-bold d-block text-truncate text-dark" href="<?= url('/business/' . $b['slug']) ?>" style="font-size:0.96rem">
      <?= e($b['name']) ?>
      <?php if (!empty($b['is_verified'])): ?>
        <i class="fa-solid fa-circle-check verified-tick" title="Verified Business"></i>
      <?php endif; ?>
    </a>
    <div class="d-flex align-items-center gap-2 flex-wrap mt-1" style="font-size:0.78rem;color:var(--ek-text-muted)">
      <?php if (!empty($b['cat_name'])): ?>
        <span class="badge text-bg-light fw-semibold text-secondary"><i class="fa-solid fa-briefcase me-1 text-success"></i><?= e($b['cat_name']) ?></span>
      <?php endif; ?>
      <?php if (!empty($b['area']) || !empty($b['city_name'])): ?>
        <span class="text-truncate"><i class="fa-solid fa-location-dot me-1 text-muted"></i><?= e(trim(($b['area'] ? $b['area'] . ', ' : '') . ($b['city_name'] ?? 'Kamalia'))) ?></span>
      <?php endif; ?>
    </div>
    <?php if (!empty($b['rating_count']) && (int)$b['rating_count'] > 0): ?>
      <div class="stars mt-2">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fa-<?= $i <= round((float)$b['rating_avg']) ? 'solid' : 'regular' ?> fa-star<?= $i <= round((float)$b['rating_avg']) ? '' : ' off' ?>"></i>
        <?php endfor; ?>
        <span class="text-muted ms-1" style="font-size:0.75rem">(<?= (int)$b['rating_count'] ?>)</span>
      </div>
    <?php endif; ?>
  </div>
  <div class="d-flex flex-column gap-2 flex-shrink-0">
    <?php if (!empty($b['phone'])): ?>
      <a class="btn btn-sm btn-ek rounded-pill px-3" href="tel:<?= e($b['phone']) ?>" aria-label="Call business" title="Call Now">
        <i class="fa-solid fa-phone"></i>
      </a>
    <?php endif; ?>
    <?php if (!empty($b['whatsapp'])): ?>
      <a class="btn btn-sm btn-outline-success rounded-pill px-3" target="_blank" rel="noopener" href="<?= e(wa_link($b['whatsapp'], 'Hello ' . $b['name'])) ?>" aria-label="WhatsApp" title="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
      </a>
    <?php endif; ?>
  </div>
</article>
