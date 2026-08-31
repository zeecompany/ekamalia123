<?php
/** Comments block. Expects: $comments, plus optional $commentTarget = ['type'=>'ad'|'product','id'=>..] */
$commentTarget ??= ['type' => 'ad', 'id' => $ad['id'] ?? 0];
?>
<div class="ek-card p-4 mb-3">
  <h5 class="fw-bold mb-3"><i class="fa-regular fa-comments me-1"></i> Comments <span class="text-muted">(<?= count($comments) ?>)</span></h5>
  <?php if (setting('comments_enabled') === '1'): ?>
    <?php if (auth()): ?>
    <form data-comment-form method="post" action="<?= url('/comment') ?>" class="mb-4">
      <?= csrf_field() ?>
      <input type="hidden" name="item_type" value="<?= e($commentTarget['type']) ?>">
      <input type="hidden" name="item_id" value="<?= (int)$commentTarget['id'] ?>">
      <div class="d-flex gap-2">
        <img src="<?= asset('img/avatar-default.svg') ?>" style="width:38px;height:38px;border-radius:50%" alt="">
        <div class="flex-grow-1">
          <textarea class="form-control" name="body" rows="2" placeholder="Write a comment…" maxlength="2000" required></textarea>
          <button class="btn btn-ek btn-sm rounded-pill px-4 mt-2 float-end">Post</button>
        </div>
      </div>
    </form>
    <?php else: ?>
    <div class="alert alert-light border small">Please <a href="<?= url('/login') ?>">login</a> to join the conversation.</div>
    <?php endif; ?>
    <?php foreach ($comments as $c): ?>
      <div class="comment">
        <div class="c-head">
          <img class="avatar-sm" src="<?= asset('img/avatar-default.svg') ?>" alt="">
          <div>
            <span class="fw-semibold small"><?= e($c['user_name']) ?></span>
            <span class="text-muted ms-1" style="font-size:.7rem"><?= e(time_ago($c['created_at'])) ?></span>
          </div>
        </div>
        <div class="c-body"><?= e($c['body']) ?></div>
        <div class="c-actions">
          <?php if (auth()): ?>
          <button data-like="comment" data-id="<?= $c['id'] ?>"><i class="fa-<?= $c['liked'] ? 'solid' : 'regular' ?> fa-heart me-1"></i><?= (int)$c['likes_count'] ?></button>
          <button onclick="replyTo(<?= $c['id'] ?>, '<?= e(addslashes($c['user_name'])) ?>')">Reply</button>
          <?php endif; ?>
          <?php if (auth() && ((int)$c['user_id'] === (int)user_id())): ?><button onclick="delComment(<?= $c['id'] ?>)">Delete</button><?php endif; ?>
          <button data-report="comment" data-id="<?= $c['id'] ?>">Report</button>
        </div>
        <?php if ($c['replies']): ?>
        <div class="replies mt-2">
          <?php foreach ($c['replies'] as $r): ?>
          <div class="comment" style="border:0;padding:.5rem 0">
            <div class="c-head"><img class="avatar-sm" style="width:28px;height:28px" src="<?= asset('img/avatar-default.svg') ?>" alt="">
              <span class="fw-semibold small"><?= e($r['user_name']) ?></span>
              <span class="text-muted" style="font-size:.68rem"><?= e(time_ago($r['created_at'])) ?></span></div>
            <div class="c-body"><?= e($r['body']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$comments): ?><p class="text-muted small">No comments yet — be the first!</p><?php endif; ?>
    <?php if (auth()): ?>
    <form data-comment-form method="post" action="<?= url('/comment') ?>" id="replyForm" class="d-none mt-3 border-top pt-3">
      <?= csrf_field() ?>
      <input type="hidden" name="item_type" value="<?= e($commentTarget['type']) ?>">
      <input type="hidden" name="item_id" value="<?= (int)$commentTarget['id'] ?>">
      <input type="hidden" name="parent_id" id="replyParent" value="">
      <label class="small text-muted" id="replyLabel"></label>
      <textarea class="form-control form-control-sm" name="body" rows="2" maxlength="2000" required></textarea>
      <button class="btn btn-ek btn-sm rounded-pill px-4 mt-2">Reply</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>
</div>
<script>
function replyTo(id, name) {
  const f = document.getElementById('replyForm');
  if (!f) return;
  f.classList.remove('d-none');
  document.getElementById('replyParent').value = id;
  document.getElementById('replyLabel').textContent = 'Replying to ' + name;
  f.querySelector('textarea').focus();
}
function delComment(id) {
  if (!ekConfirm('Delete this comment?')) return;
  ekPost('<?= url('/comment/delete') ?>', { id }).then(r => { toast(r.message, r.ok ? 'success' : 'danger'); if (r.ok) location.reload(); });
}
</script>
