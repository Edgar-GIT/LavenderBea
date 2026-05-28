<?php
declare(strict_types=1);

//mapa produto => quantidade
function fetch_cart_map(PDO $pdo, int $userId): array{
    $stmt = $pdo->prepare(
        'SELECT produto_id, quantidade
         FROM vw_carrinho_produtos
         WHERE utilizador_id = :utilizador_id
         ORDER BY updated_at DESC, id DESC'
    );
    $stmt->execute(['utilizador_id' => $userId]);

    $cart = [];

    foreach ($stmt->fetchAll() as $item) {
        $cart[(int) $item['produto_id']] = max(1, (int) $item['quantidade']);
    }

    return $cart;
}

