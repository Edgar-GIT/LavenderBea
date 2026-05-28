<?php
declare(strict_types=1);

//ids favoritos do utilizador
function fetch_favorite_ids(PDO $pdo, int $userId): array{
    $stmt = $pdo->prepare(
        'SELECT produto_id
         FROM vw_favoritos_produtos
         WHERE utilizador_id = :utilizador_id
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute(['utilizador_id' => $userId]);

    return array_map('intval', array_column($stmt->fetchAll(), 'produto_id'));
}

