<?php
/** Homepage — sections order controlled from Admin > Homepage Builder */
$enabled = array_column($sections, 'is_enabled', 'section_key');
$on = fn($k) => in_array($k, array_column($sections, 'section_key'), true); // exists
$enabledKeys = [];
foreach ($sections as $s) if ($s['is_enabled']) $enabledKeys[] = $s['section_key'];
$show = fn($k) => in_array($k, $enabledKeys, true);
$sectionTitle = [];
foreach ($sections as $s) $sectionTitle[$s['section_key']] = $s['title'];
$placeholder = asset('img/placeholder.svg');
?>

<?php if ($show('hero_slider') && $sliders): ?>
<section class="ek-hero">
  <div class="swiper heroSwiper">
    <div class="swiper-wrapper">
      <?php foreach ($sliders as $s): ?>
      <div class="swiper-slide ek-hero-slide" data-anim="<?= e($s['animation']) ?>">
        <img class="slide-bg" src="<?= e(str_starts_with($s['image'], 'assets/') ? asset($s['image']) : upload_url($s['image'])) ?>" alt="<?= e($s['title'] ?? 'eKamalia Marketplace') ?>" fetchpriority="high">
        <div class="slide-veil"></div>
        <div class="slide-content">
          <div class="hero-eyebrow"><i class="fa-solid fa-sparkles"></i> Kamalia's #1 Local Marketplace</div>
          <h2><?= e($s['title']) ?></h2>
          <p><?= e($s['subtitle']) ?></p>
          <div class="d-flex gap-3 flex-wrap align-items-center">
            <?php if ($s['button_text']): ?>
              <a class="btn-slide" href="<?= e(str_starts_with($s['button_url'] ?? '', 'http') ? $s['button_url'] : url($s['button_url'] ?? '/')) ?>">
                <?= e($s['button_text']) ?> <i class="fa-solid fa-arrow-right"></i>
              </a>
            <?php endif; ?>
            <a class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold" href="<?= url('/ads/create') ?>">
              <i class="fa-solid fa-plus me-1"></i> Post Free Ad
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>
<?php endif; ?>

<?php if ($show('quick_actions')): ?>
<section class="ek-section pt-2 pb-3">
  <div class="container">
    <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-5">
      <?php
      $quick = [
        ['Post an Ad', 'fa-circle-plus', 'qa-1', '/ads/create'],
        ['Find Products', 'fa-bag-shopping', 'qa-2', '/products'],
        ['Find Shops', 'fa-store', 'qa-3', '/shops'],
        ['Find Services', 'fa-screwdriver-wrench', 'qa-4', '/category/services'],
        ['Find Jobs', 'fa-briefcase', 'qa-5', '/category/jobs'],
        ['Property', 'fa-house-chimney', 'qa-6', '/category/property'],
        ['Vehicles', 'fa-car-side', 'qa-7', '/category/vehicles'],
        ['Electronics', 'fa-tv', 'qa-8', '/category/electronics'],
        ['Businesses', 'fa-map-location-dot', 'qa-9', '/businesses'],
        ['Explore Kamalia', 'fa-city', 'qa-10', '/explore-kamalia'],
      ];
      foreach ($quick as $q): ?>
        <div class="col">
          <a class="ek-qa-card <?= $q[2] ?>" href="<?= url($q[3]) ?>">
            <div class="qa-icon-wrap"><i class="fa-solid <?= $q[1] ?>"></i></div>
            <span class="qa-label"><?= e($q[0]) ?></span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<div class="container">
  <?php if ($show('marquee_offers')):
      $offers = qa('SELECT text,url FROM marquees WHERE status="active" AND type="offer" AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) ORDER BY sort_order LIMIT 8');
      if ($offers): $items = array_merge($offers, $offers); ?>
  <section class="mt-2 mb-3">
    <div class="ek-offers-bar" role="marquee" aria-label="Offers">
      <div class="mq-track">
        <?php foreach ($items as $o): ?>
          <span class="mq-item">
            <i class="fa-solid fa-gift text-warning"></i>
            <?php if ($o['url']): ?>
              <a href="<?= e(str_starts_with($o['url'], 'http') ? $o['url'] : url($o['url'])) ?>" class="text-decoration-none"><?= e($o['text']) ?></a>
            <?php else: ?>
              <?= e($o['text']) ?>
            <?php endif; ?>
            <span class="text-muted opacity-50">•</span>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($banner): ?>
  <section class="mt-3 mb-4">
    <a href="<?= url('/ad-click/' . $banner['id']) ?>" target="_blank" rel="noopener" class="d-block rounded-4 overflow-hidden shadow-sm">
      <img src="<?= e(upload_url($banner['image'])) ?>" class="w-100" style="max-height:180px;object-fit:cover" alt="<?= e($banner['title']) ?>" loading="lazy">
    </a>
  </section>
  <?php endif; ?>

  <?php if ($show('categories')): ?>
  <section class="ek-section" id="categories">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-layer-group"></i> Marketplace</span>
        <h2><?= e($sectionTitle['categories'] ?? 'Browse Categories') ?></h2>
        <p class="head-sub">Explore products, classifieds and services available across Kamalia</p>
      </div>
      <a class="ek-link-all" href="<?= url('/categories') ?>">
        <span>All categories</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6">
      <?php foreach (array_slice($cats, 0, 18) as $c): ?>
        <div class="col reveal">
          <a class="ek-cat-card" href="<?= url('/category/' . $c['slug']) ?>">
            <div class="ic"><i class="fa-solid <?= e($c['icon'] ?: 'fa-tag') ?>"></i></div>
            <div class="nm"><?= e($c['name']) ?></div>
            <span class="cat-count"><?= number_format((int)$c['ads_count']) ?> listings</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('bijli_updates')): ?>
  <?php $offCount = count(array_filter($feeders, fn($f) => ($f['status'] ?? 'on') !== 'on')); ?>
  <section class="ek-bijli-section">
    <div class="ek-section-head mb-3">
      <div class="head-info">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="section-pill text-warning"><i class="fa-solid fa-bolt"></i> Live Service</span>
          <?php if ($offCount > 0): ?>
            <span class="bijli-live-badge off"><span class="pulse-dot"></span> <?= $offCount ?> Feeder<?= $offCount > 1 ? 's' : '' ?> OFF Now</span>
          <?php else: ?>
            <span class="bijli-live-badge on"><span class="pulse-dot"></span> All Feeders Active</span>
          <?php endif; ?>
        </div>
        <h2><?= e($sectionTitle['bijli_updates'] ?? 'Bijli Live Updates') ?></h2>
        <p class="head-sub">Real-time electricity feeder status for Kamalia &amp; surroundings</p>
      </div>
      <a class="ek-link-all" href="<?= url('/bijli') ?>">
        <span>Live dashboard</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-4">
      <?php foreach (array_slice($feeders, 0, 4) as $f): ?>
        <div class="col reveal"><?php include views_path('partials/bijli-card'); ?></div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('featured_ads') && $ads): ?>
  <section class="ek-section">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-tag"></i> Classifieds</span>
        <h2><?= e($sectionTitle['featured_ads'] ?? 'Fresh & Featured Ads') ?></h2>
        <p class="head-sub">Latest verified deals and classified listings in Kamalia</p>
      </div>
      <a class="ek-link-all" href="<?= url('/ads') ?>">
        <span>View all ads</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper">
        <?php foreach ($ads as $a): ?><div class="swiper-slide h-auto"><?php include views_path('partials/ad-card'); ?></div><?php endforeach; ?>
      </div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('featured_products') && $products): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-bag-shopping"></i> Shopping</span>
        <h2><?= e($sectionTitle['featured_products'] ?? 'Featured Products') ?></h2>
        <p class="head-sub">Quality products available for immediate delivery from local shops</p>
      </div>
      <a class="ek-link-all" href="<?= url('/products') ?>">
        <span>Shop all</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper">
        <?php foreach ($products as $p): ?><div class="swiper-slide h-auto"><?php include views_path('partials/product-card'); ?></div><?php endforeach; ?>
      </div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('deals') && $deals): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill text-danger"><i class="fa-solid fa-percent"></i> Discounts</span>
        <h2><?= e($sectionTitle['deals'] ?? 'Deals & Special Offers') ?></h2>
        <p class="head-sub">Discounted products and limited-time promotional bargains</p>
      </div>
      <a class="ek-link-all" href="<?= url('/deals') ?>">
        <span>All deals</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper">
        <?php foreach ($deals as $p): ?><div class="swiper-slide h-auto"><?php include views_path('partials/product-card'); ?></div><?php endforeach; ?>
      </div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('featured_shops') && $shops): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-store"></i> Verified Sellers</span>
        <h2><?= e($sectionTitle['featured_shops'] ?? 'Featured Kamalia Shops') ?></h2>
        <p class="head-sub">Browse top-rated stores and local brands in town</p>
      </div>
      <a class="ek-link-all" href="<?= url('/shops') ?>">
        <span>All shops</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="swiper shopSwiper pb-4">
      <div class="swiper-wrapper">
        <?php foreach (array_slice($shops, 0, 8) as $s): ?><div class="swiper-slide h-auto"><?php include views_path('partials/shop-card'); ?></div><?php endforeach; ?>
      </div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('business_directory') && $businesses): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-map-location-dot"></i> Directory</span>
        <h2><?= e($sectionTitle['business_directory'] ?? 'Kamalia Business Directory') ?></h2>
        <p class="head-sub">Find local service providers, offices, doctors and shops</p>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <a class="ek-link-all" href="<?= url('/businesses') ?>">Full directory <i class="fa-solid fa-arrow-right"></i></a>
        <a class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" href="<?= url('/businesses/create') ?>"><i class="fa-solid fa-plus me-1"></i>List Business</a>
      </div>
    </div>
    <div class="row g-3 row-cols-1 row-cols-md-2">
      <?php foreach ($businesses as $b): ?><div class="col"><?php include views_path('partials/business-card'); ?></div><?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('news') && $news): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-newspaper"></i> Updates</span>
        <h2><?= e($sectionTitle['news'] ?? 'Kamalia News & Updates') ?></h2>
        <p class="head-sub">Local happenings, community news and announcements</p>
      </div>
      <a class="ek-link-all" href="<?= url('/news') ?>">
        <span>All news</span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
    <div class="row g-4 row-cols-1 row-cols-md-3">
      <?php foreach ($news as $n): ?>
      <div class="col reveal">
        <a class="ek-card d-block h-100 overflow-hidden text-decoration-none" href="<?= url('/news/' . $n['slug']) ?>">
          <img src="<?= e($n['image'] ? upload_url($n['image']) : asset('img/news-default.svg')) ?>" class="w-100" style="height:180px;object-fit:cover" alt="<?= e($n['title']) ?>" loading="lazy">
          <div class="p-3">
            <div class="text-muted small mb-1"><i class="fa-regular fa-clock me-1 text-success"></i><?= e(time_ago($n['published_at'])) ?></div>
            <div class="fw-bold text-dark mb-2" style="font-size:0.96rem;line-height:1.4"><?= e($n['title']) ?></div>
            <div class="text-muted" style="font-size:0.84rem;line-height:1.5"><?= e(mb_substr(strip_tags((string)$n['excerpt']), 0, 95)) ?>…</div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('testimonials') && $testimonials): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head">
      <div class="head-info">
        <span class="section-pill"><i class="fa-solid fa-comments"></i> Community</span>
        <h2><?= e($sectionTitle['testimonials'] ?? 'What Kamalia Says') ?></h2>
        <p class="head-sub">Trusted by shop owners, buyers and locals</p>
      </div>
    </div>
    <div class="row g-3 row-cols-1 row-cols-md-3">
      <?php foreach ($testimonials as $tt): ?>
      <div class="col reveal">
        <div class="ek-card p-4 h-100 d-flex flex-direction-column">
          <div class="stars mb-3"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $tt['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></div>
          <p class="mb-3 flex-grow-1" style="font-size:0.92rem;color:var(--ek-text-secondary);line-height:1.6">“<?= e($tt['comment']) ?>”</p>
          <div class="d-flex align-items-center gap-3 pt-2 border-top">
            <img src="<?= e($tt['photo'] ? upload_url($tt['photo']) : asset('img/avatar-default.svg')) ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover" alt="" loading="lazy">
            <div>
              <div class="fw-bold text-dark small"><?= e($tt['name']) ?></div>
              <?php if ($tt['business']): ?><div class="text-muted" style="font-size:0.75rem"><?= e($tt['business']) ?></div><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show('cta')): ?>
  <section class="ek-section pt-0 mb-4">
    <div class="rounded-4 p-4 p-md-5 text-center text-white position-relative overflow-hidden" style="background:var(--ek-gradient-primary);box-shadow:var(--ek-shadow-lg)">
      <h2 class="fw-bold text-white mb-2" style="font-size:clamp(1.5rem, 3vw, 2.25rem)">Apni Dukaan, Apna Bazaar 🇵🇰</h2>
      <p class="mb-4 mx-auto" style="max-width:620px;opacity:0.92;font-size:1.02rem">
        Join <?= number_format($stats['users'] ?? 1500) ?>+ Kamalia members. Post classified ads for free, launch your verified online shop, and grow your local business.
      </p>
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="<?= url('/ads/create') ?>" class="btn btn-gold rounded-pill px-4 py-2">
          <i class="fa-solid fa-plus me-1"></i> Post Free Ad
        </a>
        <a href="<?= url('/shops/create') ?>" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-success">
          <i class="fa-solid fa-store me-1"></i> Create Your Shop
        </a>
        <a href="<?= url('/pos/request') ?>" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">
          <i class="fa-solid fa-cash-register me-1"></i> Premium POS
        </a>
      </div>
      <div class="d-flex gap-4 justify-content-center flex-wrap mt-4 pt-3 border-top border-white border-opacity-25 small">
        <span><b class="ek-counter fs-6 text-warning" data-count="<?= $stats['ads'] ?? 250 ?>">0</b>+ Live Ads</span>
        <span><b class="ek-counter fs-6 text-warning" data-count="<?= $stats['products'] ?? 1200 ?>">0</b>+ Products</span>
        <span><b class="ek-counter fs-6 text-warning" data-count="<?= $stats['shops'] ?? 80 ?>">0</b>+ Verified Shops</span>
        <span><b class="ek-counter fs-6 text-warning" data-count="<?= $stats['users'] ?? 1500 ?>">0</b>+ Members</span>
      </div>
    </div>
  </section>
  <?php endif; ?>
</div>
