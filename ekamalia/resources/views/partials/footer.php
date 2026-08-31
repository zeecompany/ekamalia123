<?php
/** Footer */
$siteName = setting('site_name', 'eKamalia');
?>
<footer class="ek-footer pt-5">
  <div class="container">
    <div class="row g-4 pb-4">
      <div class="col-lg-4 col-md-6">
        <div class="ek-logo mb-3" style="color:#FFFFFF">
          <div class="logo-icon" style="box-shadow:none"><i class="fa-solid fa-store"></i></div>
          <span style="color:#FFFFFF;background:none;-webkit-text-fill-color:#FFFFFF"><?= e($siteName) ?></span>
          <span class="logo-tag">Official</span>
        </div>
        <p style="font-size:0.88rem;max-width:340px;color:#94A3B8;line-height:1.65">
          <?= e(t('footer.about')) ?>
        </p>
        <div class="mt-4">
          <?php if (setting('social_fb')): ?><a class="social-round" href="<?= e(setting('social_fb')) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
          <?php if (setting('social_ig')): ?><a class="social-round" href="<?= e(setting('social_ig')) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
          <?php if (setting('social_x')): ?><a class="social-round" href="<?= e(setting('social_x')) ?>" target="_blank" rel="noopener" aria-label="Twitter X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
          <?php if (setting('social_yt')): ?><a class="social-round" href="<?= e(setting('social_yt')) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a><?php endif; ?>
          <?php if (setting('whatsapp_number')): ?><a class="social-round" href="<?= e(wa_link(setting('whatsapp_number'), 'Hello ' . $siteName . '!')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a><?php endif; ?>
        </div>
      </div>

      <div class="col-lg-2 col-6">
        <h6><?= e(t('footer.links')) ?></h6>
        <a href="<?= url('/products') ?>">Products</a><br>
        <a href="<?= url('/ads') ?>">Classified Ads</a><br>
        <a href="<?= url('/shops') ?>">Verified Shops</a><br>
        <a href="<?= url('/businesses') ?>">Business Directory</a><br>
        <a href="<?= url('/deals') ?>">Deals & Coupons</a><br>
        <a href="<?= url('/news') ?>">Kamalia News</a>
      </div>

      <div class="col-lg-3 col-6">
        <h6>Kamalia Services</h6>
        <a href="<?= url('/bijli') ?>"><i class="fa-solid fa-bolt me-1 text-warning"></i> Bijli Live Updates</a><br>
        <a href="<?= url('/explore-kamalia') ?>">Explore Kamalia City</a><br>
        <a href="<?= url('/shops/create') ?>">Create Online Shop</a><br>
        <a href="<?= url('/pos/request') ?>">POS Terminal Access</a><br>
        <a href="<?= url('/categories') ?>">All Categories</a><br>
        <a href="<?= url('/cities') ?>">Areas & Surroundings</a>
      </div>

      <div class="col-lg-3">
        <h6><?= e(t('footer.support')) ?></h6>
        <?php foreach ($footerPages as $p): ?>
          <a href="<?= url('/page/' . $p['slug']) ?>"><?= e($p['title']) ?></a><br>
        <?php endforeach; ?>
        <a href="<?= url('/contact') ?>">Contact & Support</a><br>
        <div class="mt-3 p-3 rounded-3" style="background:#143522;border:1px solid rgba(255,255,255,0.06)">
          <div class="small fw-bold text-white mb-1"><i class="fa-solid fa-shield-halved text-success me-1"></i> 100% Kamalia Local</div>
          <div class="text-muted" style="font-size:0.75rem">Supporting local businesses and traders across Kamalia.</div>
        </div>
      </div>
    </div>

    <div class="f-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
      <span>© <?= date('Y') ?> <?= e($siteName) ?>. <?= e(t('footer.rights')) ?></span>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="text-white"><i class="fa-solid fa-location-dot me-1 text-success"></i> Kamalia, Toba Tek Singh, Punjab 🇵🇰</span>
        <div class="d-flex gap-1">
          <button class="chip border-0 bg-dark text-white py-1 px-2" style="font-size:0.75rem" data-lang="en">EN</button>
          <button class="chip border-0 bg-dark text-white py-1 px-2" style="font-size:0.75rem" data-lang="ur">اردو</button>
        </div>
      </div>
    </div>
  </div>
</footer>
