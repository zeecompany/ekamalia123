<?php
/**
 * eKamalia — PHP built-in server router (dev/testing only; Apache uses .htaccess).
 * Usage: php -S 0.0.0.0:8080 router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;
/* serve real static files directly */
if ($path !== '/' && is_file($file)) return false;
/* installer mirrors the .htaccess rule */
if ($path === '/install' || str_starts_with($path, '/install/')) {
    if (is_file(__DIR__ . $path)) return false;
    require __DIR__ . '/install/index.php';
    return true;
}
require __DIR__ . '/index.php';
