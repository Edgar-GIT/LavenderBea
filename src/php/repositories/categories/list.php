<?php
declare(strict_types=1);

function fetch_categories(PDO $pdo): array{
    $stmt = $pdo->query('SELECT * FROM vw_categorias ORDER BY nome ASC');
    return $stmt->fetchAll();
}
