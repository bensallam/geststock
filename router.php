<?php
/**
 * PHP built-in server router.
 * Usage (dev only): php -S localhost:8080 router.php
 * Production: use nginx.conf with PHP-FPM (Nginx handles routing natively).
 */

$uri = $_SERVER['REQUEST_URI'];

// Strip query string for file existence check
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files (CSS, JS, images) directly
$staticFile = __DIR__ . '/public' . $path;
if (preg_match('#^/public/#', $path) && file_exists($staticFile)) {
    return false; // let built-in server handle it
}

// Everything else → index.php with route parameter
$route = ltrim($path, '/');
$_GET['route'] = $route;

require __DIR__ . '/index.php';
