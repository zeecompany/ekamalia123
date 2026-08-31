<h1 class="h4 fw-bold mb-4">System Health</h1>
<div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3">
  <?php
  $rows = [
    ['PHP Version', $info['php'], $info['php_ok']],
    ['MySQL Version', $info['mysql'], $info['db_ok']],
    ['Disk Free', $info['disk_free'], true],
    ['Uploads Writable', $info['uploads_writable'] ? 'Yes' : 'NO', $info['uploads_writable']],
    ['Storage Writable', $info['storage_writable'] ? 'Yes' : 'NO', $info['storage_writable']],
    ['GD Extension', $info['gd'] ? 'Loaded' : 'Missing', $info['gd']],
    ['cURL Extension', $info['curl'] ? 'Loaded' : 'Missing', $info['curl']],
    ['mbstring', $info['mbstring'] ? 'Loaded' : 'Missing', $info['mbstring']],
    ['SMTP Configured', $info['smtp_set'] ? 'Yes' : 'Not yet', $info['smtp_set']],
    ['OTP System', $info['otp_enabled'] ? 'Enabled' : 'Disabled', true],
    ['Installed', $info['installed'] ? 'Yes (locked)' : 'Not installed', $info['installed']],
    ['Environment', $info['env'], true],
    ['App Version', $info['version'], true],
    ['App Log Size', $info['logs'], true],
    ['Failed Emails', $info['email_failures'], $info['email_failures'] === 0],
    ['Maintenance Mode', $info['maintenance'] ? 'ON' : 'Off', !$info['maintenance']],
  ];
  foreach ($rows as $r): ?>
  <div class="col"><div class="admin-card p-3 d-flex justify-content-between align-items-center">
    <span class="small fw-semibold"><?= $r[0] ?></span>
    <span class="badge text-bg-<?= $r[2] ? 'success' : 'danger' ?>"><?= e((string)$r[1]) ?></span>
  </div></div>
  <?php endforeach; ?>
</div>
<div class="alert alert-info small mt-4"><i class="fa-solid fa-lightbulb me-1"></i>
  Run regular backups from the <a href="<?= url('/admin/backups') ?>">Backups</a> page. If SMTP fails, check the Email tab in Settings and send a test email.</div>
