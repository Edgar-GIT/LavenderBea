<?php
declare(strict_types=1);

//produtos relacionados pela categoria
function fetch_related_products(PDO $pdo, int $categoryId, int $excludeId, int $limit = 4): array
{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM vw_produtos_catalogo
         WHERE categoria_id = :category_id AND id <> :exclude_id
         ORDER BY destaque DESC, created_at DESC, id DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([
        'category_id' => $categoryId,
        'exclude_id' => $excludeId,
    ]);

    return $stmt->fetchAll();
}
