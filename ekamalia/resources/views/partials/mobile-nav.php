<?php
/** Mobile bottom navigation */
$auth = (isset($auth) && $auth) ? $auth : auth();
$p = current_path();
$active = fn($prefix) => str_starts_with($p, $prefix) ? ' active' : '';
$unreadMsgs = $auth ? (int)qv('SELECT COALESCE(SUM(CASE WHEN seller_id=? THEN buyer_unread ELSE seller_unread END),0) FROM message_threads WHERE (buyer_id=? OR seller_id=?)', [$auth['id'], $auth['id'], $auth['id']]) : 0;
?>
<nav class="mobile-nav" aria-label="Mobile navigation">
  <a href="<?= url('/') ?>" class="<?= $p === '/' ? 'active' : '' ?>">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </a>
  <a href="<?= url('/categories') ?>" class="<?= $active('/category') . $active('/categories') ?>">
    <i class="fa-solid fa-grip"></i>
    <span>Categories</span>
  </a>
  <a href="<?= url('/ads/create') ?>" class="post-fab" aria-label="Post an Ad" title="Post Free Ad">
    <i class="fa-solid fa-plus"></i>
  </a>
  <a href="<?= url('/messages') ?>" class="<?= $active('/messages') . $active('/chat') ?>">
    <i class="fa-regular fa-comment-dots position-relative">
      <?php if ($unreadMsgs > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="font-size:0;width:8px;height:8px"></span>
      <?php endif; ?>
    </i>
    <span>Messages</span>
  </a>
  <a href="<?= url($auth ? '/dashboard' : '/login') ?>" class="<?= $active('/dashboard') . $active('/login') . $active('/profile') ?>">
    <i class="fa-regular fa-user"></i>
    <span>Account</span>
  </a>
</nav>
