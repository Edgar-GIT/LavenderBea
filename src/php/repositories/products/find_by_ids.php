<?php
declare(strict_types=1);

//ler produtos por ids
function fetch_products_by_ids(PDO $pdo, array $ids): array{
    $ids = array_values(array_unique(array_map('intval', $ids)));

    if ($ids === []) {
        return [];
    }

    $marks = implode(', ', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        'SELECT *
         FROM vw_produtos_catalogo
         WHERE id IN (' . $marks . ')'
    );
    $stmt->execute($ids);

    $items = [];

    foreach ($stmt->fetchAll() as $item) {
        $items[(int) $item['id']] = $item;
    }

    return $items;
}
