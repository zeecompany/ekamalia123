<div class="container py-5" style="max-width:440px">
  <div class="text-center mb-4">
    <a class="ek-logo mb-2" href="<?= url('/') ?>">
      <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
      <span>eKamalia</span>
    </a>
    <h1 class="h4 mt-3 fw-bold text-dark"><?= e(t('auth.login')) ?></h1>
    <p class="text-muted small">Welcome back! Access your ads, orders &amp; shop</p>
  </div>
  <form class="ek-card p-4 shadow-sm" method="post" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label fw-semibold small text-muted"><?= e(t('auth.email')) ?></label>
      <input type="email" class="form-control form-control-lg" name="email" value="<?= old('email') ?>" required autofocus placeholder="name@example.com">
    </div>
    <div class="mb-2">
      <div class="d-flex justify-content-between align-items-center">
        <label class="form-label fw-semibold small text-muted mb-0"><?= e(t('auth.password')) ?></label>
        <a class="small text-success text-decoration-none" href="<?= url('/forgot-password') ?>"><?= e(t('auth.forgot')) ?></a>
      </div>
      <input type="password" class="form-control form-control-lg mt-1" name="password" required placeholder="Enter password">
    </div>
    <button class="btn btn-ek w-100 btn-lg rounded-pill mt-4">
      <span>Login</span> <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
    </button>
    <div class="text-center mt-3 pt-3 border-top small text-muted">
      <?= e(t('auth.no_account')) ?> <a class="fw-bold text-success text-decoration-none" href="<?= url('/register') ?>"><?= e(t('auth.register')) ?></a>
    </div>
  </form>
</div>
