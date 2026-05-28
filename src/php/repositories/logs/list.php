<?php
declare(strict_types=1);

//listagem de logs do admin
function fetch_admin_logs(PDO $pdo, int $limit = 80): array{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM vw_logs_admin
         ORDER BY created_at DESC, id DESC
         LIMIT :limit_count'
    );
    $stmt->bindValue('limit_count', max(1, $limit), PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

