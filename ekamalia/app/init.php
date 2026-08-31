<?php
/**
 * eKamalia — application bootstrap.
 * Loads config, connects DB, prepares session/auth/security basics.
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');           // never show PHP errors to visitors
ini_set('log_errors', '1');

define('EK_INSTALLED_LOCK', EK_ROOT . '/storage/install.lock');
define('EK_CONFIG_FILE', EK_ROOT . '/config/config.php');
define('EK_IN_INSTALLER', stripos($_SERVER['SCRIPT_NAME'] ?? '', '/install/') !== false);

/* ---------- autoloader (App\*) ---------- */
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $rel = str_replace('\\', '/', substr($class, 4));
        $file = EK_ROOT . '/app/' . $rel . '.php';
        if (is_file($file)) require $file;
    }
});

require EK_ROOT . '/app/helpers.php';

/* ---------- error/exception handlers ---------- */
set_exception_handler(function (Throwable $e): void {
    error_log('[EK] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (EK_IN_INSTALLER) { http_response_code(500); echo 'Installer error: ' . e($e->getMessage()); return; }
    http_response_code(500);
    try { view('errors/500', [], 'layouts/main'); } catch (Throwable $t) { echo 'Server error. Please try again later.'; }
    exit;
});
set_error_handler(function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return true;
    error_log("[EK PHP] $str @ $file:$line");
    return true; // suppress output, keep running (production-safe)
});

/* ---------- config ---------- */
$GLOBALS['ek_config'] = is_file(EK_CONFIG_FILE) ? require EK_CONFIG_FILE : null;

if (!EK_IN_INSTALLER) {
    if (!$GLOBALS['ek_config']) {                       // not installed yet
        header('Location: ' . base_path() . '/install'); exit;
    }
    if (!is_file(EK_INSTALLED_LOCK)) {                  // config exists but install incomplete
        header('Location: ' . base_path() . '/install'); exit;
    }
}

date_default_timezone_set(config('app.timezone', 'Asia/Karachi'));
mb_internal_encoding('UTF-8');

/* ---------- security headers ---------- */
if (!EK_IN_INSTALLER && PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    if (config('app.env', 'production') === 'production') {
        ini_set('session.cookie_httponly', '1');
    }
}

/* ---------- secure session ---------- */
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 14,
        'path'     => base_path() ?: '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('ekamalia_sess');
    session_start();
}

/* ---------- maintenance gate (admins bypass) ---------- */
if (!EK_IN_INSTALLER && setting('maintenance_mode', '0') === '1' && PHP_SAPI !== 'cli') {
    $path = current_path();
    $allowed = ['/login', '/admin', '/admin/login'];
    $isAdmin = auth() && auth()['role'] === 'admin';
    if (!$isAdmin && !in_array($path, $allowed, true) && !str_starts_with($path, '/admin')) {
        http_response_code(503);
        view('errors/503', ['message' => setting('maintenance_message', 'We are upgrading eKamalia. Back soon! In Sha Allah.')], 'layouts/main');
        exit;
    }
}

/* ---------- CSRF verification for every POST ---------- */
if (!EK_IN_INSTALLER && PHP_SAPI !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!csrf_verify(is_string($token) ? $token : '')) {
        if (is_ajax()) { json_out(['ok' => false, 'message' => 'Session expired. Please refresh the page.'], 419); }
        http_response_code(419);
        view('errors/419', [], 'layouts/main');
        exit;
    }
}
