<?php
declare(strict_types=1);

//listagem de utilizadores
function fetch_users(PDO $pdo): array{
    $stmt = $pdo->query('SELECT * FROM vw_utilizadores_admin ORDER BY created_at DESC, id DESC');
    return $stmt->fetchAll();
}
