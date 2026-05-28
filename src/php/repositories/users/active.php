<?php
declare(strict_types=1);

//alteracao do estado da conta
function set_user_active(PDO $pdo, int $userId, bool $active): void
{
    $stmt = $pdo->prepare(
        'UPDATE utilizadores SET ativo = :ativo WHERE id = :id'
    );
    $stmt->execute([
        'ativo' => $active ? 1 : 0,
        'id' => $userId,
    ]);
}
