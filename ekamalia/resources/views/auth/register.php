<?php $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order'); ?>
<div class="container py-5" style="max-width:540px">
  <div class="text-center mb-4">
    <a class="ek-logo mb-2" href="<?= url('/') ?>">
      <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
      <span>eKamalia</span>
    </a>
    <h1 class="h4 mt-3 fw-bold text-dark"><?= e(t('auth.register')) ?></h1>
    <p class="text-muted small">Join Kamalia's local commerce community — 100% free!</p>
  </div>
  <form class="ek-card p-4 shadow-sm" method="post" action="<?= url('/register') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold small text-muted"><?= e(t('auth.name')) ?> *</label>
        <input type="text" class="form-control" name="name" value="<?= old('name') ?>" required maxlength="120" placeholder="e.g. Ahmad Ali">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted"><?= e(t('auth.email')) ?> *</label>
        <input type="email" class="form-control" name="email" value="<?= old('email') ?>" required placeholder="name@example.com">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted"><?= e(t('auth.phone')) ?></label>
        <input type="tel" class="form-control" name="phone" value="<?= old('phone') ?>" placeholder="0300-1234567" pattern="[0-9+\-\s]{10,15}">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted">Area / City</label>
        <select class="form-select" name="city_id">
          <option value="">Select Area</option>
          <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= old('city_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted"><?= e(t('auth.password')) ?> *</label>
        <input type="password" class="form-control" name="password" required minlength="8" placeholder="Min 8 characters">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold small text-muted">Confirm Password *</label>
        <input type="password" class="form-control" name="password_confirmation" required minlength="8" placeholder="Repeat password">
      </div>
    </div>
    <div class="form-check mt-3 small text-muted">
      <input class="form-check-input" type="checkbox" id="agree" required>
      <label class="form-check-label" for="agree">I agree to the <a href="<?= url('/page/terms') ?>" class="text-success" target="_blank">Terms</a> &amp; <a href="<?= url('/page/privacy') ?>" class="text-success" target="_blank">Privacy Policy</a></label>
    </div>
    <button class="btn btn-ek w-100 btn-lg rounded-pill mt-3">
      <span>Create Free Account</span> <i class="fa-solid fa-user-plus ms-1"></i>
    </button>
    <div class="text-center mt-3 pt-3 border-top small text-muted">
      <?= e(t('auth.have_account')) ?> <a class="fw-bold text-success text-decoration-none" href="<?= url('/login') ?>"><?= e(t('auth.login')) ?></a>
    </div>
  </form>
</div>
