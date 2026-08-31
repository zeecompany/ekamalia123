<h1 class="h4 fw-bold mb-4">Shop Reviews (<?= count($reviews) ?>)</h1>
<?php foreach ($reviews as $r): ?>
<div class="ek-card p-3 mb-2">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <img src="<?= asset('img/avatar-default.svg') ?>" style="width:36px;height:36px;border-radius:50%" alt="">
      <div><b class="small"><?= e($r['user_name']) ?></b>
        <div class="stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></div></div>
    </div>
    <div class="text-muted" style="font-size:.72rem"><?= e(time_ago($r['created_at'])) ?><?= $r['product_name'] ? ' • ' . e($r['product_name']) : '' ?></div>
  </div>
  <p class="small mb-2 mt-2"><?= e($r['body']) ?></p>
  <?php if ($r['seller_reply']): ?>
    <div class="bg-light rounded-3 p-2 small"><b class="text-success">Your reply:</b> <?= e($r['seller_reply']) ?></div>
  <?php else: ?>
  <form class="d-flex gap-2 reply-form" data-review-id="<?= $r['id'] ?>">
    <input class="form-control form-control-sm" placeholder="Reply to this review…" required>
    <button class="btn btn-ek-sm" type="submit">Reply</button>
  </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (!$reviews): ?><div class="ek-empty"><i class="fa-regular fa-star"></i><h5>No reviews yet</h5></div><?php endif; ?>
<script>
document.querySelectorAll('.reply-form').forEach(f => f.addEventListener('submit', e => {
  e.preventDefault();
  ekPost('<?= url('/review/reply') ?>', { id: f.dataset.reviewId, reply: f.querySelector('input').value }).then(r => { toast(r.message, r.ok ? 'success' : 'danger'); if (r.ok) location.reload(); });
}));
</script>
