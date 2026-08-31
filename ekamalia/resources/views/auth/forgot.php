<div class="container py-5" style="max-width:440px">
  <div class="ek-card p-4">
    <div class="text-center mb-3">
      <div style="font-size:48px" class="text-warning"><i class="fa-solid fa-key"></i></div>
      <h1 class="h5 fw-bold mt-2"><?= e(t('auth.forgot')) ?></h1>
      <p class="text-muted small">Enter your account email — we'll send a reset code.</p>
    </div>
    <form method="post" action="<?= url('/forgot-password') ?>">
      <?= csrf_field() ?>
      <input type="email" class="form-control form-control-lg mb-3" name="email" placeholder="you@example.com" required autofocus>
      <button class="btn btn-ek w-100 rounded-pill">Send Reset Code</button>
    </form>
    <div class="text-center mt-3 small"><a href="<?= url('/login') ?>">Back to login</a></div>
  </div>
</div>
