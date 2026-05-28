<?php
declare(strict_types=1);

//leitura de um utilizador por login
function fetch_user_by_login(PDO $pdo, string $login): ?array{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM utilizadores
         WHERE username = :username OR email = :email
         LIMIT 1'
    );
    $stmt->execute([
        'username' => $login,
        'email' => $login,
    ]);

    return $stmt->fetch() ?: null;
}
