<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';
require_admin_page($pdo);

$cats = fetch_categories($pdo);
$me = current_user($pdo);
$section = (string) ($_GET['section'] ?? 'dashboard');
$section = in_array($section, ['dashboard', 'products', 'users', 'logs'], true) ? $section : 'dashboard';
$errs = [];
$okMsg = '';

$okKey = (string) ($_GET['success'] ?? '');
if ($okKey !== '') {
    switch ($okKey) {
        case 'product_saved':
            $okMsg = 'Produto guardado com sucesso!';
            break;
        case 'product_deleted':
            $okMsg = 'Produto eliminado com sucesso!';
            break;
        case 'product_updated':
            $okMsg = 'Produto atualizado com sucesso!';
            break;
        case 'user_saved':
            $okMsg = 'Utilizador guardado com sucesso!';
            break;
        case 'user_deleted':
            $okMsg = 'Utilizador eliminado com sucesso!';
            break;
        case 'user_updated':
            $okMsg = 'Utilizador atualizado com sucesso!';
            break;
        default:
            $okMsg = '';
    }
}

$catIds = array_map(static fn (array $item): int => (int) $item['id'], $cats);

$pForm = [
    'id' => 0,
    'nome' => '',
    'descricao' => '',
    'preco' => '0.00',
    'categoria_id' => (int) ($cats[0]['id'] ?? 0),
    'imagem' => '',
    'stock' => 0,
    'destaque' => 0,
];

$uForm = [
    'id' => 0,
    'nome' => '',
    'username' => '',
    'email' => '',
    'telemovel' => '',
    'role' => 'cliente',
    'ativo' => 1,
];

//read product in edition
$editPid = (int) ($_GET['edit_product'] ?? 0);

if ($editPid > 0) {
    $editProd = fetch_product($pdo, $editPid);

    if ($editProd) {
        $pForm = [
            'id' => (int) $editProd['id'],
            'nome' => $editProd['nome'],
            'descricao' => $editProd['descricao'] ?? '',
            'preco' => number_format((float) $editProd['preco'], 2, '.', ''),
            'categoria_id' => (int) $editProd['categoria_id'],
            'imagem' => $editProd['imagem'] ?? '',
            'stock' => (int) $editProd['stock'],
            'destaque' => (int) $editProd['destaque'],
        ];
    }
}

//leitura do utilizador em edicao
$editUid = (int) ($_GET['edit_user'] ?? 0);

if ($editUid > 0) {
    $editUsr = fetch_user($pdo, $editUid);

    if ($editUsr) {
        $uForm = [
            'id' => (int) $editUsr['id'],
            'nome' => $editUsr['nome'],
            'username' => $editUsr['username'],
            'email' => $editUsr['email'],
            'telemovel' => $editUsr['telemovel'] ?? '',
            'role' => $editUsr['role'],
            'ativo' => (int) $editUsr['ativo'],
        ];
    }
}

//operaçoes
if (is_post()) {
    try {
        $action = (string) ($_POST['action'] ?? '');
        $section = (string) ($_POST['section'] ?? $section);
        $section = in_array($section, ['dashboard', 'products', 'users', 'logs'], true) ? $section : 'dashboard';

        if ($action === 'save_product') {
            $section = 'products';
            $pForm = [
                'id' => (int) ($_POST['product_id'] ?? 0),
                'nome' => trim((string) ($_POST['nome'] ?? '')),
                'descricao' => trim((string) ($_POST['descricao'] ?? '')),
                'preco' => trim((string) ($_POST['preco'] ?? '0')),
                'categoria_id' => (int) ($_POST['categoria_id'] ?? 0),
                'imagem' => trim((string) ($_POST['imagem_atual'] ?? '')),
                'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
                'destaque' => isset($_POST['destaque']) ? 1 : 0,
            ];

            if ($pForm['nome'] === '' || $pForm['descricao'] === '') {
                throw new RuntimeException('Nome e descrição do produto são obrigatórios.');
            }

            if (!is_numeric($pForm['preco']) || (float) $pForm['preco'] <= 0) {
                throw new RuntimeException('O preco do produto deve ser superior a zero.');
            }

            if (!in_array($pForm['categoria_id'], $catIds, true)) {
                throw new RuntimeException('Seleciona uma categoria válida.');
            }

            $pForm['imagem'] = upload_product_image($_FILES['imagem'] ?? [], $pForm['imagem']);

            if ($pForm['imagem'] === '') {
                throw new RuntimeException('Carrega uma imagem para o produto.');
            }

            $isNew = $pForm['id'] === 0;
            $savedPid = save_product($pdo, [
                'nome' => $pForm['nome'],
                'descricao' => $pForm['descricao'],
                'preco' => number_format((float) $pForm['preco'], 2, '.', ''),
                'categoria_id' => $pForm['categoria_id'],
                'imagem' => $pForm['imagem'],
                'stock' => $pForm['stock'],
                'destaque' => $pForm['destaque'],
            ], $pForm['id'] ?: null);

            $nextQ = [
                'section' => 'products',
                'success' => $isNew ? 'product_saved' : 'product_updated',
            ];

            if (!$isNew) {
                $nextQ['edit_product'] = $savedPid;
            }

            redirect('admin.php', $nextQ);
        }

        if ($action === 'delete_product') {
            $section = 'products';
            $pid = (int) ($_POST['product_id'] ?? 0);
            $product = fetch_product($pdo, $pid);

            if (!$product) {
                throw new RuntimeException('O produto a apagar já não existe.');
            }

            delete_product_row($pdo, $pid);
            delete_uploaded_image($product['imagem'] ?? null);

            redirect('admin.php', ['section' => 'products', 'success' => 'product_deleted']);
        }

        if ($action === 'toggle_featured') {
            $section = 'products';
            $pid = (int) ($_POST['product_id'] ?? 0);
            $mark = (int) ($_POST['next_featured'] ?? 0) === 1;
            $product = fetch_product($pdo, $pid);

            if (!$product) {
                throw new RuntimeException('O produto já não existe.');
            }

            set_product_featured($pdo, $pid, $mark);
            redirect('admin.php', ['section' => 'products', 'success' => 'product_updated']);
        }

        if ($action === 'save_user') {
            $section = 'users';
            $uForm = [
                'id' => (int) ($_POST['user_id'] ?? 0),
                'nome' => trim((string) ($_POST['nome'] ?? '')),
                'username' => trim((string) ($_POST['username'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'telemovel' => trim((string) ($_POST['telemovel'] ?? '')),
                'role' => ($_POST['role'] ?? 'cliente') === 'admin' ? 'admin' : 'cliente',
                'ativo' => isset($_POST['ativo']) ? 1 : 0,
            ];
            $password = (string) ($_POST['password'] ?? '');

            if ($uForm['nome'] === '' || $uForm['username'] === '' || $uForm['email'] === '') {
                throw new RuntimeException('Nome, utilizador e email são obrigatórios.');
            }

            if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $uForm['username'])) {
                throw new RuntimeException('O utilizador deve ter 3 a 30 caracteres sem espacos.');
            }

            if (!filter_var($uForm['email'], FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('O email do utilizador não é válido.');
            }

            if ($uForm['id'] === 0 && strlen($password) < 8) {
                throw new RuntimeException('Indica uma password com pelo menos 8 caracteres para a nova conta.');
            }

            if ($uForm['id'] === (int) $me['id'] && $uForm['role'] !== 'admin') {
                throw new RuntimeException('Não podes retirar o papel de admin a ti própria.');
            }

            if ($uForm['id'] === (int) $me['id'] && $uForm['ativo'] !== 1) {
                throw new RuntimeException('Não podes desativar a tua própria conta.');
            }

            $hash = null;

            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new RuntimeException('A password deve ter pelo menos 8 caracteres.');
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
            }

            $savedUid = save_user($pdo, [
                'nome' => $uForm['nome'],
                'username' => $uForm['username'],
                'email' => $uForm['email'],
                'telemovel' => $uForm['telemovel'],
                'password_hash' => $hash,
                'role' => $uForm['role'],
                'ip_registo' => $_SERVER['REMOTE_ADDR'] ?? null,
                'ativo' => $uForm['ativo'],
            ], $uForm['id'] ?: null);

            redirect('admin.php', ['section' => 'users', 'edit_user' => $savedUid, 'success' => 'user_saved']);
        }

        if ($action === 'delete_user') {
            $section = 'users';
            $uid = (int) ($_POST['user_id'] ?? 0);

            if ($uid === (int) $me['id']) {
                throw new RuntimeException('Não podes apagar a tua própria conta.');
            }

            $user = fetch_user($pdo, $uid);

            if (!$user) {
                throw new RuntimeException('O utilizador já não existe.');
            }

            delete_user_row($pdo, $uid);
            redirect('admin.php', ['section' => 'users', 'success' => 'user_deleted']);
        }

        if ($action === 'toggle_user_active') {
            $section = 'users';
            $uid = (int) ($_POST['user_id'] ?? 0);
            $active = (int) ($_POST['next_active'] ?? 0) === 1;

            if ($uid === (int) $me['id'] && !$active) {
                throw new RuntimeException('Não podes desativar a tua própria conta.');
            }

            $user = fetch_user($pdo, $uid);

            if (!$user) {
                throw new RuntimeException('O utilizador já não existe.');
            }

            set_user_active($pdo, $uid, $active);
            redirect('admin.php', ['section' => 'users', 'success' => 'user_updated']);
        }
    } 
    catch (Throwable $error) {
        $errs[] = public_error_message(
            $error,
            'Não foi possível guardar esta alteração.'
        );
    }
}

//leitura final para as listagens do painel
$prods = fetch_products($pdo);
$users = fetch_users($pdo);
$logs = fetch_admin_logs($pdo);
$prodQ = trim((string) ($_GET['product_search'] ?? ''));
$showProd = $prodQ === ''
    ? $prods
    : array_values(array_filter(
        $prods,
        static function (array $item) use ($prodQ): bool {
            $name = stripos((string) $item['nome'], $prodQ) !== false;
            $code = stripos(product_public_code($item), $prodQ) !== false;
            $cat = stripos((string) $item['categoria_nome'], $prodQ) !== false;
            $id = (string) $item['id'] === $prodQ;
            return $name || $code || $cat || $id;
        }
    ));
$userQ = trim((string) ($_GET['user_search'] ?? ''));
$showUser = $userQ === ''
    ? $users
    : array_values(array_filter(
        $users,
        static fn (array $item): bool => stripos((string) $item['nome'], $userQ) !== false
    ));
$feat = array_values(array_filter(
    $prods,
    static fn (array $item): bool => (int) $item['destaque'] === 1
));
$recent = array_slice($logs, 0, 8);
$prevImg = $pForm['imagem'] !== '' ? product_image($pForm['imagem']) : '';
$pageTitle = 'Admin — ' . APP_NAME;
$sec = $section;

require __DIR__ . '/../views/admin/header.php';
?>
<?php if ($okMsg !== ''): ?>
  <div class="error-stack compact-error">
    <div class="page-alert page-alert-success"><?= e($okMsg) ?></div>
  </div>
<?php endif; ?>
<?php if ($errs !== []): ?>
  <div class="error-stack compact-error">
    <?php foreach ($errs as $error): ?>
      <div class="page-alert page-alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="admin-section-head">
  <div>
    <h2 class="section-title">
      <?php
        $titleMap = array(
            'dashboard' => 'Painel',
            'users' => 'Utilizadores',
            'logs' => 'Logs',
        );
        echo isset($titleMap[$section]) ? $titleMap[$section] : 'Produtos';
      ?>
    </h2>
    <p class="section-subtitle">
      <?php
        $subtitleMap = array(
            'dashboard' => 'Resumo rápido da loja e atividade recente.',
            'users' => 'Gestão de Usuários.',
            'logs' => 'Registo automático das alterações importantes.',
        );
        echo isset($subtitleMap[$section]) ? $subtitleMap[$section] : 'Criação, edição, apagamento e relevância dos produtos.';
      ?>
    </p>
  </div>
</div>

<div class="admin-summary-grid">
  <div class="summary-card">
    <div class="summary-card-title">Produtos</div>
    <div class="summary-row">
      <span>Total</span>
      <strong><?= e((string) count($prods)) ?></strong>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-card-title">Relevância</div>
    <div class="summary-row">
      <span>Em destaque</span>
      <strong><?= e((string) count($feat)) ?></strong>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-card-title">Utilizadores</div>
    <div class="summary-row">
      <span>Contas criadas</span>
      <strong><?= e((string) count($users)) ?></strong>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-card-title">Logs</div>
    <div class="summary-row">
      <span>Eventos</span>
      <strong><?= e((string) count($logs)) ?></strong>
    </div>
  </div>
</div>

<?php if ($section === 'dashboard'): ?>
  <section class="admin-panel">
    <div class="section-header admin-list-header">
      <div>
        <h3 class="section-title">Logs recentes</h3>
        <p class="section-subtitle">Últimos movimentos registados pelas triggers da base de dados.</p>
      </div>
      <a href="<?= e(url_for('admin.php', ['section' => 'logs'])) ?>" class="btn btn-ghost">
        Ver todos
      </a>
    </div>

    <?php if ($recent === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">Ainda não há logs</div>
        <p class="empty-state-text">
          Assim que criares, alterares ou apagares dados importantes, os eventos aparecem aqui.
        </p>
      </div>
    <?php else: ?>
      <div class="admin-log-list">
        <?php foreach ($recent as $log): ?>
          <article class="admin-log-card">
            <div>
              <span class="admin-log-badge"><?= e($log['acao']) ?></span>
              <strong><?= e($log['resumo']) ?></strong>
              <p><?= e($log['detalhes'] ?: 'Sem detalhes adicionais.') ?></p>
            </div>
            <span><?= e(format_datetime($log['created_at'])) ?></span>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
<?php elseif ($section === 'products'): ?>
  <div class="admin-products-grid">
    <section class="admin-panel">
      <div class="product-detail admin-preview-grid">
        <div class="product-detail-media admin-preview-media">
          <?php if ($prevImg !== ''): ?>
            <img
              src="<?= e($prevImg) ?>"
              alt="Preview do produto"
              data-preview-image
            />
          <?php else: ?>
            <img
              src=""
              alt="Preview do produto"
              class="hidden"
              data-preview-image
            />
          <?php endif; ?>
          <div
            class="admin-preview-empty<?= $prevImg !== '' ? ' hidden' : '' ?>"
            data-preview-empty
          >
            Sem imagem carregada
          </div>
        </div>

        <div class="product-detail-panel">
          <span class="product-card-category">Preview do produto</span>
          <h2 class="product-detail-title" data-preview-name>
            <?= e($pForm['nome'] !== '' ? $pForm['nome'] : 'Novo produto') ?>
          </h2>
          <div class="product-detail-price" data-preview-price>
            <?= e(format_price($pForm['preco'])) ?>
          </div>
          <div class="product-detail-stock" data-preview-stock>
            <?= $pForm['stock'] > 0 ? e((string) $pForm['stock']) . ' unidades em stock' : 'Sem stock de momento' ?>
          </div>
          <p class="product-detail-description" data-preview-desc>
            <?= e($pForm['descricao'] !== '' ? $pForm['descricao'] : 'A descrição vai aparecer aqui assim que começares a escrever no formulário.') ?>
          </p>

          <form method="post" enctype="multipart/form-data" class="admin-form-grid">
            <input type="hidden" name="action" value="save_product" />
            <input type="hidden" name="section" value="products" />
            <input type="hidden" name="product_id" value="<?= e((string) $pForm['id']) ?>" />
            <input type="hidden" name="imagem_atual" value="<?= e($pForm['imagem']) ?>" />

            <div class="field-group">
              <label class="field-label" for="admin-product-name">Nome</label>
              <input
                id="admin-product-name"
                name="nome"
                type="text"
                class="field-input"
                value="<?= e($pForm['nome']) ?>"
                data-preview-name-source
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="admin-product-price">Preço</label>
              <input
                id="admin-product-price"
                name="preco"
                type="number"
                step="0.01"
                min="0.01"
                class="field-input"
                value="<?= e($pForm['preco']) ?>"
                data-preview-price-source
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="admin-product-category">Categoria</label>
              <select
                id="admin-product-category"
                name="categoria_id"
                class="field-input"
                required
              >
                <?php foreach ($cats as $category): ?>
                  <option
                    value="<?= e((string) $category['id']) ?>"
                    <?= selected_attr($category['id'], $pForm['categoria_id']) ?>
                  >
                    <?= e($category['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field-group">
              <label class="field-label" for="admin-product-stock">Stock</label>
              <input
                id="admin-product-stock"
                name="stock"
                type="number"
                min="0"
                class="field-input"
                value="<?= e((string) $pForm['stock']) ?>"
                data-preview-stock-source
                required
              />
            </div>

            <div class="field-group field-group-wide">
              <label class="field-label" for="admin-product-description">Descrição</label>
              <textarea
                id="admin-product-description"
                name="descricao"
                class="field-textarea"
                data-preview-desc-source
                required
              ><?= e($pForm['descricao']) ?></textarea>
            </div>

            <div class="field-group">
              <label class="field-label" for="admin-product-image">Imagem</label>
              <input
                id="admin-product-image"
                name="imagem"
                type="file"
                class="field-input"
                accept=".jpg,.jpeg,.png,.webp"
                data-preview-input
              />
            </div>

            <label class="products-fav-toggle admin-checkbox">
              <input
                type="checkbox"
                name="destaque"
                value="1"
                <?= checked_attr((int) $pForm['destaque'] === 1) ?>
              />
              <span>Mostrar na home como destaque</span>
            </label>

            <div class="admin-form-actions field-group-wide">
              <button class="btn btn-primary" type="submit">
                <?= $pForm['id'] > 0 ? 'Guardar alterações' : 'Criar produto' ?>
              </button>
              <a href="<?= e(url_for('admin.php', ['section' => 'products'])) ?>" class="btn btn-ghost">
                Novo produto
              </a>
            </div>
          </form>
        </div>
      </div>
    </section>

    <aside class="admin-panel">
      <div class="section-header">
        <div>
          <h3 class="section-title">Relevância atual</h3>
          <p class="section-subtitle">Os produtos marcados para aparecerem em destaque.</p>
        </div>
      </div>

      <?php if ($feat === []): ?>
        <div class="helper-text">Ainda não existem produtos relevantes.</div>
      <?php else: ?>
        <div class="admin-compact-list">
          <?php foreach ($feat as $product): ?>
            <article class="admin-compact-card">
              <img
                src="<?= e(product_image($product['imagem'])) ?>"
                alt="<?= e($product['nome']) ?>"
                class="admin-thumb"
              />
              <div class="admin-compact-copy">
                <strong><?= e($product['nome']) ?></strong>
                <span><?= e(product_public_code($product)) ?> · <?= e($product['categoria_nome']) ?></span>
              </div>
              <form method="post">
                <input type="hidden" name="action" value="toggle_featured" />
                <input type="hidden" name="section" value="products" />
                <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>" />
                <input type="hidden" name="next_featured" value="0" />
                <button class="btn btn-ghost btn-small" type="submit">Retirar</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  </div>

  <section class="admin-panel mt-lg">
    <div class="section-header admin-list-header">
      <div>
        <h3 class="section-title">Catálogo registado</h3>
        <p class="section-subtitle">Edita, destaca ou remove qualquer artigo da base de dados.</p>
      </div>
      <form
        method="get"
        class="products-search"
        data-live-search-group
        data-search-url="<?= e(base_url('src/php/actions/admin/products_suggest.php')) ?>"
      >
        <input type="hidden" name="section" value="products" />
        <input
          class="field-input"
          type="search"
          name="product_search"
          placeholder="Pesquisar por nome, ID ou categoria..."
          value="<?= e($prodQ) ?>"
          autocomplete="off"
          data-live-search-input
        />
        <div class="nav-search-results hidden" data-live-search-results></div>
      </form>
    </div>

    <?php if ($showProd === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">
          <?= $prodQ === '' ? 'Ainda não existem produtos' : 'Sem resultados para esse produto' ?>
        </div>
        <p class="empty-state-text">
          <?= $prodQ === ''
            ? 'Cria o primeiro artigo usando o formulário acima.'
            : 'Experimenta pesquisar outro nome, ID ou categoria.' ?>
        </p>
      </div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Categoria</th>
              <th>Preço</th>
              <th>Stock</th>
              <th>Relevância</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($showProd as $product): ?>
              <tr>
                <td>
                  <div class="admin-table-title">
                    <img
                      src="<?= e(product_image($product['imagem'])) ?>"
                      alt="<?= e($product['nome']) ?>"
                      class="admin-thumb"
                    />
                    <div>
                      <strong><?= e($product['nome']) ?></strong>
                      <div class="admin-table-note"><?= e(product_public_code($product)) ?></div>
                    </div>
                  </div>
                </td>
                <td><?= e($product['categoria_nome']) ?></td>
                <td><?= e(format_price($product['preco'])) ?></td>
                <td><?= e((string) $product['stock']) ?></td>
                <td><?= (int) $product['destaque'] === 1 ? 'Sim' : 'Não' ?></td>
                <td><?= e(format_datetime($product['created_at'])) ?></td>
                <td>
                  <div class="admin-actions">
                    <a
                      href="<?= e(url_for('admin.php', ['section' => 'products', 'edit_product' => $product['id']])) ?>"
                      class="btn btn-ghost btn-small"
                    >
                      Editar
                    </a>

                    <form method="post">
                      <input type="hidden" name="action" value="toggle_featured" />
                      <input type="hidden" name="section" value="products" />
                      <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>" />
                      <input
                        type="hidden"
                        name="next_featured"
                        value="<?= (int) $product['destaque'] === 1 ? '0' : '1' ?>"
                      />
                      <button class="btn btn-ghost btn-small" type="submit">
                        <?= (int) $product['destaque'] === 1 ? 'Retirar destaque' : 'Destacar' ?>
                      </button>
                    </form>

                    <form method="post">
                      <input type="hidden" name="action" value="delete_product" />
                      <input type="hidden" name="section" value="products" />
                      <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>" />
                      <button class="btn btn-danger btn-small" type="submit">Apagar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
<?php elseif ($section === 'users'): ?>
  <div class="admin-users-grid">
    <section class="admin-panel">
      <h3 class="section-title">
        <?= $uForm['id'] > 0 ? 'Editar utilizador' : 'Novo utilizador' ?>
      </h3>
      <p class="section-subtitle">
        O admin pode criar, editar, promover, desativar ou apagar qualquer conta.
      </p>

      <form method="post" class="contact-form">
        <input type="hidden" name="action" value="save_user" />
        <input type="hidden" name="section" value="users" />
        <input type="hidden" name="user_id" value="<?= e((string) $uForm['id']) ?>" />

        <div class="field-group">
          <label class="field-label" for="admin-user-name">Nome</label>
          <input
            id="admin-user-name"
            name="nome"
            type="text"
            class="field-input"
            value="<?= e($uForm['nome']) ?>"
            required
          />
        </div>

        <div class="field-group">
          <label class="field-label" for="admin-user-username">Utilizador</label>
          <input
            id="admin-user-username"
            name="username"
            type="text"
            class="field-input"
            value="<?= e($uForm['username']) ?>"
            required
          />
        </div>

        <div class="field-group">
          <label class="field-label" for="admin-user-email">Email</label>
          <input
            id="admin-user-email"
            name="email"
            type="email"
            class="field-input"
            value="<?= e($uForm['email']) ?>"
            required
          />
        </div>

        <div class="field-group">
          <label class="field-label" for="admin-user-phone">Telemóvel</label>
          <input
            id="admin-user-phone"
            name="telemovel"
            type="text"
            class="field-input"
            value="<?= e($uForm['telemovel']) ?>"
          />
        </div>

        <div class="field-group">
          <label class="field-label" for="admin-user-role">Role</label>
          <select id="admin-user-role" name="role" class="field-input">
            <option value="cliente" <?= selected_attr('cliente', $uForm['role']) ?>>
              Cliente
            </option>
            <option value="admin" <?= selected_attr('admin', $uForm['role']) ?>>
              Admin
            </option>
          </select>
        </div>

        <div class="field-group">
          <label class="field-label" for="admin-user-password">
            <?= $uForm['id'] > 0 ? 'Nova password (opcional)' : 'Password' ?>
          </label>
          <input
            id="admin-user-password"
            name="password"
            type="password"
            class="field-input"
            <?= $uForm['id'] > 0 ? '' : 'required' ?>
          />
        </div>

        <label class="products-fav-toggle admin-checkbox">
          <input
            type="checkbox"
            name="ativo"
            value="1"
            <?= checked_attr((int) $uForm['ativo'] === 1) ?>
          />
          <span>Conta ativa</span>
        </label>

        <div class="admin-form-actions">
          <button class="btn btn-primary" type="submit">
            <?= $uForm['id'] > 0 ? 'Guardar utilizador' : 'Criar utilizador' ?>
          </button>
          <a href="<?= e(url_for('admin.php', ['section' => 'users'])) ?>" class="btn btn-ghost">
            Novo utilizador
          </a>
        </div>
      </form>
    </section>

    <aside class="summary-card">
      <div class="summary-card-title">Acesso rápido</div>
      <div class="summary-row">
        <span>Admins</span>
        <strong><?= e((string) count(array_filter($users, static fn (array $item): bool => $item['role'] === 'admin'))) ?></strong>
      </div>
      <div class="summary-row">
        <span>Clientes</span>
        <strong><?= e((string) count(array_filter($users, static fn (array $item): bool => $item['role'] === 'cliente'))) ?></strong>
      </div>
      <div class="summary-row">
        <span>Ativos</span>
        <strong><?= e((string) count(array_filter($users, static fn (array $item): bool => (int) $item['ativo'] === 1))) ?></strong>
      </div>
    </aside>
  </div>

  <section class="admin-panel mt-lg">
    <div class="section-header admin-list-header">
      <div>
        <h3 class="section-title">Contas registadas</h3>
        <p class="section-subtitle">O admin tem acesso completo aos dados desta fase do projeto.</p>
      </div>
      <div
        class="products-search"
        data-live-search-group
        data-search-url="<?= e(base_url('src/php/actions/admin/users_suggest.php')) ?>"
      >
        <input
          class="field-input"
          type="search"
          name="q"
          placeholder="Pesquisar por nome, utilizador ou email..."
          autocomplete="off"
          data-live-search-input
        />
        <div class="nav-search-results hidden" data-live-search-results></div>
      </div>
    </div>

    <?php if ($showUser === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">
          <?= $userQ === '' ? 'Ainda não existem utilizadores' : 'Sem resultados para esse nome' ?>
        </div>
        <p class="empty-state-text">
          <?= $userQ === ''
            ? 'As novas contas criadas no site vão aparecer automaticamente aqui.'
            : 'Experimenta pesquisar outro nome ou limpa o campo de pesquisa.' ?>
        </p>
      </div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Conta</th>
              <th>Email</th>
              <th>Telefone</th>
              <th>Role</th>
              <th>Estado</th>
              <th>IP</th>
              <th>Criada em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($showUser as $user): ?>
              <tr>
                <td>
                  <strong><?= e($user['nome']) ?></strong>
                  <div class="admin-table-note">@<?= e($user['username']) ?></div>
                </td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['telemovel'] ?: '—') ?></td>
                <td><?= e($user['role']) ?></td>
                <td><?= (int) $user['ativo'] === 1 ? 'Ativa' : 'Suspensa' ?></td>
                <td><?= e($user['ip_registo'] ?: '—') ?></td>
                <td><?= e(format_datetime($user['created_at'])) ?></td>
                <td>
                  <div class="admin-actions">
                    <a
                      href="<?= e(url_for('admin.php', ['section' => 'users', 'edit_user' => $user['id']])) ?>"
                      class="btn btn-ghost btn-small"
                    >
                      Editar
                    </a>

                    <form method="post">
                      <input type="hidden" name="action" value="toggle_user_active" />
                      <input type="hidden" name="section" value="users" />
                      <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>" />
                      <input
                        type="hidden"
                        name="next_active"
                        value="<?= (int) $user['ativo'] === 1 ? '0' : '1' ?>"
                      />
                      <button class="btn btn-ghost btn-small" type="submit">
                        <?= (int) $user['ativo'] === 1 ? 'Suspender' : 'Ativar' ?>
                      </button>
                    </form>

                    <form method="post">
                      <input type="hidden" name="action" value="delete_user" />
                      <input type="hidden" name="section" value="users" />
                      <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>" />
                      <button class="btn btn-danger btn-small" type="submit">Apagar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
<?php elseif ($section === 'logs'): ?>
  <section class="admin-panel">
    <div class="section-header admin-list-header">
      <div>
        <h3 class="section-title">Histórico de atividade</h3>
        <p class="section-subtitle">
          Eventos gerados automaticamente pela base de dados quando algo muda.
        </p>
      </div>
    </div>

    <?php if ($logs === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">Ainda não há logs</div>
        <p class="empty-state-text">
          Cria ou altera produtos, utilizadores, favoritos ou cestos para começar o histórico.
        </p>
      </div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table admin-log-table">
          <thead>
            <tr>
              <th>Evento</th>
              <th>Ação</th>
              <th>Detalhes</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td><strong><?= e($log['resumo']) ?></strong></td>
                <td><span class="admin-log-badge"><?= e($log['acao']) ?></span></td>
                <td><?= e($log['detalhes'] ?: '—') ?></td>
                <td><?= e(format_datetime($log['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
<?php endif; ?>
<?php require __DIR__ . '/../views/admin/footer.php'; ?>
