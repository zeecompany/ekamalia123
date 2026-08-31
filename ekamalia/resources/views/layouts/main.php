<?php
/** Layout: frontend (header + content + footer + mobile nav) */
$seo = array_merge(seo_defaults(), $seo ?? []);
$flashMsg = flash();
$me = auth();
$citiesTop = qa('SELECT id,name,slug,is_primary FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order LIMIT 30');
$mainCats = qa('SELECT name,slug,icon FROM categories WHERE parent_id IS NULL AND type IN ("both","ad","product") AND status="active" ORDER BY sort_order LIMIT 14');
$footerPages = qa('SELECT title,slug FROM pages WHERE status="active" AND show_in_footer=1 ORDER BY id LIMIT 8');
$unreadNotifs = $me ? (int)qv('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$me['id']]) : 0;
$unreadMsgs = $me ? (int)qv('SELECT COALESCE(SUM(CASE WHEN seller_id=? THEN buyer_unread ELSE seller_unread END),0) FROM message_threads WHERE (buyer_id=? OR seller_id=?)', [$me['id'], $me['id'], $me['id']]) : 0;
$cartCount = App\Services\Cart::count();
$myShopExists = $me && qv('SELECT COUNT(*) FROM shops WHERE user_id=? AND status="approved"', [$me['id']]);
?>
<!doctype html>
<html lang="<?= lang() === 'ur' ? 'ur' : 'en' ?>" dir="<?= lang_dir() ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($seo['title']) ?></title>
<meta name="description" content="<?= e($seo['description']) ?>">
<meta name="keywords" content="<?= e($seo['keywords'] ?? '') ?>">
<link rel="canonical" href="<?= e($seo['canonical']) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(setting('site_name', 'eKamalia')) ?>">
<meta property="og:title" content="<?= e($seo['title']) ?>">
<meta property="og:description" content="<?= e($seo['description']) ?>">
<meta property="og:image" content="<?= e($seo['og_image']) ?>">
<meta property="og:url" content="<?= e($seo['canonical']) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="theme-color" content="<?= e(setting('app_theme_color', '#0D8244')) ?>">
<link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/logo.png') ?>">
<link rel="manifest" href="<?= url('manifest.json') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<?php if (lang() === 'ur'): ?><link href="<?= asset('css/app-rtl.css') ?>" rel="stylesheet"><?php endif; ?>
<?= $pageJsonLd ?? '' ?>
</head>
<body>
<?php include views_path('partials/marquee-top'); ?>
<?php include views_path('partials/header'); ?>

<main id="main"><?= $content ?></main>

<?php include views_path('partials/footer'); ?>
<?php include views_path('partials/mobile-nav'); ?>
<?php if (!empty($reportModal)) include views_path('partials/report-modal'); ?>

<div class="toast-ek"></div>
<?php if ($flashMsg): ?>
<script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($flashMsg['message']) ?>, <?= json_encode($flashMsg['type']) ?>));</script>
<?php endif; ?>

<script>window.EK_LOGGED_IN = <?= $me ? 'true' : 'false' ?>; window.EK_BASE = <?= json_encode(base_path()) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (setting('pwa_enabled', '1') === '1'): ?>
<script>if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('<?= url('service-worker.js') ?>').catch(()=>{}));</script>
<?php endif; ?>
</body>
</html>
