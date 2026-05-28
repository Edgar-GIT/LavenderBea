<?php
declare(strict_types=1);

function set_product_featured(PDO $pdo, int $productId, bool $featured): void{
    $stmt = $pdo->prepare(
        'UPDATE produtos SET destaque = :destaque WHERE id = :id'
    );
    $stmt->execute([
        'destaque' => $featured ? 1 : 0,
        'id' => $productId,
    ]);
}
