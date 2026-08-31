<div class="container py-5" style="max-width:440px">
  <div class="ek-card p-4 text-center">
    <div style="font-size:52px" class="text-success mb-2"><i class="fa-regular fa-envelope-open"></i></div>
    <h1 class="h5 fw-bold">Enter Verification Code</h1>
    <p class="text-muted small">We sent a 6-digit code to <b><?= e($ctx['identifier'] ?? '') ?></b><br>Purpose: <?= e(ucfirst($ctx['purpose'] ?? '')) ?></p>
    <form method="post" action="<?= url('/verify-otp') ?>">
      <?= csrf_field() ?>
      <input type="text" class="form-control form-control-lg text-center mb-3" name="code" placeholder="••••••" maxlength="6" pattern="\d{6}" required autofocus style="letter-spacing:12px;font-size:1.4rem">
      <button class="btn btn-ek w-100 rounded-pill btn-lg">Verify</button>
    </form>
    <form method="post" action="<?= url('/resend-otp') ?>" class="mt-3">
      <?= csrf_field() ?>
      <button class="btn btn-link small">Didn't get the code? Resend</button>
    </form>
  </div>
</div>
