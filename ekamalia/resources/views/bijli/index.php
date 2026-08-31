<div class="container py-4">
  <div class="text-center mb-4">
    <div class="bijli-live-badge on mb-2">
      <span class="pulse-dot"></span> LIVE • Monitored by eKamalia Team
    </div>
    <h1 class="h3 fw-bold mb-1">⚡ <?= e(t('bijli.title')) ?></h1>
    <p class="text-muted mb-0 mx-auto" style="max-width:580px"><?= e(t('bijli.subtitle')) ?></p>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="ek-card p-3 d-flex align-items-center gap-3">
        <div class="bijli-orb off"><i class="fa-solid fa-plug-circle-xmark"></i></div>
        <div>
          <div class="fs-4 fw-bold text-danger"><?= $offCount ?></div>
          <div class="text-muted small fw-semibold">Feeders OFF Right Now</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="ek-card p-3 d-flex align-items-center gap-3">
        <div class="bijli-orb on"><i class="fa-solid fa-lightbulb"></i></div>
        <div>
          <div class="fs-4 fw-bold text-success"><?= count($feeders) - $offCount ?></div>
          <div class="text-muted small fw-semibold">Feeders ON &amp; Active</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="ek-card p-3 d-flex align-items-center gap-3">
        <div class="bijli-orb scheduled"><i class="fa-solid fa-tower-broadcast"></i></div>
        <div>
          <div class="fs-4 fw-bold text-primary"><?= count($feeders) ?></div>
          <div class="text-muted small fw-semibold">Monitored Sub-stations</div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="chip <?= !$activeCity && !input('feeder') ? 'active' : '' ?>" href="<?= url('/bijli') ?>">All Areas</a>
    <?php foreach ($cities as $c): ?>
      <a class="chip <?= ($activeCity && $activeCity['id'] == $c['id']) ? 'active' : '' ?>" href="<?= url('/bijli?city=' . $c['slug']) ?>">
        <i class="fa-solid fa-location-dot me-1"></i><?= e($c['name']) ?> <span class="opacity-75 ms-1">(<?= (int)$c['feeder_count'] ?>)</span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-gauge-high text-warning"></i> Real-time Feeder Status
      </h5>
      <div id="bijliLive" class="d-flex flex-column gap-2">
        <?php foreach ($feeders as $f): include views_path('partials/bijli-card'); endforeach; ?>
        <?php if (!$feeders): ?>
          <div class="ek-empty">
            <div class="empty-icon"><i class="fa-solid fa-plug"></i></div>
            <h5>No feeders monitored for this area yet</h5>
            <p>Our team is adding new feeder networks across Toba Tek Singh district.</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="text-muted small mt-2 d-flex align-items-center gap-1">
        <i class="fa-solid fa-rotate text-success"></i> Auto-refreshes every 60 seconds
      </div>

      <div class="ek-card p-4 mt-4">
        <h5 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-bullhorn me-1"></i> <?= e(t('bijli.report')) ?></h5>
        <p class="text-muted small mb-3">Notice an outage in your area? Report it to notify the community and update the live status.</p>
        <?php if (auth()): ?>
        <form id="bijliReportForm">
          <div class="row g-2">
            <div class="col-md-5">
              <select class="form-select" id="brFeeder" required>
                <option value="">Select Feeder</option>
                <?php foreach ($feeders as $f): ?><option value="<?= $f['id'] ?>"><?= e($f['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-7">
              <input class="form-control" id="brMsg" placeholder="e.g. Bijli gayi hai 30 min se — Chak 341" required maxlength="300">
            </div>
          </div>
          <button class="btn btn-danger rounded-pill px-4 mt-3 btn-sm fw-semibold">
            <i class="fa-solid fa-paper-plane me-1"></i> Send Outage Report
          </button>
        </form>
        <?php else: ?>
        <a class="btn btn-outline-success rounded-pill px-4 btn-sm fw-semibold" href="<?= url('/login') ?>">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Login to report an outage
        </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-5">
      <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
        <i class="fa-regular fa-clock text-primary"></i> Latest Outage Log &amp; News
      </h5>
      <div class="ek-card p-3" style="max-height:640px;overflow-y:auto">
        <?php foreach ($updates as $u):
            $cls = ['on' => 'success', 'off' => 'danger', 'maintenance' => 'warning', 'scheduled' => 'info'][$u['status']] ?? 'secondary';
            $icon = ['on' => 'fa-plug-circle-check', 'off' => 'fa-plug-circle-xmark', 'maintenance' => 'fa-screwdriver-wrench', 'scheduled' => 'fa-clock'][$u['status']] ?? 'fa-bolt'; ?>
        <div class="d-flex gap-2 py-3 border-bottom">
          <span class="badge text-bg-<?= $cls ?> align-self-start py-2 px-2" style="min-width:82px;font-size:0.75rem">
            <i class="fa-solid <?= $icon ?> me-1"></i><?= strtoupper($u['status']) ?>
          </span>
          <div class="min-w-0">
            <div class="fw-semibold small text-dark"><?= e($u['feeder_name']) ?> <span class="text-muted fw-normal">• <?= e($u['city_name']) ?></span></div>
            <?php if ($u['title']): ?><div class="small fw-semibold mt-1 text-secondary"><?= e($u['title']) ?></div><?php endif; ?>
            <?php if ($u['message']): ?><div class="text-muted" style="font-size:0.76rem;line-height:1.4"><?= e($u['message']) ?></div><?php endif; ?>
            <div class="text-muted mt-1" style="font-size:0.7rem">
              <i class="fa-regular fa-clock me-1"></i><?= e(fmt_date($u['created_at'])) ?><?= $u['expected_at'] ? ' • expected back ' . e(fmt_date($u['expected_at'], 'h:i A')) : '' ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$updates): ?>
          <p class="text-muted small text-center py-4 mb-0">No outage logs recorded today. All systems normal!</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
const brf = document.getElementById('bijliReportForm');
brf && brf.addEventListener('submit', e => {
  e.preventDefault();
  ekPost('<?= url('/bijli/report') ?>', { feeder_id: document.getElementById('brFeeder').value, message: document.getElementById('brMsg').value })
    .then(r => { toast(r.message, r.ok ? 'success' : 'danger'); if (r.ok) document.getElementById('brMsg').value = ''; });
});
</script>
