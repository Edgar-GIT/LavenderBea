<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//leitura do produto atual
$productId = (int) ($_GET['id'] ?? 0);
$product = $productId > 0 ? fetch_product($pdo, $productId) : null;

//tratamento da compra para o cesto
if ($product && is_post()) {
    try {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'add_to_cart') {
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            $quantity = min($quantity, max(1, (int) $product['stock']));

            if ((int) $product['stock'] <= 0) {
                throw new RuntimeException('Este produto está sem stock.');
            }

            add_to_cart((int) $product['id'], $quantity, $pdo);
            redirect('cart.php');
        }
    } 
    catch (Throwable $error) {
        redirect('product.php', ['id' => $productId]);
    }
}

//dados complementares da pagina
$rel = $product
    ? fetch_related_products($pdo, (int) $product['categoria_id'], (int) $product['id'])
    : [];
$cats = fetch_categories($pdo);
$term = '';
$pageTitle = ($product['nome'] ?? 'Produto') . ' — ' . APP_NAME;
$activePage = 'product';
$code = $product ? product_public_code($product) : '';

require __DIR__ . '/../views/partials/header.php';
?>
<?php if (!$product): ?>
  <section class="section">
    <div class="container">
      <div class="empty-state">
        <div class="empty-state-title">Produto não encontrado</div>
        <p class="empty-state-text">
          O artigo que procuraste não existe ou já não está disponível.
        </p>
        <a href="<?= e(url_for('products.php')) ?>" class="btn btn-primary">Voltar ao catálogo</a>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="section">
    <div class="container product-detail">
      <div class="product-detail-media">
        <img
          src="<?= e(product_image($product['imagem'])) ?>"
          alt="<?= e($product['nome']) ?>"
        />
      </div>

      <div class="product-detail-panel">
        <div class="product-card-topline">
          <span class="product-card-category"><?= e($product['categoria_nome']) ?></span>
          <span class="product-badge"><?= e($code) ?></span>
        </div>

        <h1 class="product-detail-title"><?= e($product['nome']) ?></h1>
        <div class="product-detail-price"><?= e(format_price($product['preco'])) ?></div>
        <div class="product-detail-stock">
          <?= (int) $product['stock'] > 0
            ? e((string) $product['stock']) . ' unidades disponíveis'
            : 'Sem stock neste momento' ?>
        </div>

        <div class="mt-xs">
          <span class="status-badge">
            <span class="status-dot<?= (int) $product['stock'] > 0 ? '' : ' out' ?>"></span>
            <span><?= (int) $product['stock'] > 0 ? 'Disponível' : 'Sem stock' ?></span>
          </span>
          <button
            class="btn btn-ghost btn-inline-favorite<?= is_favorite_product($pdo, (int) $product['id']) ? ' is-favorite' : '' ?>"
            type="button"
            data-favorite-toggle
            data-product-id="<?= e((string) $product['id']) ?>"
          >
            <?= is_favorite_product($pdo, (int) $product['id']) ? '♥ Guardado' : '♥ Favorito' ?>
          </button>
        </div>

        <p class="product-detail-description">
          <?= nl2br(e($product['descricao'] ?? '')) ?>
        </p>

        <form method="post" class="product-detail-actions">
          <input type="hidden" name="action" value="add_to_cart" />

          <div class="purchase-box">
            <div class="quantity-row">
              <label class="field-label" for="quantity">Quantidade</label>
              <input
                id="quantity"
                name="quantity"
                type="number"
                min="1"
                max="<?= e((string) max(1, (int) $product['stock'])) ?>"
                value="1"
                class="field-input quantity-input"
                data-qty-input
                <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>
              />
            </div>

            <div class="product-total-line">
              <span>Total</span>
              <strong
                data-qty-total
                data-unit-price="<?= e(number_format((float) $product['preco'], 2, '.', '')) ?>"
              >
                <?= e(format_price($product['preco'])) ?>
              </strong>
            </div>
          </div>

          <div class="helper-text">
            Adiciona ao cesto agora e fecha a compra com calma depois.
          </div>

          <div class="product-action-row">
            <button
              class="btn btn-primary"
              type="submit"
              <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>
            >
              Adicionar ao cesto
            </button>
            <a href="<?= e(url_for('cart.php')) ?>" class="btn btn-ghost">Abrir cesto</a>
            <a href="<?= e(url_for('products.php')) ?>" class="btn btn-ghost">Voltar</a>
          </div>
        </form>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Relacionados</h2>
          <p class="section-subtitle">
            Mais peças da mesma categoria para continuares a explorar.
          </p>
        </div>
      </div>

      <?php if ($rel === []): ?>
        <div class="helper-text">Ainda não existem produtos relacionados.</div>
      <?php else: ?>
        <div class="product-rail-grid">
          <?php foreach ($rel as $card): ?>
            <?php require __DIR__ . '/../views/partials/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
