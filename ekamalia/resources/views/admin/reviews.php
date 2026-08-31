<h1 class="h4 fw-bold mb-4">Reviews Moderation</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $v): ?>
  <a class="chip <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php foreach ($reviews as $r): ?>
<div class="admin-card p-3 mb-2">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div><b class="small"><?= e($r['user_name']) ?></b>
      <span class="stars small"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></span>
      <span class="badge text-bg-light border"><?= e($r['item_type']) ?> #<?= (int)$r['item_id'] ?></span>
      <?= $r['is_verified_purchase'] ? '<span class="badge text-bg-success">Verified purchase</span>' : '' ?></div>
    <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($r['created_at'])) ?></div>
  </div>
  <p class="small mb-2 mt-1"><?= e($r['body']) ?></p>
  <?php if ($r['seller_reply']): ?><div class="bg-light rounded-3 p-2 small mb-2"><b class="text-success">Seller reply:</b> <?= e($r['seller_reply']) ?></div><?php endif; ?>
  <div class="chip-actions d-flex flex-wrap">
    <?php if ($r['status'] !== 'approved'): ?><button class="btn btn-sm btn-success" onclick="adminAction('<?= url('/admin/reviews/' . $r['id'] . '/update') ?>', {action:'approve'})">Approve</button><?php endif; ?>
    <?php if ($r['status'] !== 'rejected'): ?><button class="btn btn-sm btn-outline-danger" onclick="adminAction('<?= url('/admin/reviews/' . $r['id'] . '/update') ?>', {action:'reject'})">Reject</button><?php endif; ?>
    <button class="btn btn-sm btn-outline-dark" onclick="adminAction('<?= url('/admin/reviews/' . $r['id'] . '/update') ?>', {action:'delete'}, 'Delete review permanently?')">Delete</button>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$reviews): ?><div class="ek-empty"><i class="fa-regular fa-star"></i><h5>No reviews</h5></div><?php endif; ?>
