<?php
declare(strict_types=1);

//remover artigo do cesto
function delete_cart_item_row(PDO $pdo, int $userId, int $productId): void{
    $stmt = $pdo->prepare(
        'DELETE FROM carrinho_itens
         WHERE utilizador_id = :utilizador_id
           AND produto_id = :produto_id'
    );
    $stmt->execute([
        'utilizador_id' => $userId,
        'produto_id' => $productId,
    ]);
}

