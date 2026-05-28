<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';

//verificacao de metodo
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido.';
    exit;
}
//verificacao login, produto ainda existe, e favoritar/desfavoritar
try {
    if (!is_logged_in($pdo)) {
        http_response_code(401);
        echo 'Tens de iniciar sessão para usar os favoritos.';
        exit;
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $product = fetch_product($pdo, $productId);

    if (!$product) {
        throw new RuntimeException('O produto já não existe.');
    }

    $isFavorite = toggle_favorite_product($pdo, $productId);
    echo $isFavorite ? 'added' : 'removed';
} 
catch (Throwable $error) {
    http_response_code(422);
    echo $error->getMessage();
}
