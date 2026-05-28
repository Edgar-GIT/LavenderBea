<?php
declare(strict_types=1);

// PHP built-in server router
// Serves static files directly with correct MIME types.
// Falls through to index.php for all dynamic application routes.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

// Let PHP handle directory listings / directory index resolution natively
if (is_dir($file)) {
    return false;
}

// Map extensions to MIME types
const MIME_TYPES = [
    'css'   => 'text/css; charset=UTF-8',
    'js'    => 'application/javascript; charset=UTF-8',
    'mp4'   => 'video/mp4',
    'webm'  => 'video/webm',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'svg'   => 'image/svg+xml; charset=UTF-8',
    'ico'   => 'image/x-icon',
    'webp'  => 'image/webp',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'otf'   => 'font/otf',
    'pdf'   => 'application/pdf',
    'json'  => 'application/json; charset=UTF-8',
    'xml'   => 'application/xml; charset=UTF-8',
    'txt'   => 'text/plain; charset=UTF-8',
];

$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

if (isset(MIME_TYPES[$ext]) && is_file($file)) {
    // Serve the static file directly with the correct Content-Type
    header('Content-Type: ' . MIME_TYPES[$ext]);
    readfile($file);
    return true;
}

// File does not exist or is not a recognised static type — hand off to the app
require __DIR__ . '/index.php';
