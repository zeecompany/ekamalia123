<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div class="d-flex align-items-center gap-3">
    <img src="<?= e($u['avatar'] ? upload_url($u['avatar']) : asset('img/avatar-default.svg')) ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover" alt="">
    <div>
      <h1 class="h4 fw-bold mb-0"><?= e($u['name']) ?> <?= $u['role'] === 'admin' ? '<span class="badge text-bg-dark">ADMIN</span>' : '' ?></h1>
      <div class="text-muted small"><?= e($u['email']) ?> • <?= e(pk_phone($u['phone'] ?? '')) ?> • <?= e($u['city_name'] ?? '') ?> • joined <?= e(fmt_date($u['created_at'], 'd M Y')) ?></div>
      <?php $cls = ['active' => 'success', 'pending' => 'warning', 'suspended' => 'secondary', 'banned' => 'danger'][$u['status']] ?? 'light'; ?>
      <span class="badge text-bg-<?= $cls ?> mt-1"><?= e($u['status']) ?></span> <?= !empty($u['is_verified']) ? '<span class="badge text-bg-primary">Verified</span>' : '' ?>
    </div>
  </div>
  <div class="chip-actions d-flex flex-wrap">
    <button class="btn btn-sm btn-outline-primary" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'<?= !empty($u['is_verified']) ? 'unverify' : 'verify' ?>'})"><?= !empty($u['is_verified']) ? 'Remove Verification' : 'Verify User' ?></button>
    <button class="btn btn-sm btn-outline-secondary" onclick="if(confirm('Send a new password to user?')) adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'reset_password'})">Reset Password</button>
    <button class="btn btn-sm <?= $u['status'] === 'banned' ? 'btn-success' : 'btn-outline-danger' ?>" onclick="adminAction('<?= url('/admin/users/' . $u['id'] . '/update') ?>', {action:'<?= $u['status'] === 'banned' ? 'unban' : 'ban' ?>'}, '<?= $u['status'] === 'banned' ? 'Unban' : 'Ban' ?> this user?')"><?= $u['status'] === 'banned' ? 'Unban' : 'Ban User' ?></button>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-6"><div class="admin-card"><div class="ac-head"><h6>Orders (<?= count($orders) ?>)</h6></div><div class="ac-body p-0">
    <?php foreach ($orders as $o): ?>
    <a class="d-flex justify-content-between px-3 py-2 border-bottom text-decoration-none text-dark small" href="<?= url('/admin/orders/' . $o['id']) ?>">
      <span><b><?= e($o['order_number']) ?></b> <span class="text-muted"><?= e(time_ago($o['created_at'])) ?></span></span>
      <span class="d-flex gap-2 align-items-center"><b><?= money($o['total']) ?></b><?= status_badge($o['status']) ?></span></a>
    <?php endforeach; ?>
    <?php if (!$orders): ?><p class="text-muted small p-3 mb-0">No orders.</p><?php endif; ?>
  </div></div></div>
  <div class="col-lg-6"><div class="admin-card"><div class="ac-head"><h6>Shops (<?= count($shops) ?>)</h6></div><div class="ac-body p-0">
    <?php foreach ($shops as $s): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span><b><?= e($s['name']) ?></b> <span class="text-muted">/shop/<?= e($s['slug']) ?></span></span><?= status_badge($s['status']) ?></div>
    <?php endforeach; ?>
    <?php if (!$shops): ?><p class="text-muted small p-3 mb-0">No shops.</p><?php endif; ?>
  </div></div></div>
  <div class="col-lg-6"><div class="admin-card"><div class="ac-head"><h6>Ads (<?= count($ads) ?>)</h6></div><div class="ac-body p-0">
    <?php foreach ($ads as $a): ?>
    <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span class="text-truncate"><?= e($a['title']) ?></span><?= status_badge($a['status']) ?></div>
    <?php endforeach; ?>
    <?php if (!$ads): ?><p class="text-muted small p-3 mb-0">No ads.</p><?php endif; ?>
  </div></div></div>
  <div class="col-lg-6"><div class="admin-card"><div class="ac-head"><h6>Login History</h6></div><div class="ac-body p-0">
    <?php foreach ($logins as $l): ?>
    <div class="px-3 py-2 border-bottom small"><i class="fa-solid fa-shield-halved text-muted me-2"></i><?= e($l['ip']) ?> — <?= e(mb_substr((string)$l['user_agent'], 0, 50)) ?><div class="text-muted" style="font-size:.66rem"><?= e(time_ago($l['created_at'])) ?></div></div>
    <?php endforeach; ?>
    <?php if (!$logins): ?><p class="text-muted small p-3 mb-0">No login records.</p><?php endif; ?>
  </div></div></div>
  <div class="col-lg-12"><div class="admin-card"><div class="ac-head"><h6>Reports Against This User</h6></div><div class="ac-body p-0">
    <?php foreach ($reports as $r): ?>
    <div class="px-3 py-2 border-bottom small"><b><?= e($r['type'] ?? 'report') ?></b> — <?= e($r['reason'] ?? '') ?> <span class="badge text-bg-light border"><?= e($r['status']) ?></span>
      <div class="text-muted" style="font-size:.66rem"><?= e(time_ago($r['created_at'])) ?></div></div>
    <?php endforeach; ?>
    <?php if (!$reports): ?><p class="text-muted small p-3 mb-0">Clean record — no reports.</p><?php endif; ?>
  </div></div></div>
</div>
