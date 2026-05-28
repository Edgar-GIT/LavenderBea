<?php
declare(strict_types=1);

//leituras especificas produtos
function fetch_product(PDO $pdo, int $productId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM vw_produtos_catalogo
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $productId]);

    return $stmt->fetch() ?: null;
}
