<?php
/**
 * eKamalia — Front Controller
 * Kamalia Ka Apna Digital Bazaar
 */
declare(strict_types=1);

define('EK_START', microtime(true));
define('EK_ROOT', __DIR__);

require __DIR__ . '/app/init.php';
require __DIR__ . '/app/router.php';

$router = require routes_path('web.php');
dispatch($router);
