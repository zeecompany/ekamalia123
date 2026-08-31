<?php
/**
 * Bijli feeder status card. Expects $f = [
 *   id,name,area,city_name,status,started_at,expected_at,message, since_txt
 * ]
 */
$map = [
    'on' => ['Supply Active', 'fa-lightbulb', 'on'],
    'off' => ['Power Outage', 'fa-plug-circle-xmark', 'off'],
    'maintenance' => ['Maintenance', 'fa-screwdriver-wrench', 'maintenance'],
    'scheduled' => ['Scheduled Cut', 'fa-clock', 'scheduled']
];
[$label, $icon, $cls] = $map[$f['status']] ?? $map['on'];
?>
<div class="bijli-card">
  <div class="bijli-orb <?= $cls ?>">
    <i class="fa-solid <?= $icon ?>"></i>
  </div>
  <div class="flex-grow-1 min-w-0">
    <div class="fw-bold text-truncate text-dark" style="font-size:0.95rem"><?= e($f['name']) ?></div>
    <div class="text-muted text-truncate" style="font-size:0.78rem">
      <i class="fa-solid fa-location-dot me-1 text-muted"></i><?= e($f['area'] ?: ($f['city_name'] ?? 'Kamalia')) ?>
    </div>
    <div class="bijli-status <?= $cls ?> mt-1">
      <span><?= $label ?></span>
      <?php if ($f['status'] === 'off' && !empty($f['started_at'])): ?>
        <span class="text-muted fw-normal" style="font-size:0.75rem">— <?= e(t('bijli.since')) ?> <?= e(time_ago($f['started_at'])) ?></span>
      <?php elseif ($f['status'] !== 'on' && !empty($f['expected_at'])): ?>
        <span class="text-muted fw-normal" style="font-size:0.75rem">— <?= e(t('bijli.expected')) ?>: <?= e(fmt_date($f['expected_at'], 'h:i A')) ?></span>
      <?php endif; ?>
    </div>
    <?php if (!empty($f['message']) && $f['status'] !== 'on'): ?>
      <div class="text-muted mt-1" style="font-size:0.75rem;line-height:1.4"><?= e($f['message']) ?></div>
    <?php endif; ?>
  </div>
  <div class="text-end flex-shrink-0">
    <div class="text-muted" style="font-size:0.68rem"><?= e(t('bijli.last')) ?></div>
    <div class="fw-semibold text-secondary" style="font-size:0.76rem"><?= e(time_ago($f['last_update'] ?? null)) ?></div>
    <a class="btn btn-sm btn-link p-0 mt-1 text-success text-decoration-none" style="font-size:0.72rem" href="<?= url('/bijli?feeder=' . $f['id']) ?>">History →</a>
  </div>
</div>
