<?php
/** Header: desktop + mobile */
$me = auth();
$locSel = $_GET['city'] ?? ($_SESSION['loc_city'] ?? '');
?>
<header class="ek-header">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <!-- Mobile hamburger toggle -->
      <button class="ek-icon-btn d-lg-none" onclick="ekDrawer(true)" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- Brand Logo -->
      <a class="ek-logo me-lg-4" href="<?= url('/') ?>" aria-label="eKamalia Home">
        <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
        <span>eKamalia</span>
        <span class="logo-tag d-none d-sm-inline">Kamalia</span>
      </a>

      <!-- Desktop Search Bar -->
      <form class="ek-search-form d-none d-md-flex" action="<?= url('/search') ?>" method="get" role="search">
        <div class="ek-search-wrapper">
          <div class="loc-pill">
            <i class="fa-solid fa-location-dot"></i>
            <select class="loc-select" name="city" aria-label="Location">
              <option value=""><?= e(t('search.anywhere')) ?></option>
              <?php foreach ($citiesTop as $c): ?>
                <option value="<?= e($c['slug']) ?>" <?= $locSel === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="text" id="globalSearch" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(t('search.placeholder')) ?>" autocomplete="off" aria-label="Search products, ads, shops">
          <button type="submit" class="ek-search-btn" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>
        <div class="ek-search-suggest" id="searchSuggest"></div>
      </form>

      <!-- Action Buttons & User Menu -->
      <div class="ek-actions ms-auto">
        <a class="ek-btn-post d-none d-lg-inline-flex" href="<?= url('/ads/create') ?>">
          <i class="fa-solid fa-circle-plus"></i>
          <span><?= e(t('nav.post_ad')) ?></span>
        </a>

        <?php if ($me): ?>
          <a class="ek-icon-btn" href="<?= url('/messages') ?>" aria-label="Messages" title="Messages">
            <i class="fa-regular fa-comment-dots"></i>
            <span class="ek-badge-dot" id="msgBadge" style="display:<?= $unreadMsgs ? 'flex' : 'none' ?>"><?= $unreadMsgs ?></span>
          </a>
          <button class="ek-icon-btn" data-dd="ddNotif" aria-label="Notifications" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="ek-badge-dot" data-notif-badge style="display:<?= $unreadNotifs ? 'flex' : 'none' ?>"><?= $unreadNotifs ?></span>
          </button>
        <?php endif; ?>

        <a class="ek-icon-btn" href="<?= url('/wishlist') ?>" aria-label="Wishlist" title="Wishlist">
          <i class="fa-regular fa-heart"></i>
        </a>

        <a class="ek-icon-btn" href="<?= url('/cart') ?>" aria-label="Shopping Cart" title="Cart">
          <i class="fa-solid fa-basket-shopping"></i>
          <span class="ek-badge-dot" data-cart-badge style="display:<?= $cartCount ? 'flex' : 'none' ?>"><?= $cartCount ?></span>
        </a>

        <?php if ($me): ?>
          <div class="dropdown">
            <button class="btn p-0 border-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= $me['avatar'] ? upload_url($me['avatar']) : asset('img/avatar-default.svg') ?>" alt="<?= e($me['name']) ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--ek-primary-light)" loading="lazy">
              <span class="d-none d-xl-inline fw-semibold text-dark small"><?= e(explode(' ', $me['name'])[0]) ?></span>
              <i class="fa-solid fa-angle-down text-muted small d-none d-xl-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="min-width:240px">
              <div class="px-3 py-2 bg-light rounded-3 mb-2">
                <div class="fw-bold small text-dark"><?= e($me['name']) ?></div>
                <div class="text-muted" style="font-size:.75rem"><?= e($me['email'] ?: $me['phone']) ?></div>
              </div>
              <a class="dropdown-item rounded-3 py-2" href="<?= url('/dashboard') ?>"><i class="fa-solid fa-gauge-high me-2 text-success"></i><?= e(t('nav.dashboard')) ?></a>
              <a class="dropdown-item rounded-3 py-2" href="<?= url('/dashboard/orders') ?>"><i class="fa-solid fa-box me-2 text-primary"></i><?= e(t('nav.my_orders')) ?></a>
              <a class="dropdown-item rounded-3 py-2" href="<?= url('/dashboard/my-ads') ?>"><i class="fa-solid fa-tag me-2 text-warning"></i><?= e(t('nav.my_ads')) ?></a>
              <?php if ($myShopExists): ?>
                <a class="dropdown-item rounded-3 py-2" href="<?= url('/seller') ?>"><i class="fa-solid fa-store me-2 text-success"></i><?= e(t('nav.my_shop')) ?></a>
              <?php else: ?>
                <a class="dropdown-item rounded-3 py-2" href="<?= url('/shops/create') ?>"><i class="fa-solid fa-store me-2 text-success"></i>Create Your Shop</a>
              <?php endif; ?>
              <?php if (pos_access()): ?>
                <a class="dropdown-item rounded-3 py-2" href="<?= url('/pos') ?>"><i class="fa-solid fa-cash-register me-2 text-info"></i><?= e(t('nav.pos')) ?></a>
              <?php endif; ?>
              <?php if ($me['role'] === 'admin'): ?>
                <a class="dropdown-item rounded-3 py-2 text-success fw-bold" href="<?= url('/admin') ?>"><i class="fa-solid fa-user-shield me-2"></i><?= e(t('nav.admin')) ?></a>
              <?php endif; ?>
              <hr class="my-1">
              <a class="dropdown-item rounded-3 py-2 text-danger" href="<?= url('/logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i><?= e(t('nav.logout')) ?></a>
            </div>
          </div>
        <?php else: ?>
          <a class="ek-icon-btn d-sm-none" href="<?= url('/login') ?>" aria-label="Login"><i class="fa-regular fa-user"></i></a>
          <a class="btn btn-outline-success d-none d-sm-inline-flex rounded-pill px-3 py-1 fw-semibold small" href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a>
          <a class="btn btn-ek d-none d-sm-inline-flex rounded-pill px-3 py-1 fw-semibold small" href="<?= url('/register') ?>"><?= e(t('nav.register')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Mobile Search Bar -->
  <div class="container d-md-none pb-2 pt-1">
    <form class="ek-search-form" action="<?= url('/search') ?>" method="get" role="search">
      <div class="ek-search-wrapper w-100">
        <input type="text" id="globalSearchM" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(t('search.placeholder')) ?>" aria-label="Search">
        <input type="hidden" name="city" value="<?= e($locSel) ?>">
        <button type="submit" class="ek-search-btn" aria-label="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>
    </form>
  </div>

  <!-- Category & Quick Nav Bar (Desktop) -->
  <div class="ek-catnav d-none d-lg-block">
    <div class="container">
      <div class="scroller">
        <a href="<?= url('/categories') ?>" class="fw-bold"><i class="fa-solid fa-grip text-success"></i> All Categories</a>
        <a href="<?= url('/bijli') ?>"><i class="fa-solid fa-bolt text-warning"></i> Bijli Updates <span class="cat-live-badge">LIVE</span></a>
        <a href="<?= url('/shops') ?>"><i class="fa-solid fa-store text-primary"></i> Shops</a>
        <a href="<?= url('/businesses') ?>"><i class="fa-solid fa-map-location-dot text-info"></i> Directory</a>
        <a href="<?= url('/deals') ?>"><i class="fa-solid fa-percent text-danger"></i> Deals</a>
        <?php foreach ($mainCats as $c): ?>
          <a href="<?= url('/category/' . $c['slug']) ?>"><i class="fa-solid <?= e($c['icon'] ?: 'fa-tag') ?>"></i> <?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Notifications Dropdown Panel -->
  <?php if ($me): ?>
  <div class="ek-dropdown" id="ddNotif">
    <div class="dd-head">
      <span>Notifications</span>
      <a href="<?= url('/notifications') ?>" class="small text-success text-decoration-none">View all</a>
    </div>
    <div class="dd-body" id="notifList">
      <div class="p-4 text-center text-muted small">Loading notifications…</div>
    </div>
  </div>
  <?php endif; ?>
</header>

<!-- Mobile Slide-out Drawer -->
<div class="ek-drawer-backdrop" id="ekDrawerBackdrop"></div>
<aside class="ek-drawer" id="ekDrawer" aria-label="Navigation Menu">
  <div class="dw-head">
    <div class="d-flex justify-content-between align-items-center">
      <span class="ek-logo" style="color:#fff;background:none;-webkit-text-fill-color:#fff">
        <i class="fa-solid fa-store text-warning"></i> eKamalia
      </span>
      <button class="btn text-white p-1" onclick="ekDrawer(false)" aria-label="Close menu">
        <i class="fa-solid fa-xmark fs-5"></i>
      </button>
    </div>
    <?php if ($me): ?>
      <div class="d-flex align-items-center gap-3 mt-3 pt-2 border-top border-white border-opacity-25">
        <img src="<?= $me['avatar'] ? upload_url($me['avatar']) : asset('img/avatar-default.svg') ?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid #fff" alt="">
        <div>
          <div class="fw-bold small text-white"><?= e($me['name']) ?></div>
          <div style="font-size:.75rem;color:rgba(255,255,255,.85)"><?= e($me['phone'] ?: $me['email']) ?></div>
        </div>
      </div>
    <?php else: ?>
      <div class="d-grid gap-2 mt-3">
        <a href="<?= url('/login') ?>" class="btn btn-light btn-sm rounded-pill fw-bold text-success">Login</a>
        <a href="<?= url('/register') ?>" class="btn btn-outline-light btn-sm rounded-pill fw-semibold">Create Free Account</a>
      </div>
    <?php endif; ?>
  </div>

  <nav class="flex-grow-1 py-2">
    <a class="dw-link" href="<?= url('/') ?>"><i class="fa-solid fa-house text-success"></i> <?= e(t('nav.home')) ?></a>
    <a class="dw-link" href="<?= url('/categories') ?>"><i class="fa-solid fa-grip text-primary"></i> <?= e(t('nav.categories')) ?></a>
    <a class="dw-link fw-bold text-success" href="<?= url('/ads/create') ?>"><i class="fa-solid fa-circle-plus text-success"></i> <?= e(t('nav.post_ad')) ?></a>
    <a class="dw-link" href="<?= url('/products') ?>"><i class="fa-solid fa-bag-shopping text-warning"></i> <?= e(t('nav.products')) ?></a>
    <a class="dw-link" href="<?= url('/ads') ?>"><i class="fa-solid fa-tag text-info"></i> <?= e(t('nav.ads')) ?></a>
    <a class="dw-link" href="<?= url('/shops') ?>"><i class="fa-solid fa-store text-success"></i> <?= e(t('nav.shops')) ?></a>
    <a class="dw-link" href="<?= url('/businesses') ?>"><i class="fa-solid fa-map-location-dot text-danger"></i> <?= e(t('nav.businesses')) ?></a>
    <a class="dw-link" href="<?= url('/bijli') ?>"><i class="fa-solid fa-bolt text-warning"></i> <?= e(t('nav.bijli')) ?> <span class="badge text-bg-warning ms-auto">LIVE</span></a>
    <a class="dw-link" href="<?= url('/deals') ?>"><i class="fa-solid fa-percent text-danger"></i> Deals & Discounts</a>
    <a class="dw-link" href="<?= url('/news') ?>"><i class="fa-solid fa-newspaper text-secondary"></i> <?= e(t('nav.news')) ?></a>
    <a class="dw-link" href="<?= url('/explore-kamalia') ?>"><i class="fa-solid fa-city text-primary"></i> <?= e(t('nav.explore')) ?></a>
    <a class="dw-link" href="<?= url('/contact') ?>"><i class="fa-solid fa-headset text-muted"></i> <?= e(t('nav.contact')) ?></a>

    <hr class="my-2">

    <?php if ($me): ?>
      <a class="dw-link" href="<?= url('/dashboard') ?>"><i class="fa-solid fa-gauge-high text-success"></i> <?= e(t('nav.dashboard')) ?></a>
      <a class="dw-link" href="<?= url('/dashboard/my-ads') ?>"><i class="fa-solid fa-tag text-warning"></i> <?= e(t('nav.my_ads')) ?></a>
      <?php if ($myShopExists): ?>
        <a class="dw-link" href="<?= url('/seller') ?>"><i class="fa-solid fa-store text-success"></i> <?= e(t('nav.my_shop')) ?></a>
      <?php else: ?>
        <a class="dw-link" href="<?= url('/shops/create') ?>"><i class="fa-solid fa-store text-success"></i> Create Shop</a>
      <?php endif; ?>
      <?php if (pos_access()): ?>
        <a class="dw-link" href="<?= url('/pos') ?>"><i class="fa-solid fa-cash-register text-info"></i> POS Terminal</a>
      <?php endif; ?>
      <a class="dw-link text-danger" href="<?= url('/logout') ?>"><i class="fa-solid fa-right-from-bracket text-danger"></i> <?= e(t('nav.logout')) ?></a>
    <?php endif; ?>

    <div class="p-3 d-flex gap-2 align-items-center">
      <span class="small text-muted fw-semibold">Language:</span>
      <button class="chip <?= lang() === 'en' ? 'active' : '' ?>" data-lang="en">English</button>
      <button class="chip <?= lang() === 'ur' ? 'active' : '' ?>" data-lang="ur">اردو</button>
    </div>
  </nav>
</aside>
