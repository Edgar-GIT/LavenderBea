<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//tratamento das operacoes do cesto
if (is_post()) {
    try {
        $action = $_POST['action'] ?? '';
        $pid = (int) ($_POST['product_id'] ?? 0);

        if ($action === 'clear_cart') {
            clear_cart($pdo);
            redirect('cart.php');
        }

        if ($pid <= 0) {
            throw new RuntimeException('Produto inválido para atualizar o cesto.');
        }

        if ($action === 'remove_item') {
            set_cart_quantity($pid, 0, $pdo);
            redirect('cart.php');
        }

        if ($action === 'update_item') {
            $qty = max(0, (int) ($_POST['quantity'] ?? 0));
            $prod = fetch_product($pdo, $pid);

            if (!$prod) {
                set_cart_quantity($pid, 0, $pdo);
                throw new RuntimeException('O produto deixou de existir e foi removido do cesto.');
            }

            $qty = min($qty, (int) $prod['stock']);
            set_cart_quantity($pid, $qty, $pdo);
            redirect('cart.php');
        }
    } 
    catch (Throwable $error) {
        redirect('cart.php');
    }
}

//leitura dos artigos atualmente no cesto
$cart = cart_items($pdo);
$prods = fetch_products_by_ids($pdo, array_keys($cart));
$lines = [];
$total = 0.0;

foreach ($cart as $pid => $qty) {
    if (!isset($prods[$pid])) {
        set_cart_quantity((int) $pid, 0, $pdo);
        continue;
    }

    $prod = $prods[$pid];
    $old = (int) $qty;
    $qty = min($old, (int) $prod['stock']);

    if ($qty <= 0) {
        set_cart_quantity((int) $pid, 0, $pdo);
        continue;
    }

    if ($qty !== $old) {
        set_cart_quantity((int) $pid, $qty, $pdo);
    }

    $sub = (float) $prod['preco'] * $qty;
    $total += $sub;

    $lines[] = [
        'product' => $prod,
        'quantity' => $qty,
        'subtotal' => $sub,
    ];
}

$cats = fetch_categories($pdo);
$pageTitle = 'Cesto — ' . APP_NAME;
$activePage = 'cart';

require __DIR__ . '/../views/partials/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h1 class="section-title">Cesto</h1>
        <p class="section-subtitle">
          Revê os artigos guardados, ajusta quantidades e continua a explorar o catálogo.
        </p>
      </div>
    </div>

    <?php if ($lines === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">O teu cesto está vazio</div>
        <p class="empty-state-text">
          Ainda não guardaste nenhum artigo. Entra no catálogo e adiciona produtos reais ao teu cesto.
        </p>
        <a href="<?= e(url_for('products.php')) ?>" class="btn btn-primary">Abrir catálogo</a>
      </div>
    <?php else: ?>
      <div class="cart-grid">
        <div class="cart-list">
          <?php foreach ($lines as $line): ?>
            <article
              class="cart-card"
              data-cart-line
              data-unit-price="<?= e(number_format((float) $line['product']['preco'], 2, '.', '')) ?>"
            >
              <div class="cart-card-media">
                <img
                  src="<?= e(product_image($line['product']['imagem'])) ?>"
                  alt="<?= e($line['product']['nome']) ?>"
                />
              </div>

              <div class="cart-card-body">
                <div class="cart-card-top">
                  <div>
                    <div class="product-card-category">
                      <?= e($line['product']['categoria_nome']) ?>
                    </div>
                    <h2 class="cart-card-title"><?= e($line['product']['nome']) ?></h2>
                    <div class="cart-card-code-group">
                      <span class="badge-soft"><?= e(product_public_code($line['product'])) ?></span>
                      <button
                        class="btn btn-ghost btn-small"
                        type="button"
                        data-copy-text="<?= e(product_public_code($line['product'])) ?>"
                      >
                        Copiar ID
                      </button>
                    </div>
                    <p class="cart-card-description">
                      <?= e(excerpt($line['product']['descricao'] ?? '', 180)) ?>
                    </p>
                  </div>
                  <div class="cart-card-price" data-cart-line-total>
                    <?= e(format_price($line['subtotal'])) ?>
                  </div>
                </div>

                <div class="cart-card-meta">
                  <span class="product-stock">
                    <?= e((string) $line['product']['stock']) ?> em stock
                  </span>
                </div>

                <div class="cart-card-actions">
                  <form method="post" class="cart-inline-form" data-cart-auto-form>
                    <input type="hidden" name="action" value="update_item" />
                    <input
                      type="hidden"
                      name="product_id"
                      value="<?= e((string) $line['product']['id']) ?>"
                    />
                    <label class="field-label" for="qty-<?= e((string) $line['product']['id']) ?>">
                      Quantidade
                    </label>
                    <input
                      id="qty-<?= e((string) $line['product']['id']) ?>"
                      class="field-input quantity-input"
                      type="number"
                      name="quantity"
                      min="0"
                      max="<?= e((string) $line['product']['stock']) ?>"
                      value="<?= e((string) $line['quantity']) ?>"
                      data-cart-auto-input
                    />
                  </form>

                  <form method="post" class="cart-inline-form">
                    <input type="hidden" name="action" value="remove_item" />
                    <input
                      type="hidden"
                      name="product_id"
                      value="<?= e((string) $line['product']['id']) ?>"
                    />
                    <button class="btn btn-danger" type="submit">Remover</button>
                  </form>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <aside class="summary-card">
          <div class="summary-card-title">Resumo</div>
          <div class="summary-row">
            <span>Artigos</span>
            <strong data-cart-summary-count><?= e((string) cart_count($pdo)) ?></strong>
          </div>
          <div class="summary-row">
            <span>Total</span>
            <strong data-cart-summary-total><?= e(format_price($total)) ?></strong>
          </div>
          <div class="helper-text">
            O cesto fica guardado na tua conta quando tens sessão iniciada.
          </div>

          <div class="summary-actions">
            <a href="<?= e(url_for('products.php')) ?>" class="btn btn-primary">Continuar a comprar</a>
            <?php if (!is_logged_in($pdo)): ?>
              <a href="<?= e(url_for('auth.php')) ?>" class="btn btn-ghost">Entrar na conta</a>
            <?php endif; ?>
            <form method="post">
              <input type="hidden" name="action" value="clear_cart" />
              <button class="btn btn-danger btn-block" type="submit">Limpar cesto</button>
            </form>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
