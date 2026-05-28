<?php
declare(strict_types=1);

//leitura do alias da categoria para gerar o codigo
function fetch_product_category_slug(PDO $pdo, int $categoryId): string
{
    $stmt = $pdo->prepare('SELECT slug FROM categorias WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch();

    return (string) ($category['slug'] ?? '');
}

//geracao do proximo codigo publico do produto
function next_product_code(PDO $pdo, int $categoryId): string
{
    $prefix = product_code_prefix(fetch_product_category_slug($pdo, $categoryId));
    $stmt = $pdo->prepare('SELECT codigo FROM produtos WHERE codigo LIKE :prefix');
    $stmt->execute(['prefix' => $prefix . '-%']);

    $maxNumber = 0;

    foreach ($stmt->fetchAll() as $row) {
        $code = (string) ($row['codigo'] ?? '');

        if (!preg_match('/^[A-Z]{2}-([0-9]{3,})$/', $code, $match)) {
            continue;
        }

        $maxNumber = max($maxNumber, (int) $match[1]);
    }

    return $prefix . '-' . str_pad((string) ($maxNumber + 1), 3, '0', STR_PAD_LEFT);
}

//garantia de codigos publicos para produtos antigos
function ensure_product_codes(PDO $pdo): void{
    $stmt = $pdo->query(
        'SELECT id, categoria_id
         FROM produtos
         WHERE codigo IS NULL OR codigo = ""
         ORDER BY id ASC'
    );

    foreach ($stmt->fetchAll() as $product) {
        $code = next_product_code($pdo, (int) $product['categoria_id']);
        $update = $pdo->prepare('UPDATE produtos SET codigo = :codigo WHERE id = :id');
        $update->execute([
            'codigo' => $code,
            'id' => (int) $product['id'],
        ]);
    }
}

//criacao ou edicao de produto
function save_product(PDO $pdo, array $data, ?int $productId = null): int{
    if ($productId) {
        $codeStmt = $pdo->prepare('SELECT codigo FROM produtos WHERE id = :id LIMIT 1');
        $codeStmt->execute(['id' => $productId]);
        $existing = $codeStmt->fetch() ?: [];
        $code = trim((string) ($existing['codigo'] ?? ''));

        if ($code === '') {
          $code = next_product_code($pdo, (int) $data['categoria_id']);
        }

        $stmt = $pdo->prepare(
            'UPDATE produtos
             SET codigo = :codigo,
                 nome = :nome,
                 descricao = :descricao,
                 preco = :preco,
                 categoria_id = :categoria_id,
                 imagem = :imagem,
                 stock = :stock,
                 destaque = :destaque
             WHERE id = :id'
        );

        $stmt->execute([
            'codigo' => $code,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
            'preco' => $data['preco'],
            'categoria_id' => $data['categoria_id'],
            'imagem' => $data['imagem'],
            'stock' => $data['stock'],
            'destaque' => $data['destaque'],
            'id' => $productId,
        ]);

        return $productId;
    }

    $code = next_product_code($pdo, (int) $data['categoria_id']);

    $stmt = $pdo->prepare(
        'INSERT INTO produtos
            (codigo, nome, descricao, preco, categoria_id, imagem, stock, destaque)
         VALUES
            (:codigo, :nome, :descricao, :preco, :categoria_id, :imagem, :stock, :destaque)'
    );

    $stmt->execute([
        'codigo' => $code,
        'nome' => $data['nome'],
        'descricao' => $data['descricao'],
        'preco' => $data['preco'],
        'categoria_id' => $data['categoria_id'],
        'imagem' => $data['imagem'],
        'stock' => $data['stock'],
        'destaque' => $data['destaque'],
    ]);

    return (int) $pdo->lastInsertId();
}
