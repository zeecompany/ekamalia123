<h1 class="h4 fw-bold mb-4">My Activity</h1>
<h5 class="fw-bold mb-3">My Comments</h5>
<?php if ($comments): ?>
<?php foreach ($comments as $c): ?>
<div class="ek-card p-3 mb-2">
  <div class="d-flex justify-content-between"><span class="badge text-bg-light border"><?= e($c['type_label']) ?></span>
    <span class="text-muted" style="font-size:.7rem"><?= e(time_ago($c['created_at'])) ?></span></div>
  <p class="small mb-0 mt-1"><?= e($c['body']) ?></p>
</div>
<?php endforeach; ?>
<?php else: ?><p class="text-muted small">No comments yet.</p><?php endif; ?>
<h5 class="fw-bold mt-4 mb-3">My Reviews</h5>
<?php if ($reviews): ?>
<?php foreach ($reviews as $r): ?>
<div class="ek-card p-3 mb-2">
  <div class="d-flex justify-content-between"><span class="stars"><?php for ($i=1;$i<=5;$i++):?><i class="fa-<?=$i<=$r['rating']?'solid':'regular'?> fa-star"></i><?php endfor;?></span>
    <span class="text-muted" style="font-size:.7rem"><?= e(time_ago($r['created_at'])) ?></span></div>
  <p class="small mb-0 mt-1"><?= e($r['body']) ?></p>
  <?php if ($r['seller_reply']): ?><div class="bg-light rounded-3 p-2 small mt-2"><b class="text-success">Seller:</b> <?= e($r['seller_reply']) ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
<?php else: ?><p class="text-muted small">No reviews yet.</p><?php endif; ?>
