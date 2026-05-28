<?php
/**
 * Router for PHP built-in development server.
 *
 * Serves static files with correct MIME types so that CSS, JavaScript,
 * and media assets are not incorrectly served as text/html.
 * Falls back to index.php for all PHP application routing.
 */

$mimeTypes = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2'=> 'font/woff2',
    'ttf'  => 'font/ttf',
    'otf'  => 'font/otf',
    'pdf'  => 'application/pdf',
    'json' => 'application/json',
    'xml'  => 'application/xml',
    'txt'  => 'text/plain',
];

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath    = __DIR__ . $requestPath;

// If the file exists on disk and is not a directory, serve it directly.
if (is_file($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($filePath);
        return true;
    }

    // For any other known static file, let the built-in server handle it.
    return false;
}

// No static file matched — hand off to the application entry point.
require __DIR__ . '/index.php';
