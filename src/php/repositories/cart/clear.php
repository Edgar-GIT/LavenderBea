<?php
declare(strict_types=1);

//limpar cesto do utilizador
function clear_cart_rows(PDO $pdo, int $userId): void{
    $stmt = $pdo->prepare(
        'DELETE FROM carrinho_itens
         WHERE utilizador_id = :utilizador_id'
    );
    $stmt->execute(['utilizador_id' => $userId]);
}

