<?php
declare(strict_types=1);

//alternar favorito
function toggle_favorite_row(PDO $pdo, int $userId, int $productId): bool{
    if (favorite_exists($pdo, $userId, $productId)) {
        $stmt = $pdo->prepare(
            'DELETE FROM favoritos
             WHERE utilizador_id = :utilizador_id
               AND produto_id = :produto_id'
        );
        $stmt->execute([
            'utilizador_id' => $userId,
            'produto_id' => $productId,
        ]);

        return false;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO favoritos (utilizador_id, produto_id)
         VALUES (:utilizador_id, :produto_id)'
    );
    $stmt->execute([
        'utilizador_id' => $userId,
        'produto_id' => $productId,
    ]);

    return true;
}

