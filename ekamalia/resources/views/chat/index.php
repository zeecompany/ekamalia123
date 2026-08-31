<div class="container py-4">
  <h1 class="h4 fw-bold mb-3"><i class="fa-regular fa-comment-dots me-2 text-success"></i><?= e(t('nav.messages')) ?></h1>
  <div class="chat-wrap">
    <div class="chat-list">
      <?php foreach ($threads as $th): ?>
        <?php
        $isBuyerSide = (int)$th['buyer_id'] === (int)auth()['id'];
        $otherName = $isBuyerSide ? ($th['shop_name'] ?: $th['seller_name']) : $th['buyer_name'];
        $unread = $isBuyerSide ? (int)$th['buyer_unread'] : (int)$th['seller_unread'];
        ?>
        <a class="thread text-decoration-none text-dark" href="<?= url('/chat/' . $th['id']) ?>">
          <img src="<?= asset('img/avatar-default.svg') ?>" style="width:44px;height:44px;border-radius:50%" alt="">
          <div class="flex-grow-1 min-w-0">
            <div class="d-flex justify-content-between"><span class="fw-semibold small text-truncate"><?= e($otherName) ?></span>
              <span class="text-muted" style="font-size:.66rem"><?= e(time_ago($th['last_message_at'] ?? $th['created_at'])) ?></span></div>
            <div class="text-muted text-truncate" style="font-size:.76rem"><?= e(mb_substr((string)($th['last_msg'] ?? 'Start chatting…'), 0, 46)) ?></div>
          </div>
          <?php if ($unread > 0): ?><span class="ek-badge-dot" style="position:static"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <?php if (!$threads): ?><div class="p-4 text-center text-muted small">No conversations yet.<br>Chat with sellers from any product or ad!</div><?php endif; ?>
    </div>
    <div class="chat-thread-view align-items-center justify-content-center text-muted" style="font-size:.9rem">
      Select a conversation to start chatting 💬
    </div>
  </div>
</div>
