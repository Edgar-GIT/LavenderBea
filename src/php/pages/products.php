<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//leitura dos filtros do catalogo
$cats = fetch_categories($pdo);
$term = search_value();
$catSlug = normalize_category_slug($_GET['cat'] ?? null);
$catNow = $catSlug ? fetch_category_by_slug($pdo, $catSlug) : null;
$items = fetch_products($pdo, [
    'category_slug' => $catNow['slug'] ?? null,
    'search' => $term,
]);
$pageTitle = 'Produtos — ' . APP_NAME;
$activePage = 'products';

if ($catNow) {
    $pageTitle = $catNow['nome'] . ' — ' . APP_NAME;
}

require __DIR__ . '/../views/partials/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h1 class="section-title">
          <?= e($catNow['nome'] ?? 'Produtos') ?>
        </h1>
        <p class="section-subtitle section-subtitle-wide">
          <?= $term !== ''
            ? 'Resultados da pesquisa por nome ou ID.'
            : 'Pesquisa por nome ou ID, ou percorre o drop atual.' ?>
        </p>
      </div>
      <span class="pill">
        <span class="pill-dot"></span>
        <span><?= e((string) count($items)) ?> resultado(s)</span>
      </span>
    </div>

    <form method="get" class="products-toolbar">
      <?php if ($catNow): ?>
        <input type="hidden" name="cat" value="<?= e($catNow['slug']) ?>" />
      <?php endif; ?>
      <div class="products-search" data-live-search-group>
        <input
          id="products-search"
          type="search"
          name="q"
          class="field-input"
          placeholder="Pesquisa por nome ou ID..."
          value="<?= e($term) ?>"
          autocomplete="off"
          data-live-search-input
        />
        <div class="nav-search-results hidden" data-live-search-results></div>
      </div>
      <div class="products-toolbar-actions">
        <button class="btn btn-primary" type="submit">Pesquisar</button>
        <?php if (is_logged_in($pdo)): ?>
          <a href="<?= e(url_for('favorites.php')) ?>" class="btn btn-ghost">Favoritos</a>
        <?php endif; ?>
      </div>
    </form>

    <div class="products-filter">
      <div class="products-filter-chips">
        <a
          href="<?= e(url_for('products.php', ['q' => $term])) ?>"
          class="products-filter-chip <?= e(active_class($catNow === null)) ?>"
        >
          Todos
        </a>
        <?php foreach ($cats as $cat): ?>
          <a
            href="<?= e(url_for('products.php', ['cat' => $cat['slug'], 'q' => $term])) ?>"
            class="products-filter-chip <?= e(active_class(($catNow['slug'] ?? '') === $cat['slug'])) ?>"
          >
            <?= e($cat['nome']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="products-results">
      <?php if ($items === []): ?>
        <div class="empty-state">
          <div class="empty-state-title">Ainda não há produtos para mostrar</div>
          <p class="empty-state-text">
            Ajusta os filtros ou volta mais tarde quando existirem artigos nesta categoria.
          </p>
        </div>
      <?php else: ?>
        <div class="product-rail-grid">
          <?php foreach ($items as $card): ?>
            <?php require __DIR__ . '/../views/partials/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
