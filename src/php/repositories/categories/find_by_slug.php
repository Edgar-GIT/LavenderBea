<?php
declare(strict_types=1);

function fetch_category_by_slug(PDO $pdo, string $slug): ?array{
    $stmt = $pdo->prepare('SELECT * FROM vw_categorias WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);

    return $stmt->fetch() ?: null;
}
