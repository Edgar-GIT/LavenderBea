<?php
declare(strict_types=1);

//listagem de produtos via filtro
function fetch_products(PDO $pdo, array $filters = []): array
{
    $sql = [
        'SELECT p.*
         FROM vw_produtos_catalogo p
         WHERE 1 = 1',
    ];

    $params = [];

    if (!empty($filters['featured'])) {
        $sql[] = 'AND p.destaque = 1';
    }

    if (!empty($filters['category_slug'])) {
        $sql[] = 'AND p.categoria_slug = :category_slug';
        $params['category_slug'] = $filters['category_slug'];
    }

    if (!empty($filters['search'])) {
        $sql[] = 'AND (
            p.nome LIKE :search_name
            OR p.codigo LIKE :search_code
            OR CAST(p.id AS CHAR) LIKE :search_id
        )';
        $search = '%' . $filters['search'] . '%';
        $params['search_name'] = $search;
        $params['search_code'] = $search;
        $params['search_id'] = $search;
    }

    $sql[] = 'ORDER BY p.destaque DESC, p.created_at DESC, p.id DESC';

    if (!empty($filters['limit'])) {
        $sql[] = 'LIMIT ' . (int) $filters['limit'];
    }

    $stmt = $pdo->prepare(implode(' ', $sql));
    $stmt->execute($params);

    return $stmt->fetchAll();
}
