<?php
declare(strict_types=1);

//criacao ou edicao de utilizadores
function save_user(PDO $pdo, array $data, ?int $userId = null): int
{
    if ($userId) {
        $sql = 'UPDATE utilizadores
                SET nome = :nome,
                    username = :username,
                    email = :email,
                    telemovel = :telemovel,
                    role = :role,
                    ativo = :ativo';

        $params = [
            'nome' => $data['nome'],
            'username' => $data['username'],
            'email' => $data['email'],
            'telemovel' => $data['telemovel'] ?: null,
            'role' => $data['role'],
            'ativo' => $data['ativo'],
            'id' => $userId,
        ];

        if (!empty($data['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }

        $sql .= ' WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $userId;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO utilizadores
            (nome, username, email, telemovel, password_hash, role, ip_registo, ativo)
         VALUES
            (:nome, :username, :email, :telemovel, :password_hash, :role, :ip_registo, :ativo)'
    );

    $stmt->execute([
        'nome' => $data['nome'],
        'username' => $data['username'],
        'email' => $data['email'],
        'telemovel' => $data['telemovel'] ?: null,
        'password_hash' => $data['password_hash'],
        'role' => $data['role'],
        'ip_registo' => $data['ip_registo'] ?: null,
        'ativo' => $data['ativo'],
    ]);

    return (int) $pdo->lastInsertId();
}
