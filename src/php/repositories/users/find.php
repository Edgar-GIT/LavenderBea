<?php
declare(strict_types=1);

//leitura de um utilizador especifico
function fetch_user(PDO $pdo, int $userId): ?array{
    $stmt = $pdo->prepare('SELECT * FROM utilizadores WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);

    return $stmt->fetch() ?: null;
}
