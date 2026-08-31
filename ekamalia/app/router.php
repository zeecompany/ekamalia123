<?php
/**
 * eKamalia — tiny but complete router.
 * Routes: ['GET|POST', '/path/{param}', 'Controller@method']
 */
declare(strict_types=1);

function load_routes(string $file): array {
    $routes = require $file;
    return is_array($routes) ? $routes : [];
}

function dispatch(array $routes): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = current_path();
    if ($path !== '/' ) $path = rtrim($path, '/') ?: '/';

    // strip /index.php if served without rewrite
    if (str_starts_with($path, '/index.php')) $path = '/' . trim(substr($path, 10), '/') ;
    if ($path === '//') $path = '/';

    $allowed = [];
    foreach ($routes as $route) {
        [$verbs, $pattern, $handler] = $route;
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#u';
        if (preg_match($regex, $path, $m)) {
            if (!str_contains($verbs, $method)) { $allowed = array_merge($allowed, explode('|', $verbs)); continue; }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $fn] = explode('@', $handler);
            $fqcn = 'App\\Controllers\\' . $class;
            if (!class_exists($fqcn)) { error_log("[EK] Missing controller $fqcn"); not_found(); }
            $controller = new $fqcn();
            if (!method_exists($controller, $fn)) { error_log("[EK] Missing method $fqcn::$fn"); not_found(); }
            $controller->{$fn}($params);
            return;
        }
    }
    if ($allowed) { http_response_code(405); view('errors/405', [], 'layouts/main'); exit; }
    not_found();
}
