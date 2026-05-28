<?php
declare(strict_types=1);

//ligacao base dados
function db(): PDO{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $srvDsn = sprintf(
        'mysql:host=%s;port=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT
    );

    $server = new PDO($srvDsn, DB_USER, DB_PASS, $options);
    $server->exec(
        sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            DB_NAME
        )
    );

    $dbDsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $pdo = new PDO($dbDsn, DB_USER, DB_PASS, $options);

    ensure_schema($pdo);
    ensure_product_code_schema($pdo);
    ensure_triggers($pdo);
    ensure_views($pdo);
    ensure_admin_account($pdo);

    return $pdo;
}

//criar triggers
function ensure_triggers(PDO $pdo): void{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $triggers = file_get_contents(BASE_PATH . '/src/sql/triggers.sql');

    if ($triggers === false) {
        throw new RuntimeException('Não foi possível ler o ficheiro SQL das triggers.');
    }

    $pdo->exec($triggers);
    $loaded = true;
}

//criar as views
function ensure_views(PDO $pdo): void{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $views = file_get_contents(BASE_PATH . '/src/sql/views.sql');

    if ($views === false) {
        throw new RuntimeException('Não foi possível ler o ficheiro SQL das views.');
    }

    $pdo->exec($views);
    $loaded = true;
}

//criar tabelas
function ensure_schema(PDO $pdo): void{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $schema = file_get_contents(BASE_PATH . '/src/sql/lavender_bea.sql');

    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler o ficheiro SQL do projeto.');
    }

    $pdo->exec($schema);
    $loaded = true;
}

//garantia da coluna e dos valores do codigo publico dos produtos
function ensure_product_code_schema(PDO $pdo): void{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    try {
        $pdo->query('SELECT codigo FROM produtos LIMIT 1');
    } catch (Throwable) {
        $pdo->exec('ALTER TABLE produtos ADD COLUMN codigo VARCHAR(20) DEFAULT NULL AFTER id');
    }

    $indexStmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.statistics
         WHERE table_schema = :schema_name
           AND table_name = :table_name
           AND index_name = :index_name'
    );
    $indexStmt->execute([
        'schema_name' => DB_NAME,
        'table_name' => 'produtos',
        'index_name' => 'uq_produtos_codigo',
    ]);
    $indexRow = $indexStmt->fetch();

    if ((int) ($indexRow['total'] ?? 0) === 0) {
        $pdo->exec('ALTER TABLE produtos ADD UNIQUE KEY uq_produtos_codigo (codigo)');
    }

    ensure_product_codes($pdo);
    $loaded = true;
}

//garantir user admin
function ensure_admin_account(PDO $pdo): void{
    $stmt = $pdo->prepare(
        'SELECT id FROM utilizadores WHERE username = :username LIMIT 1'
    );
    $stmt->execute(['username' => ADMIN_USERNAME]);
    $admin = $stmt->fetch();

    if ($admin) {
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO utilizadores
            (nome, username, email, telemovel, password_hash, role, ip_registo, ativo)
         VALUES
            (:nome, :username, :email, :telemovel, :password_hash, :role, :ip_registo, :ativo)'
    );

    $insert->execute([
        'nome' => ADMIN_NAME,
        'username' => ADMIN_USERNAME,
        'email' => ADMIN_EMAIL,
        'telemovel' => null,
        'password_hash' => password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT),
        'role' => 'admin',
        'ip_registo' => '127.0.0.1',
        'ativo' => 1,
    ]);
}
