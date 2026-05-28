<?php
declare(strict_types=1);

function delete_user_row(PDO $pdo, int $userId): void{
    $stmt = $pdo->prepare('DELETE FROM utilizadores WHERE id = :id');
    $stmt->execute(['id' => $userId]);
}
