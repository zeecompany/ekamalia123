<?php $isBuyerSide = (int)$thread['buyer_id'] === (int)auth()['id'];
$otherName = $isBuyerSide ? ($thread['shop_name'] ?: $thread['seller_name']) : $thread['buyer_name']; ?>
<div class="container py-4">
  <a class="btn btn-link btn-sm d-md-none mb-2" href="<?= url('/messages') ?>"><i class="fa-solid fa-arrow-left"></i> All chats</a>
  <div class="chat-wrap open-thread">
    <div class="chat-list">
      <?php foreach ($threads as $th): ?>
        <?php $other = ((int)$th['buyer_id'] === (int)auth()['id']) ? ($th['shop_name'] ?: $th['seller_name']) : $th['buyer_name']; ?>
        <a class="thread text-decoration-none text-dark <?= (int)$th['id'] === (int)$thread['id'] ? 'active' : '' ?>" href="<?= url('/chat/' . $th['id']) ?>">
          <img src="<?= asset('img/avatar-default.svg') ?>" style="width:44px;height:44px;border-radius:50%" alt="">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold small text-truncate"><?= e($other) ?></div>
            <div class="text-muted text-truncate" style="font-size:.76rem"><?= e(mb_substr((string)($th['last_msg'] ?? ''), 0, 40)) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="chat-thread-view">
      <div class="d-flex align-items-center gap-2 px-3 py-2 bg-white border-bottom">
        <img src="<?= asset('img/avatar-default.svg') ?>" style="width:40px;height:40px;border-radius:50%" alt="">
        <div class="flex-grow-1">
          <div class="fw-bold small"><?= e($otherName) ?></div>
          <div class="text-muted" id="presence" style="font-size:.68rem">connecting…</div>
        </div>
        <div class="dropdown">
          <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown" aria-label="Options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
          <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
            <button class="dropdown-item rounded-3" onclick="blockUser()"><i class="fa-solid fa-ban me-2"></i>Block user</button>
            <button class="dropdown-item rounded-3 text-danger" onclick="reportChat()"><i class="fa-regular fa-flag me-2"></i>Report</button>
          </div>
        </div>
      </div>
      <div class="chat-msgs" id="chatMsgs">
        <?php if ($refProduct): ?>
          <a class="bubble them d-block text-decoration-none text-dark" href="<?= url('/product/' . $refProduct['slug']) ?>" style="max-width:260px">
            <img src="<?= e(img_or($refProduct['image'] ?? '')) ?>" style="width:100%;border-radius:10px" alt="">
            <div class="fw-semibold small mt-1"><?= e($refProduct['name']) ?></div>
            <div class="text-success fw-bold"><?= money($refProduct['sale_price'] ?: $refProduct['price']) ?></div>
          </a>
        <?php endif; ?>
        <?php foreach ($messages as $m): ?>
          <?php $mine = (int)$m['sender_id'] === (int)auth()['id']; ?>
          <div class="bubble <?= $mine ? 'me' : 'them' ?>">
            <?php if ($m['image']): ?><img src="<?= e(upload_url($m['image'])) ?>" alt=""><?php endif; ?>
            <?php if ($m['body']): ?><div><?= e($m['body']) ?></div><?php endif; ?>
            <div class="time"><?= e(date('h:i A', strtotime($m['created_at']))) ?><?= $mine ? ' <i class="fa-solid fa-check' . ($m['is_read'] ? '-double' : '') . '" style="font-size:.6rem"></i>' : '' ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($blocked): ?>
        <div class="p-3 bg-white border-top text-center small text-danger">You blocked this user. <button class="btn btn-link btn-sm p-0" onclick="unblock()">Unblock</button></div>
      <?php else: ?>
      <form class="chat-input" id="chatForm">
        <input type="file" id="chatImage" accept="image/jpeg,image/png,image/webp" class="d-none">
        <button type="button" class="btn btn-light rounded-circle" onclick="document.getElementById('chatImage').click()" aria-label="Send photo"><i class="fa-regular fa-image"></i></button>
        <textarea class="form-control" id="chatText" rows="1" placeholder="Type a message…" maxlength="3000"></textarea>
        <button class="btn btn-ek rounded-circle" style="width:44px;height:44px" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
const THREAD_ID = <?= (int)$thread['id'] ?>, LAST_ID = <?= (int)(end($messages)['id'] ?? 0) ?>;
let lastId = LAST_ID;
const wrap = document.getElementById('chatMsgs');
function esc(s){ const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function addBubble(m) {
  const el = document.createElement('div');
  el.className = 'bubble ' + (m.me ? 'me' : 'them');
  el.innerHTML = (m.image ? `<img src="${m.image}" alt="">` : '') + (m.body ? `<div>${esc(m.body)}</div>` : '')
    + `<div class="time">${m.time}${m.me ? ' <i class="fa-solid fa-check-double" style="font-size:.6rem"></i>' : ''}</div>`;
  wrap.appendChild(el);
  wrap.scrollTop = wrap.scrollHeight;
}
wrap.scrollTop = wrap.scrollHeight;
function poll() {
  ekGet('<?= url('/api/chat/' . $thread['id'] . '/poll') ?>?after=' + lastId).then(r => {
    if (!r.ok) return;
    r.messages.forEach(m => { lastId = Math.max(lastId, m.id); addBubble(m); });
    document.getElementById('presence').innerHTML = r.online
      ? '<span class="text-success"><i class="fa-solid fa-circle" style="font-size:.45rem"></i> online</span>'
      : 'last seen recently';
  });
}
poll(); setInterval(poll, 4000);
document.getElementById('chatForm').addEventListener('submit', e => {
  e.preventDefault();
  const text = document.getElementById('chatText').value.trim();
  const fileEl = document.getElementById('chatImage');
  const fd = new FormData();
  if (text) fd.append('body', text);
  if (fileEl.files[0]) fd.append('image', fileEl.files[0]);
  if (!text && !fileEl.files[0]) return;
  document.getElementById('chatText').value = '';
  fileEl.value = '';
  ekPost('<?= url('/chat/' . $thread['id'] . '/send') ?>', fd, true).then(r => { if (!r.ok) toast(r.message, 'danger'); else poll(); });
});
document.getElementById('chatText').addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); document.getElementById('chatForm').requestSubmit(); }
});
function blockUser() { ekPost('<?= url('/chat/' . $thread['id'] . '/block') ?>', {}).then(r => { toast(r.message); if (r.ok) location.reload(); }); }
function unblock() { location.reload(); }
function reportChat() {
  ekPost('<?= url('/chat/' . $thread['id'] . '/report') ?>', { reason: 'harassment', details: 'Reported from chat' }).then(r => toast(r.message, r.ok ? 'success' : 'danger'));
}
</script>
