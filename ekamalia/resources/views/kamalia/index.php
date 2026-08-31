<div class="container py-4">
  <div class="rounded-4 p-4 p-md-5 mb-4 text-white" style="background:var(--ek-gradient);box-shadow:var(--ek-shadow-lg)">
    <h1 class="h2 fw-bold mb-2">🏙️ Explore Kamalia</h1>
    <p class="mb-3" style="opacity:.92;max-width:640px">Kamalia — the historic city of District Toba Tek Singh, famous for its bazaars, handicrafts (khaddi), sugarmill and hardworking people. This is your complete local hub: businesses, ads, shops, news aur bijli updates — sab kuch ek jagah.</p>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-warning rounded-pill px-4 fw-bold" href="<?= url('/businesses') ?>">Local Businesses</a>
      <a class="btn btn-outline-light rounded-pill px-4" href="<?= url('/bijli') ?>">Bijli Updates</a>
      <a class="btn btn-outline-light rounded-pill px-4" href="<?= url('/news') ?>">City News</a>
    </div>
  </div>

  <div class="row g-3 mb-4 row-cols-2 row-cols-md-4">
    <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-store"></i></div><div><div class="val"><?= count($shops) ?>+</div><div class="lbl">Kamalia Shops</div></div></div></div>
    <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-briefcase"></i></div><div><div class="val"><?= count($businesses) ?>+</div><div class="lbl">Listed Businesses</div></div></div></div>
    <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-tag"></i></div><div><div class="val"><?= count($ads) ?>+</div><div class="lbl">Local Ads</div></div></div></div>
    <div class="col"><div class="kpi-card"><div class="ic <?= $offCount ? 'ic-red' : 'ic-teal' ?>"><i class="fa-solid fa-bolt"></i></div><div><div class="val"><?= $offCount ? $offCount . ' OFF' : 'All ON' ?></div><div class="lbl">Bijli Status</div></div></div></div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="ek-card p-4 h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">About Kamalia</h5>
        </div>
        <p class="small text-muted" style="line-height:1.9"><?= $aboutPage ? mb_substr(strip_tags((string)$aboutPage['content']), 0, 420) : '' ?>…</p>
        <h6 class="fw-bold mt-3 mb-2">Surrounding Areas We Serve</h6>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach (['Pirmahal', 'Toba Tek Singh', 'Chichawatni', 'Sahiwal', 'Burewala', 'Vehari', 'Gojra', 'Arifwala'] as $city): ?>
            <a class="chip" href="<?= url('/search?city=' . slugify($city)) ?>"><?= $city ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="ek-card p-4 h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">⚡ Bijli — Kamalia Feeders</h5>
          <a class="ek-link-all" href="<?= url('/bijli') ?>">Live <i class="fa-solid fa-angle-right"></i></a>
        </div>
        <div class="d-flex flex-column gap-2">
          <?php foreach (array_slice($feeders, 0, 6) as $f): $f['city_name'] = $f['city_name'] ?? 'Kamalia'; include views_path('partials/bijli-card'); endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if ($businesses): ?>
  <section class="ek-section">
    <div class="ek-section-head"><h2>Kamalia Business Directory</h2><a class="ek-link-all" href="<?= url('/businesses') ?>">See all <i class="fa-solid fa-angle-right"></i></a></div>
    <div class="row g-3"><?php foreach (array_slice($businesses, 0, 6) as $b): ?><div class="col-md-6"><?php include views_path('partials/business-card'); ?></div><?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <?php if ($ads): ?>
  <section class="ek-section pt-0">
    <div class="ek-section-head"><h2>Fresh Ads in Kamalia</h2><a class="ek-link-all" href="<?= url('/ads?city=kamalia') ?>">See all <i class="fa-solid fa-angle-right"></i></a></div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper"><?php foreach ($ads as $a): ?><div class="swiper-slide h-auto"><?php include views_path('partials/ad-card'); ?></div><?php endforeach; ?></div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>
</div>
