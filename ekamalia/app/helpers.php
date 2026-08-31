<?php
/**
 * eKamalia — global helpers (views + controllers use these daily).
 * Security-sensitive helpers are commented inline.
 */
declare(strict_types=1);

/* ================= config / paths ================= */

function config(string $key, $default = null) {
    $parts = explode('.', $key);
    $val = $GLOBALS['ek_config'] ?? null;
    foreach ($parts as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) return $default;
        $val = $val[$p];
    }
    return $val;
}

function base_path(): string {
    static $bp = null;
    if ($bp === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if (str_ends_with($dir, '/install')) $dir = substr($dir, 0, -8);
        $bp = $dir === '' ? '' : $dir;
    }
    return $bp;
}

function url(string $path = ''): string { return base_path() . '/' . ltrim($path, '/'); }
function asset(string $path): string { return url('assets/' . ltrim($path, '/')); }
function upload_url(string $rel): string { return $rel ? url('uploads/' . ltrim($rel, '/')) : ''; }
function routes_path(string $f): string { return EK_ROOT . '/routes/' . $f; }
function views_path(string $f): string { return EK_ROOT . '/resources/views/' . $f . '.php'; }

function current_path(): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $bp = base_path();
    if ($bp && str_starts_with($uri, $bp)) $uri = substr($uri, strlen($bp));
    return '/' . ltrim($uri, '/');
}

/* ================= output escaping ================= */

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function e_nullable($value): string { return e($value); }

/* ================= database (PDO, prepared only) ================= */

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $c = config('db');
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $c['host'], $c['name'], $c['charset'] ?? 'utf8mb4');
        try {
            $pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('[EK DB] ' . $e->getMessage());
            http_response_code(500);
            if (is_ajax()) { json_out(['ok' => false, 'message' => 'Database unavailable'], 500); }
            echo 'Database connection problem. Please try again shortly.';
            exit;
        }
    }
    return $pdo;
}

/** Run a prepared statement and return it. */
function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
/** Fetch all rows. */
function qa(string $sql, array $params = []): array { return q($sql, $params)->fetchAll(); }
/** Fetch first row or null. */
function q1(string $sql, array $params = []): ?array {
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}
/** Fetch scalar value. */
function qv(string $sql, array $params = [], $default = null) {
    $r = q($sql, $params)->fetchColumn();
    return $r === false ? $default : $r;
}
function last_id(): int { return (int)db()->lastInsertId(); }
function now(): string { return date('Y-m-d H:i:s'); }

/* ================= settings (DB-driven, cached) ================= */

function settings_all(): array {
    static $s = null;
    if ($s === null) {
        $s = [];
        try { foreach (qa('SELECT `key`,`value` FROM settings') as $r) $s[$r['key']] = $r['value']; } catch (Throwable $t) {}
    }
    return $s;
}
function setting(string $key, $default = null) {
    $s = settings_all();
    return array_key_exists($key, $s) && $s[$key] !== '' ? $s[$key] : $default;
}
function setting_save(string $key, $value): void {
    q('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$key, (string)$value]);
}
function settings_save_many(array $kv): void { foreach ($kv as $k => $v) setting_save($k, $v); }

/* ================= request helpers ================= */

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function is_ajax(): bool {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}
function input(string $key, $default = null) {
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    return ($v === null || $v === '') ? $default : $v;
}
function str_input(string $key, string $default = '', int $max = 65535): string {
    $v = trim((string)($_POST[$key] ?? $_GET[$key] ?? $default));
    return mb_substr($v, 0, $max);
}
function int_input(string $key, int $default = 0): int { return (int)input($key, $default); }
function float_input(string $key, float $default = 0.0): float { return (float)input($key, $default); }
function client_ip(): string {
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}
function user_agent(): string { return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255); }

function redirect(string $path): never {
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}
function back(string $fallback = '/'): never {
    $ref = $_SERVER['HTTP_REFERER'] ?? null;
    if ($ref && str_starts_with($ref, url())) { header('Location: ' . $ref); exit; }
    redirect($fallback);
}

/* ================= JSON responses ================= */

function json_out(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(array $data = []): never { json_out(['ok' => true] + $data); }
function json_fail(string $message, int $status = 400, array $extra = []): never { json_out(['ok' => false, 'message' => $message] + $extra, $status); }

/* ================= CSRF ================= */

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'; }
function csrf_verify(string $token): bool {
    return !empty($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
}

/* ================= auth ================= */

function auth(): ?array {
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $user = q1('SELECT * FROM users WHERE id=? AND status="active" AND deleted_at IS NULL', [$_SESSION['user_id']]);
            if (!$user) unset($_SESSION['user_id']);
        }
    }
    return $user;
}
function user_id(): int { return (int)(auth()['id'] ?? 0); }
function is_admin(): bool { return (auth()['role'] ?? '') === 'admin'; }

function require_login(): array {
    $u = auth();
    if (!$u) {
        if (is_ajax()) json_fail('Please login first', 401);
        $_SESSION['intended'] = current_path() . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        flash('warning', 'Please login to continue.');
        redirect('/login');
    }
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') {
        if (is_ajax()) json_fail('Unauthorized', 403);
        http_response_code(403);
        view('errors/403', [], 'layouts/main'); exit;
    }
    return $u;
}
/** Shop owned by current user (approved). */
function my_shop(?int $shopId = null): ?array {
    $u = auth(); if (!$u) return null;
    $sql = 'SELECT * FROM shops WHERE user_id=? AND status="approved" AND deleted_at IS NULL';
    $p = [$u['id']];
    if ($shopId) { $sql .= ' AND id=?'; $p[] = $shopId; }
    $shop = q1($sql . ' LIMIT 1', $p);
    return $shop;
}
function require_shop(): array {
    $shop = my_shop();
    if (!$shop) {
        flash('warning', 'You need an approved shop first.');
        redirect('/shops/create');
    }
    return $shop;
}

/* ================= POS access ================= */

function pos_access(): ?array {
    static $pos = false;
    if ($pos === false) {
        $pos = null;
        $uid = user_id();
        if ($uid) $pos = q1('SELECT * FROM pos_users WHERE user_id=? AND status="active" LIMIT 1', [$uid]);
    }
    return $pos;
}
function require_pos(): array {
    $pos = pos_access();
    if (!$pos) {
        flash('warning', 'POS access is required. Please request POS access.');
        redirect('/pos/request');
    }
    return $pos;
}
function pos_can(array $pos, string $perm): bool {
    if ($pos['role'] === 'owner') return true;
    $perms = json_decode($pos['permissions'] ?? '[]', true) ?: [];
    return in_array($perm, $perms, true);
}
function require_pos_perm(string $perm): array {
    $pos = require_pos();
    if (!pos_can($pos, $perm)) { flash('danger', 'You do not have permission for that action.'); redirect('/pos'); }
    return $pos;
}

/* ================= flash messages / old input ================= */

function flash(?string $type = null, ?string $message = null) {
    if ($type === null) {
        $f = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $f;
    }
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}
function flash_all(): array {
    $f = $_SESSION['_flash_multi'] ?? [];
    unset($_SESSION['_flash_multi']);
    return $f;
}
function old(string $key, $default = '') { return e($_SESSION['_old'][$key] ?? $default); }
function stash_old(): void {
    $_SESSION['_old'] = array_map(fn($v) => is_string($v) ? $v : '', $_POST);
    unset($_SESSION['_old']['password'], $_SESSION['_old']['password_confirmation']);
}
function clear_old(): void { unset($_SESSION['_old']); }

/* ================= i18n ================= */

function lang(): string { return $_SESSION['lang'] ?? 'en'; }
function lang_dir(): string { return lang() === 'ur' ? 'rtl' : 'ltr'; }
function t(string $key): string {
    static $lines = null;
    if ($lines === null) {
        $en = require EK_ROOT . '/resources/lang/en.php';
        $lines = $en;
        $ur = EK_ROOT . '/resources/lang/' . lang() . '.php';
        if (lang() !== 'en' && is_file($ur)) $lines = array_merge($en, require $ur);
    }
    return $lines[$key] ?? $key;
}

/* ================= formatting ================= */

function money($amount, bool $symbol = true): string {
    $n = number_format((float)$amount, ((float)$amount == (int)(float)$amount) ? 0 : 2);
    return ($symbol ? e(setting('currency_symbol', 'Rs ')) : '') . $n;
}
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim(mb_strtolower($text), '-');
    $text = preg_replace('~-+~', '-', $text);
    return ($text ?: 'item') . '-' . substr(bin2hex(random_bytes(3)), 0, 5);
}
function unique_slug(string $table, string $title): string {
    $slug = slugify($title);
    $i = 1;
    while (qv("SELECT id FROM `$table` WHERE slug=? LIMIT 1", [$slug])) $slug = slugify($title) . '-' . (++$i > 2 ? $i : '');
    return $slug;
}
function time_ago(?string $dt): string {
    if (!$dt) return '';
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($dt));
}
function fmt_date(?string $dt, string $fmt = 'd M Y, h:i A'): string {
    return $dt ? date($fmt, strtotime($dt)) : '—';
}
/** Normalize Pakistani mobile to 03XX-XXXXXXX display */
function pk_phone(string $raw): string {
    $d = preg_replace('/\D+/', '', $raw);
    if (str_starts_with($d, '92')) $d = '0' . substr($d, 2);
    return strlen($d) === 11 ? substr($d, 0, 4) . '-' . substr($d, 4) : $raw;
}
function wa_link(string $phone, string $text = ''): string {
    $d = preg_replace('/\D+/', '', $phone);
    if (str_starts_with($d, '0')) $d = '92' . substr($d, 1);
    return 'https://wa.me/' . $d . ($text ? '?text=' . rawurlencode($text) : '');
}
function order_statuses(): array {
    return ['pending','confirmed','processing','packed','dispatched','out_for_delivery','delivered','completed','cancelled','returned','refund_requested','refunded'];
}
function order_status_label(string $s): string {
    return [
        'pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','packed'=>'Packed',
        'dispatched'=>'Dispatched','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered',
        'completed'=>'Completed','cancelled'=>'Cancelled','returned'=>'Returned',
        'refund_requested'=>'Refund Requested','refunded'=>'Refunded',
    ][$s] ?? ucfirst($s);
}
function status_badge(string $status): string {
    $map = [
        'active'=>'success','approved'=>'success','verified'=>'success','published'=>'success','delivered'=>'success','completed'=>'success','on'=>'success','paid'=>'success','open'=>'success',
        'pending'=>'warning','submitted'=>'warning','processing'=>'info','packed'=>'info','dispatched'=>'info','out_for_delivery'=>'info','held'=>'secondary','draft'=>'secondary','paused'=>'secondary','hidden'=>'secondary',
        'cancelled'=>'danger','rejected'=>'danger','suspended'=>'danger','banned'=>'danger','off'=>'danger','failed'=>'danger','sold'=>'secondary','expired'=>'secondary','returned'=>'secondary','refunded'=>'secondary',
        'refund_requested'=>'warning','confirmed'=>'primary','out_of_stock'=>'danger','maintenance'=>'warning','scheduled'=>'info',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $cls . '">' . e(ucfirst(str_replace('_',' ',$status))) . '</span>';
}

/* ================= uploads (validated + renamed) ================= */

function upload_image(string $field, string $dir, int $maxMb = 5, ?array $thumb = null): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed (code ' . $f['error'] . ').');
    if ($f['size'] > $maxMb * 1024 * 1024) throw new RuntimeException("File too large (max {$maxMb} MB).");
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !isset($allowed[$info['mime']])) throw new RuntimeException('Only JPG, PNG, WEBP images are allowed.');
    $ext = $allowed[$info['mime']];
    // extra guard: verify content really decodes as an image
    if (!@imagecreatefromstring(file_get_contents($f['tmp_name']))) throw new RuntimeException('Invalid image content.');
    $dirPath = EK_ROOT . '/uploads/' . trim($dir, '/');
    if (!is_dir($dirPath)) mkdir($dirPath, 0755, true);
    $name = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;   // random name, no user input
    if (!move_uploaded_file($f['tmp_name'], $dirPath . '/' . $name)) throw new RuntimeException('Could not save upload.');
    if ($thumb && function_exists('imagecreatetruecolor')) make_thumbnail($dirPath . '/' . $name, $thumb[0], $thumb[1]);
    return trim($dir, '/') . '/' . $name;
}
function make_thumbnail(string $path, int $w, int $h): void {
    $info = @getimagesize($path); if (!$info) return;
    [$srcW, $srcH] = $info;
    $mime = $info['mime'];
    $create = ['image/jpeg'=>'imagecreatefromjpeg','image/png'=>'imagecreatefrompng','image/webp'=>'imagecreatefromwebp','image/gif'=>'imagecreatefromgif'];
    if (!isset($create[$mime])) return;
    $src = @$create[$mime]($path); if (!$src) return;
    $scale = min($w / $srcW, $h / $srcH, 1);
    $nw = max(1, (int)($srcW * $scale)); $nh = max(1, (int)($srcH * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    if (in_array($mime, ['image/png','image/webp'], true)) { imagealphablending($dst, false); imagesavealpha($dst, true); }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $srcW, $srcH);
    match($mime) {
        'image/jpeg' => imagejpeg($dst, $path, 82),
        'image/png'  => imagepng($dst, $path, 6),
        'image/webp' => function_exists('imagewebp') ? imagewebp($dst, $path, 82) : null,
        default => null,
    };
    imagedestroy($src); imagedestroy($dst);
}
function delete_upload(?string $rel): void {
    if (!$rel) return;
    $path = EK_ROOT . '/uploads/' . ltrim($rel, '/');
    $real = realpath($path);
    if ($real && str_starts_with($real, realpath(EK_ROOT . '/uploads')) && is_file($real)) @unlink($real);
}

/* ================= misc domain helpers ================= */

/** Send a branded transactional email (OTP, orders, approvals). Failures are logged, never fatal. */
function send_app_mail(string $to, string $subject, string $body, string $urlPath = '', string $ctaText = 'Open eKamalia'): bool {
    try {
        return \App\Services\Mailer::send($to, $subject, \App\Services\Mailer::template($subject, '<p>' . nl2br(e($body)) . '</p>', $urlPath ? url($urlPath) : '', $ctaText));
    } catch (Throwable $t) { error_log('[EK Mail] ' . $t->getMessage()); return false; }
}

function img_or(?string $rel, string $placeholder = 'assets/img/placeholder.svg'): string {
    return $rel ? upload_url($rel) : url($placeholder);
}

function notify(int $userId, string $title, string $body, string $type = 'general', string $urlPath = ''): void {
    if (!$userId) return;
    q('INSERT INTO notifications (user_id,title,body,type,url,created_at) VALUES (?,?,?,?,?,?)',
      [$userId, $title, $body, $type, $urlPath, now()]);
    // critical types also trigger email (best-effort, SMTP errors logged not fatal)
    if (in_array($type, ['order','payment','pos','shop'], true)) {
        $email = qv('SELECT email FROM users WHERE id=?', [$userId]);
        if ($email) send_app_mail($email, $title, $body, $urlPath);
    }
}
function notify_admins(string $title, string $body, string $type = 'admin', string $urlPath = ''): void {
    foreach (qa('SELECT id FROM users WHERE role="admin" AND status="active"') as $a) {
        q('INSERT INTO notifications (user_id,title,body,type,url,is_read,created_at) VALUES (?,?,?,?,?,0,?)',
          [$a['id'], $title, $body, $type, $urlPath, now()]);
    }
}

function audit_log(string $action, string $targetType = '', int $targetId = 0, string $details = ''): void {
    $uid = user_id();
    q('INSERT INTO audit_logs (user_id,action,target_type,target_id,details,ip,user_agent,created_at) VALUES (?,?,?,?,?,?,?,?)',
      [$uid, $action, $targetType, $targetId, mb_substr($details, 0, 500), client_ip(), user_agent(), now()]);
}

/** Simple rate limiter backed by login_attempts-style table (identifier + ip). */
function rate_limited(string $bucket, string $identifier, int $max, int $windowSec): bool {
    $count = qv('SELECT COUNT(*) FROM rate_limits WHERE bucket=? AND identifier=? AND created_at > (NOW() - INTERVAL ? SECOND)',
        [$bucket, $bucket . '|' . $identifier, $windowSec], 0);
    return (int)$count >= $max;
}
function rate_hit(string $bucket, string $identifier): void {
    q('INSERT INTO rate_limits (bucket,identifier,created_at) VALUES (?,?,?)', [$bucket, $bucket . '|' . $identifier, now()]);
    q('DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 86400 SECOND)');
}
function rate_clear(string $bucket, string $identifier): void {
    q('DELETE FROM rate_limits WHERE bucket=? AND identifier=?', [$bucket, $bucket . '|' . $identifier]);
}

/** Log a search term for admin analytics. */
function log_search(string $term, int $results): void {
    $term = trim(mb_substr($term, 0, 120));
    if ($term === '' || mb_strlen($term) < 2) return;
    q('INSERT INTO search_logs (query,results_count,user_id,created_at) VALUES (?,?,?,?)', [$term, $results, user_id(), now()]);
}

function per_page(): int { return max(5, min(60, (int)setting('per_page', 20))); }

/** Render pagination links. $total rows, current page from ?page */
function paginate(int $total, int $perPage): string {
    $page = max(1, int_input('page', 1));
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages <= 1) return '';
    $qs = $_GET; $links = '';
    for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
        $qs['page'] = $i; $active = $i === $page ? ' active' : '';
        $links .= '<li class="page-item' . $active . '"><a class="page-link" href="?' . e(http_build_query($qs)) . '#results">' . $i . '</a></li>';
    }
    return '<nav aria-label="Pagination"><ul class="pagination justify-content-center flex-wrap">'
        . ($page > 1 ? '<li class="page-item"><a class="page-link" href="?' . e(http_build_query(array_merge($qs, ['page' => $page - 1]))) . '">«</a></li>' : '')
        . $links
        . ($page < $pages ? '<li class="page-item"><a class="page-link" href="?' . e(http_build_query(array_merge($qs, ['page' => $page + 1]))) . '">»</a></li>' : '')
        . '</ul></nav>';
}

/* ================= views ================= */

function view(string $tpl, array $data = [], ?string $layout = 'layouts/main'): void {
    extract($data, EXTR_SKIP);
    ob_start();
    require views_path($tpl);
    $content = ob_get_clean();
    if ($layout === null) { echo $content; return; }
    require views_path($layout);
}

function seo_defaults(): array {
    return [
        'title'       => setting('seo_title', setting('site_name', 'eKamalia') . ' — ' . setting('site_tagline', 'Kamalia Ka Apna Digital Bazaar')),
        'description' => setting('seo_description', 'Buy and sell in Kamalia. Classified ads, online shops, business directory, bijli updates and more — Kamalia ka apna digital bazaar.'),
        'keywords'    => setting('seo_keywords', 'kamalia, olx kamalia, kamalia bazaar, toba tek singh, classified pakistan'),
        'og_image'    => setting('og_image') ? upload_url(setting('og_image')) : asset('img/og-image.jpg'),
        'canonical'   => url(str_starts_with(current_path(), '/index.php') ? '/' : ltrim(current_path(), '/')),
    ];
}

/** 404 helper */
function not_found(): never {
    http_response_code(404);
    view('errors/404', [], 'layouts/main');
    exit;
}
