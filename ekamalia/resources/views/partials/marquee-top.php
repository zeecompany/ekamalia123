<?php
/** Top announcement marquee (admin controlled) */
$announcements = qa('SELECT text,url FROM marquees WHERE status="active" AND type="announcement" AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) ORDER BY sort_order LIMIT 10');
if ($announcements):
    $items = array_merge($announcements, $announcements); // seamless loop
?>
<div class="ek-marquee-top" role="marquee" aria-label="Announcements">
  <div class="mq-track">
    <?php foreach ($items as $m): ?>
      <span class="mq-item">
        <i class="fa-solid fa-bullhorn" style="opacity:.75"></i>
        <?php if ($m['url']): ?><a href="<?= e($m['url']) ?>"><?= e($m['text']) ?></a><?php else: ?><span><?= e($m['text']) ?></span><?php endif; ?>
        <span style="opacity:.4">•</span>
      </span>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
