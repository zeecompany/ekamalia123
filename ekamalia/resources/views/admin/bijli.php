<h1 class="h4 fw-bold mb-1">Bijli Updates Manager</h1>
<p class="text-muted mb-4">Feeder-wise electricity status for Kamalia &amp; Toba Tek Singh district.</p>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="admin-card mb-3"><div class="ac-head"><h6>Add Feeder</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/bijli/feeder/save') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Feeder Name *</label><input class="form-control" name="name" placeholder="e.g. Kamalia City-1 Feeder" required></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">City</label><select class="form-select" name="city_id">
            <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= $c['is_primary'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-6"><label class="form-label">Area / Localities</label><input class="form-control" name="area" placeholder="e.g. Main Bazaar, Katchery Rd"></div>
        </div>
        <div class="mb-2"><label class="form-label">Description</label><input class="form-control" name="description"></div>
        <button class="btn btn-ek w-100 rounded-pill">Add Feeder</button>
      </form>
    </div></div>
    <div class="admin-card"><div class="ac-head"><h6>Post Load-Shedding / Status Update</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/bijli/feeder/update-status') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Feeder *</label><select class="form-select" name="feeder_id" required>
          <?php foreach ($feeders as $f): ?><option value="<?= $f['id'] ?>"><?= e($f['name']) ?> — <?= e($f['city_name']) ?> (now: <?= e(strtoupper($f['status'] ?? 'on')) ?>)</option><?php endforeach; ?></select></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label">New Status</label><select class="form-select" name="status">
            <option value="off">⚡ OFF — Load shedding</option><option value="on">✅ ON — Supply restored</option>
            <option value="maintenance">🔧 Maintenance</option><option value="scheduled">🕒 Scheduled</option></select></div>
          <div class="col-6"><label class="form-label">Started At</label><input type="datetime-local" class="form-control" name="started_at"></div>
        </div>
        <div class="mb-2"><label class="form-label">Expected Return</label><input type="datetime-local" class="form-control" name="expected_at"></div>
        <div class="mb-2"><label class="form-label">Title (auto if empty)</label><input class="form-control" name="title"></div>
        <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="2" placeholder="e.g. Shutdown 9am-1pm for grid maintenance"></textarea></div>
        <button class="btn btn-ek w-100 rounded-pill">Post Update</button>
        <div class="text-muted mt-2" style="font-size:.68rem">Posting resolves any open status & notifies users who reported this feeder in the last 7 days.</div>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <h6 class="fw-bold mb-3">Feeders (<?= count($feeders) ?>)</h6>
    <div class="table-responsive admin-card table-responsive-stack mb-4">
    <table class="table align-middle mb-0">
      <thead><tr><th>Feeder</th><th>City</th><th>Status</th><th>Reports</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($feeders as $f): ?>
      <tr>
        <td data-label="Feeder"><b class="small"><?= e($f['name']) ?></b><div class="text-muted" style="font-size:.66rem"><?= e($f['area'] ?? '') ?></div></td>
        <td data-label="City" class="small"><?= e($f['city_name']) ?></td>
        <td data-label="Status"><?php
          $st = $f['status'] ?? 'on';
          $map = ['on' => 'success', 'off' => 'danger', 'maintenance' => 'warning', 'scheduled' => 'info'];
          ?><span class="badge text-bg-<?= $map[$st] ?? 'light' ?>"><?= strtoupper(e($st)) ?></span>
          <?php if ($f['last_update']): ?><div class="text-muted" style="font-size:.62rem"><?= e(time_ago($f['last_update'])) ?></div><?php endif; ?></td>
        <td data-label="Reports" class="small"><?= (int)$f['open_reports'] ?> open</td>
        <td data-label="Actions"><div class="chip-actions d-flex">
          <form method="post" action="<?= url('/admin/bijli/feeder/' . $f['id'] . '/delete') ?>" onsubmit="return confirm('Delete feeder <?= e($f['name']) ?>?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
        </div></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <h6 class="fw-bold mb-3">Citizen Reports</h6>
    <?php foreach ($reports as $r): ?>
    <div class="admin-card p-2 px-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="small"><b><?= e($r['feeder_name']) ?></b> — <?= e(str_replace('_', ' ', $r['report_type'] ?? 'outage')) ?>
        <span class="text-muted">by <?= e($r['user_name'] ?? 'guest') ?> • <?= e(time_ago($r['created_at'])) ?></span>
        <?php if ($r['note']): ?><div class="text-muted" style="font-size:.66rem"><?= e($r['note']) ?></div><?php endif; ?></div>
      <div class="d-flex gap-1 align-items-center">
        <span class="badge text-bg-<?= $r['status'] === 'open' ? 'warning' : ($r['status'] === 'resolved' ? 'success' : 'light border') ?>"><?= e($r['status']) ?></span>
        <form method="post" action="<?= url('/admin/bijli/reports/' . $r['id'] . '/update') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="resolved"><button class="btn btn-sm btn-outline-success">Resolve</button></form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$reports): ?><p class="text-muted small">No citizen reports.</p><?php endif; ?>
    <h6 class="fw-bold mb-3 mt-4">Update History</h6>
    <?php foreach (array_slice($updates, 0, 15) as $u): ?>
    <div class="px-1 py-1 border-bottom small"><span class="badge text-bg-<?= ['on' => 'success', 'off' => 'danger', 'maintenance' => 'warning', 'scheduled' => 'info'][$u['status']] ?? 'light' ?>"><?= strtoupper(e($u['status'])) ?></span>
      <b><?= e($u['feeder_name']) ?></b> — <?= e($u['title']) ?> <span class="text-muted">by <?= e($u['by_name'] ?? 'admin') ?> • <?= e(time_ago($u['created_at'])) ?></span></div>
    <?php endforeach; ?>
  </div>
</div>
