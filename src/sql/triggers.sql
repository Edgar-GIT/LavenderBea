-- nomes: c criar, e editar, a apagar

-- produtos
DROP TRIGGER IF EXISTS trg_lpc;
CREATE TRIGGER trg_lpc
AFTER INSERT ON produtos
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'produtos',
    'criar',
    NEW.id,
    CONCAT('Produto criado: ', NEW.nome),
    CONCAT('codigo=', COALESCE(NEW.codigo, ''), '; preco=', NEW.preco, '; stock=', NEW.stock)
);

DROP TRIGGER IF EXISTS trg_lpe;
CREATE TRIGGER trg_lpe
AFTER UPDATE ON produtos
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'produtos',
    'alterar',
    NEW.id,
    CONCAT('Produto alterado: ', NEW.nome),
    CONCAT(
        'antes_nome=', OLD.nome,
        '; depois_nome=', NEW.nome,
        '; antes_preco=', OLD.preco,
        '; depois_preco=', NEW.preco,
        '; antes_stock=', OLD.stock,
        '; depois_stock=', NEW.stock
    )
);

DROP TRIGGER IF EXISTS trg_lpa;
CREATE TRIGGER trg_lpa
AFTER DELETE ON produtos
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'produtos',
    'apagar',
    OLD.id,
    CONCAT('Produto apagado: ', OLD.nome),
    CONCAT('codigo=', COALESCE(OLD.codigo, ''), '; preco=', OLD.preco, '; stock=', OLD.stock)
);

-- utilizadores
DROP TRIGGER IF EXISTS trg_luc;
CREATE TRIGGER trg_luc
AFTER INSERT ON utilizadores
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'utilizadores',
    'criar',
    NEW.id,
    CONCAT('Utilizador criado: ', NEW.username),
    CONCAT('email=', NEW.email, '; role=', NEW.role, '; ativo=', NEW.ativo)
);

DROP TRIGGER IF EXISTS trg_lue;
CREATE TRIGGER trg_lue
AFTER UPDATE ON utilizadores
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'utilizadores',
    'alterar',
    NEW.id,
    CONCAT('Utilizador alterado: ', NEW.username),
    CONCAT(
        'antes_email=', OLD.email,
        '; depois_email=', NEW.email,
        '; antes_role=', OLD.role,
        '; depois_role=', NEW.role,
        '; antes_ativo=', OLD.ativo,
        '; depois_ativo=', NEW.ativo
    )
);

DROP TRIGGER IF EXISTS trg_lua;
CREATE TRIGGER trg_lua
AFTER DELETE ON utilizadores
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'utilizadores',
    'apagar',
    OLD.id,
    CONCAT('Utilizador apagado: ', OLD.username),
    CONCAT('email=', OLD.email, '; role=', OLD.role)
);

-- favoritos
DROP TRIGGER IF EXISTS trg_lfc;
CREATE TRIGGER trg_lfc
AFTER INSERT ON favoritos
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'favoritos',
    'criar',
    NEW.id,
    'Produto adicionado aos favoritos',
    CONCAT('utilizador_id=', NEW.utilizador_id, '; produto_id=', NEW.produto_id)
);

DROP TRIGGER IF EXISTS trg_lfa;
CREATE TRIGGER trg_lfa
AFTER DELETE ON favoritos
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'favoritos',
    'apagar',
    OLD.id,
    'Produto removido dos favoritos',
    CONCAT('utilizador_id=', OLD.utilizador_id, '; produto_id=', OLD.produto_id)
);

-- cesto
DROP TRIGGER IF EXISTS trg_lcc;
CREATE TRIGGER trg_lcc
AFTER INSERT ON carrinho_itens
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'carrinho_itens',
    'criar',
    NEW.id,
    'Produto adicionado ao cesto',
    CONCAT('utilizador_id=', NEW.utilizador_id, '; produto_id=', NEW.produto_id, '; quantidade=', NEW.quantidade)
);

DROP TRIGGER IF EXISTS trg_lce;
CREATE TRIGGER trg_lce
AFTER UPDATE ON carrinho_itens
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'carrinho_itens',
    'alterar',
    NEW.id,
    'Quantidade do cesto alterada',
    CONCAT(
        'utilizador_id=', NEW.utilizador_id,
        '; produto_id=', NEW.produto_id,
        '; antes=', OLD.quantidade,
        '; depois=', NEW.quantidade
    )
);

DROP TRIGGER IF EXISTS trg_lca;
CREATE TRIGGER trg_lca
AFTER DELETE ON carrinho_itens
FOR EACH ROW
INSERT INTO logs_admin (tabela, acao, registo_id, resumo, detalhes)
VALUES (
    'carrinho_itens',
    'apagar',
    OLD.id,
    'Produto removido do cesto',
    CONCAT('utilizador_id=', OLD.utilizador_id, '; produto_id=', OLD.produto_id, '; quantidade=', OLD.quantidade)
);
