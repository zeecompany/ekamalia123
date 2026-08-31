<h1 class="h4 fw-bold mb-4">Security</h1>
<div class="ek-card p-4 mb-3">
  <h5 class="fw-bold mb-3">Change Password</h5>
  <form method="post" action="<?= url('/dashboard/password') ?>" style="max-width:480px">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label small fw-semibold">Current password</label><input type="password" class="form-control" name="current_password" required></div>
    <div class="mb-3"><label class="form-label small fw-semibold">New password (min 8)</label><input type="password" class="form-control" name="password" minlength="8" required></div>
    <div class="mb-3"><label class="form-label small fw-semibold">Confirm new password</label><input type="password" class="form-control" name="password_confirmation" minlength="8" required></div>
    <button class="btn btn-ek rounded-pill px-4">Update Password</button>
  </form>
</div>
<div class="ek-card p-4 mb-3">
  <h5 class="fw-bold mb-3">Login History</h5>
  <div class="table-responsive table-responsive-stack">
  <table class="table table-sm align-middle">
    <thead><tr><th>Date</th><th>IP</th><th>Device</th></tr></thead>
    <tbody>
    <?php foreach ($logins as $l): ?>
    <tr><td data-label="Date" class="small"><?= e(fmt_date($l['created_at'])) ?></td><td data-label="IP" class="small"><?= e($l['ip']) ?></td><td data-label="Device" class="small text-muted text-truncate" style="max-width:260px"><?= e(mb_substr($l['user_agent'] ?? '', 0, 60)) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<div class="ek-card p-4 border-danger">
  <h5 class="fw-bold text-danger mb-2">Danger Zone</h5>
  <p class="text-muted small">Delete your account and remove personal data. Your orders history is anonymized.</p>
  <form method="post" action="<?= url('/dashboard/delete-account') ?>" onsubmit="return confirm('Type DELETE in the box to confirm permanent account deletion.')">
    <?= csrf_field() ?>
    <div class="input-group" style="max-width:320px">
      <input class="form-control" name="confirm" placeholder="Type DELETE">
      <button class="btn btn-danger">Delete Account</button>
    </div>
  </form>
</div>
