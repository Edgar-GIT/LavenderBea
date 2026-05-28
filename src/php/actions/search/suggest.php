<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';

$term = trim((string) ($_GET['q'] ?? ''));

if ($term === '') {
    exit;
}

$items = fetch_products($pdo, [
    'search' => $term,
    'limit' => 5,
]);

if ($items === []) {
    ?>
    <div class="nav-search-empty">Ainda não existem produtos para essa pesquisa.</div>
    <?php
    exit;
}

foreach ($items as $item):
    ?>
    <a
      href="<?= e(url_for('product.php', ['id' => (int) $item['id']])) ?>"
      class="nav-search-item"
    >
      <span class="nav-search-item-name"><?= e($item['nome']) ?></span>
      <span class="nav-search-item-meta">
        <?= e(product_public_code($item)) ?> · <?= e(format_price($item['preco'])) ?>
      </span>
    </a>
    <?php
endforeach;
