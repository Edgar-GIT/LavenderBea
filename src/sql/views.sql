-- categorias
CREATE OR REPLACE VIEW vw_categorias AS
SELECT
    id,
    nome,
    slug
FROM categorias;

-- produtos catalogo
CREATE OR REPLACE VIEW vw_produtos_catalogo AS
SELECT
    p.id,
    p.codigo,
    p.nome,
    p.descricao,
    p.preco,
    p.categoria_id,
    c.nome AS categoria_nome,
    c.slug AS categoria_slug,
    p.imagem,
    p.stock,
    p.destaque,
    p.created_at,
    CASE
        WHEN p.stock > 0 THEN 'disponivel'
        ELSE 'sem_stock'
    END AS estado_stock
FROM produtos p
INNER JOIN categorias c ON c.id = p.categoria_id;

-- produtos em destaque
CREATE OR REPLACE VIEW vw_produtos_destaque AS
SELECT *
FROM vw_produtos_catalogo
WHERE destaque = 1;

-- stock produtos
CREATE OR REPLACE VIEW vw_stock_produtos AS
SELECT
    id,
    codigo,
    nome,
    categoria_nome,
    stock,
    estado_stock,
    created_at
FROM vw_produtos_catalogo;

-- favoritos
CREATE OR REPLACE VIEW vw_favoritos_produtos AS
SELECT
    f.id,
    f.utilizador_id,
    f.produto_id,
    p.codigo,
    p.nome,
    p.categoria_nome,
    p.categoria_slug,
    p.preco,
    p.imagem,
    p.stock,
    f.created_at
FROM favoritos f
INNER JOIN vw_produtos_catalogo p ON p.id = f.produto_id;

-- cesto
CREATE OR REPLACE VIEW vw_carrinho_produtos AS
SELECT
    ci.id,
    ci.utilizador_id,
    ci.produto_id,
    ci.quantidade,
    p.codigo,
    p.nome,
    p.descricao,
    p.categoria_nome,
    p.categoria_slug,
    p.preco,
    p.imagem,
    p.stock,
    (ci.quantidade * p.preco) AS subtotal,
    ci.created_at,
    ci.updated_at
FROM carrinho_itens ci
INNER JOIN vw_produtos_catalogo p ON p.id = ci.produto_id;

-- utilizadores admin
CREATE OR REPLACE VIEW vw_utilizadores_admin AS
SELECT
    id,
    nome,
    username,
    email,
    telemovel,
    role,
    ip_registo,
    ativo,
    created_at
FROM utilizadores;

-- logs admin
CREATE OR REPLACE VIEW vw_logs_admin AS
SELECT
    id,
    tabela,
    acao,
    registo_id,
    resumo,
    detalhes,
    created_at
FROM logs_admin;

-- resumo admin
CREATE OR REPLACE VIEW vw_resumo_admin AS
SELECT
    (SELECT COUNT(*) FROM produtos) AS total_produtos,
    (SELECT COUNT(*) FROM produtos WHERE destaque = 1) AS produtos_destaque,
    (SELECT COUNT(*) FROM produtos WHERE stock > 0) AS produtos_com_stock,
    (SELECT COUNT(*) FROM produtos WHERE stock <= 0) AS produtos_sem_stock,
    (SELECT COUNT(*) FROM utilizadores) AS total_utilizadores,
    (SELECT COUNT(*) FROM utilizadores WHERE ativo = 1) AS utilizadores_ativos,
    (SELECT COUNT(*) FROM utilizadores WHERE role = 'admin') AS total_admins,
    (SELECT COUNT(*) FROM utilizadores WHERE role = 'cliente') AS total_clientes,
    (SELECT COUNT(*) FROM favoritos) AS total_favoritos,
    (SELECT COALESCE(SUM(quantidade), 0) FROM carrinho_itens) AS artigos_em_cestos,
    (SELECT COUNT(*) FROM logs_admin) AS total_logs;
