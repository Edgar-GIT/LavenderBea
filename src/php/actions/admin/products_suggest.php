<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';
require_admin_page($pdo);

$term = trim((string) ($_GET['q'] ?? ''));

if ($term === '') {
    exit;
}

$all = fetch_products($pdo);
$items = array_filter($all, static function (array $item) use ($term): bool {
    $name = stripos((string) $item['nome'], $term) !== false;
    $code = stripos(product_public_code($item), $term) !== false;
    $cat = stripos((string) $item['categoria_nome'], $term) !== false;
    $id = (string) $item['id'] === $term;
    return $name || $code || $cat || $id;
});

if ($items === []) {
    ?>
    <div class="nav-search-empty">Sem resultados para essa pesquisa.</div>
    <?php
    exit;
}

foreach (array_slice($items, 0, 8) as $item):
    ?>
    <a
      href="<?= e(url_for('admin.php', ['section' => 'products', 'edit_product' => $item['id']])) ?>"
      class="nav-search-item"
    >
      <span class="nav-search-item-name"><?= e($item['nome']) ?></span>
      <span class="nav-search-item-meta">
        <?= e(product_public_code($item)) ?> · <?= e($item['categoria_nome']) ?> · <?= e(format_price($item['preco'])) ?>
      </span>
    </a>
    <?php
endforeach;
