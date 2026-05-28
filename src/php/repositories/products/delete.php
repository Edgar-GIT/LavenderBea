<?php
declare(strict_types=1);

function delete_product_row(PDO $pdo, int $productId): void{
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
    $stmt->execute(['id' => $productId]);
}
