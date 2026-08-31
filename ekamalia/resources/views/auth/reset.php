<div class="container py-5" style="max-width:440px">
  <div class="ek-card p-4">
    <h1 class="h5 fw-bold text-center mb-3">Set New Password</h1>
    <form method="post" action="<?= url('/reset-password') ?>">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label small fw-semibold">New password</label>
        <input type="password" class="form-control form-control-lg" name="password" minlength="8" required autofocus></div>
      <div class="mb-3"><label class="form-label small fw-semibold">Confirm password</label>
        <input type="password" class="form-control form-control-lg" name="password_confirmation" minlength="8" required></div>
      <button class="btn btn-ek w-100 rounded-pill">Update Password</button>
    </form>
  </div>
</div>
