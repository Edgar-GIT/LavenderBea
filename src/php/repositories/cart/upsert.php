<?php
declare(strict_types=1);

//adicionar quantidade ao cesto
function add_cart_item_row(PDO $pdo, int $userId, int $productId, int $quantity): void{
    $stmt = $pdo->prepare(
        'INSERT INTO carrinho_itens (utilizador_id, produto_id, quantidade)
         VALUES (:utilizador_id, :produto_id, :quantidade)
         ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)'
    );
    $stmt->execute([
        'utilizador_id' => $userId,
        'produto_id' => $productId,
        'quantidade' => max(1, $quantity),
    ]);
}

//definir quantidade exata
function set_cart_item_row(PDO $pdo, int $userId, int $productId, int $quantity): void{
    $stmt = $pdo->prepare(
        'INSERT INTO carrinho_itens (utilizador_id, produto_id, quantidade)
         VALUES (:utilizador_id, :produto_id, :quantidade)
         ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade)'
    );
    $stmt->execute([
        'utilizador_id' => $userId,
        'produto_id' => $productId,
        'quantidade' => max(1, $quantity),
    ]);
}

