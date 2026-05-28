<?php
declare(strict_types=1);

//verificar favorito
function favorite_exists(PDO $pdo, int $userId, int $productId): bool{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM vw_favoritos_produtos
         WHERE utilizador_id = :utilizador_id
           AND produto_id = :produto_id
         LIMIT 1'
    );
    $stmt->execute([
        'utilizador_id' => $userId,
        'produto_id' => $productId,
    ]);

    return (bool) $stmt->fetch();
}
