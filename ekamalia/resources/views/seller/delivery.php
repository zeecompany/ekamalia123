<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h4 fw-bold mb-0">Delivery Desk</h1><p class="text-muted small mb-0">Orders waiting to be dispatched &amp; in transit</p></div>
  <a class="btn btn-outline-success rounded-pill px-3" href="<?= url('/seller/settings') ?>"><i class="fa-solid fa-truck me-1"></i>Delivery settings</a>
</div>
<div class="row g-3 mb-4 row-cols-3">
  <div class="col"><div class="kpi-card"><div class="ic ic-gold"><i class="fa-solid fa-box-open"></i></div><div><div class="val"><?= (int)$counts['confirmed'] ?></div><div class="lbl">To Dispatch</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-blue"><i class="fa-solid fa-truck-fast"></i></div><div><div class="val"><?= (int)$counts['dispatched'] ?></div><div class="lbl">In Transit</div></div></div></div>
  <div class="col"><div class="kpi-card"><div class="ic ic-green"><i class="fa-solid fa-circle-check"></i></div><div><div class="val"><?= (int)$counts['delivered30'] ?></div><div class="lbl">Delivered (30d)</div></div></div></div>
</div>
<?php foreach ($rows as $o): ?>
<div class="ek-card p-3 mb-2">
  <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
    <div>
      <b class="text-success"><?= e($o['no']) ?></b> <span class="badge text-bg-light border"><?= strtoupper(str_replace('_', ' ', $o['payment_method'])) ?></span>
      <div class="small mt-1"><i class="fa-solid fa-location-dot me-1 text-muted"></i><?= e($o['ship_name']) ?> • <?= e($o['ship_address']) ?>, <?= e($o['city_name'] ?: $o['ship_city']) ?> <a href="tel:<?= e($o['ship_phone']) ?>"><?= e(fmt_phone($o['ship_phone'])) ?></a></div>
      <?php if ($o['note']): ?><div class="small text-muted"><i class="fa-regular fa-comment me-1"></i><?= e($o['note']) ?></div><?php endif; ?>
    </div>
    <div class="text-end">
      <b><?= money($o['total']) ?></b>
      <form class="mt-2" method="post" action="<?= url('/seller/orders/status') ?>"><?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
        <input type="hidden" name="status" value="<?= $o['status'] === 'confirmed' ? 'dispatched' : 'delivered' ?>">
        <button class="btn btn-ek-sm"><?= $o['status'] === 'confirmed' ? 'Mark Dispatched' : 'Mark Delivered' ?></button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$rows): ?><div class="ek-empty"><i class="fa-solid fa-truck"></i><h5>Nothing to deliver right now</h5><p>Confirmed orders will queue here for dispatch.</p><a class="btn btn-outline-success rounded-pill px-4" href="<?= url('/seller/orders') ?>">View all orders</a></div><?php endif; ?>
