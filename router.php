<?php
declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

$file = __DIR__ . $uri;

if (is_file($file)) {
    return false;
}

// remove "/" inicial e final
$route = trim($uri, '/');

// home
if ($route === '') {
    $route = 'home';
}

// passa a rota para o sistema
$_GET['page'] = $route;

require __DIR__ . '/index.php';
?>