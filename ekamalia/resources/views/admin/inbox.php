<h1 class="h4 fw-bold mb-4">Contact Inbox</h1>
<div class="d-flex gap-2 flex-wrap mb-3">
  <?php foreach (['' => 'All', 'unread' => 'Unread', 'replied' => 'Replied', 'archived' => 'Archived'] as $k => $v): ?>
  <a class="chip <?= $filter === $k ? 'active' : '' ?>" href="?filter=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>
<?php foreach ($messages as $m): ?>
<div class="admin-card p-3 mb-2 <?= !$m['is_read'] ? 'border-start border-4 border-warning' : '' ?>">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div><b class="small"><?= e($m['name']) ?></b> <a class="small" href="mailto:<?= e($m['email']) ?>">&lt;<?= e($m['email']) ?>&gt;</a>
      <?= $m['phone'] ? '<span class="text-muted small">• ' . e(pk_phone($m['phone'])) . '</span>' : '' ?>
      <?php if (!$m['is_read']): ?><span class="badge text-bg-warning">NEW</span><?php endif; ?></div>
    <div class="text-muted" style="font-size:.68rem"><?= e(time_ago($m['created_at'])) ?></div>
  </div>
  <?php if ($m['subject']): ?><div class="fw-semibold small mt-1"><?= e($m['subject']) ?></div><?php endif; ?>
  <p class="small mb-2 mt-1 text-muted"><?= nl2br(e($m['message'])) ?></p>
  <?php if ($m['reply_text']): ?><div class="bg-light rounded-3 p-2 small mb-2"><b class="text-success">You replied:</b> <?= e($m['reply_text']) ?></div><?php endif; ?>
  <div class="chip-actions d-flex flex-wrap align-items-center gap-2">
    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#reply-<?= $m['id'] ?>"><i class="fa-solid fa-reply me-1"></i>Reply</button>
    <?php if (!$m['is_read']): ?><form method="post" action="<?= url('/admin/inbox/' . $m['id'] . '/update') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="read"><button class="btn btn-sm btn-outline-primary">Mark Read</button></form><?php endif; ?>
    <form method="post" action="<?= url('/admin/inbox/' . $m['id'] . '/update') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><button class="btn btn-sm btn-outline-secondary">Archive</button></form>
    <form method="post" action="<?= url('/admin/inbox/' . $m['id'] . '/update') ?>" onsubmit="return confirm('Delete message?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
  </div>
  <div class="collapse mt-2" id="reply-<?= $m['id'] ?>">
    <form method="post" action="<?= url('/admin/inbox/' . $m['id'] . '/update') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="reply">
      <textarea class="form-control mb-2" name="reply" rows="3" placeholder="Write your email reply…" required></textarea>
      <button class="btn btn-ek-sm">Send Reply</button>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php if (!$messages): ?><div class="ek-empty"><i class="fa-solid fa-inbox"></i><h5>Inbox empty</h5></div><?php endif; ?>
