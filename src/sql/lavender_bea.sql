CREATE TABLE IF NOT EXISTS categorias (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utilizadores (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    telemovel VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
    ip_registo VARCHAR(45) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) DEFAULT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT DEFAULT NULL,
    preco DECIMAL(8,2) NOT NULL,
    categoria_id INT NOT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favoritos (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL,
    produto_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_favoritos_utilizador_produto (utilizador_id, produto_id),
    CONSTRAINT fk_favoritos_utilizador
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_favoritos_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS carrinho_itens (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_carrinho_utilizador_produto (utilizador_id, produto_id),
    CONSTRAINT fk_carrinho_utilizador
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_carrinho_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_admin (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tabela VARCHAR(50) NOT NULL,
    acao ENUM('criar', 'alterar', 'apagar') NOT NULL,
    registo_id INT DEFAULT NULL,
    resumo VARCHAR(255) NOT NULL,
    detalhes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nome, slug)
VALUES
    ('Prints', 'prints'),
    ('Sweats', 'sweats'),
    ('T-Shirts', 't-shirts'),
    ('Tote Bags', 'tote-bags')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
