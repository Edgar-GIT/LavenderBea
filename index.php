<?php
declare(strict_types=1);

// Extract the path component only — strip query string so /shop?id=5 → /shop
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = '/' . trim((string) $uri, '/');

// Route map: URL path → page file under /src/php/pages/
const ROUTES = [
    '/'          => 'home.php',
    '/home'      => 'home.php',
    '/shop'      => 'products.php',   // /shop maps to the products catalogue
    '/products'  => 'products.php',
    '/product'   => 'product.php',
    '/account'   => 'account.php',
    '/admin'     => 'admin.php',
    '/favorites' => 'favorites.php',
    '/auth'      => 'auth.php',
    '/login'     => 'auth.php',
    '/cart'      => 'cart.php',
    '/make-piece'=> 'make-piece.php',
    '/site'      => 'home.php',       // /site falls back to home
    '/logout'    => 'logout.php',
];

$page = ROUTES[$path] ?? null;

if ($page !== null) {
    require __DIR__ . '/src/php/pages/' . $page;
} else {
    // Unknown path — serve the home page
    require __DIR__ . '/src/php/pages/home.php';
}
