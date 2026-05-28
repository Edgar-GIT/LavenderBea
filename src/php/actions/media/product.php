<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';

$filename = basename((string) ($_GET['file'] ?? ''));

if ($filename === '' || $filename === '.' || $filename === '..') {
    http_response_code(404);
    exit;
}

$filePath = PRODUCT_UPLOAD_DIR . '/' . $filename;

if (!is_file($filePath)) {
    http_response_code(404);
    exit;
}

//deteta tipo ficheiro e envia para o browser
$mime = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime); //força modo renderizacao do browser
header('Content-Length: ' . (string) filesize($filePath)); //tamanho
header('Cache-Control: public, max-age=86400'); //cache 1 dia
readfile($filePath); //envia o ficheiro para o browser

exit;
?>