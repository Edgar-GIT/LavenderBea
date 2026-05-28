<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';
require_login_page($pdo);

//leitura dos favoritos da conta atual
$favIds = favorite_items($pdo);
$favMap = fetch_products_by_ids($pdo, $favIds);
$favs = [];

foreach ($favIds as $id) {
    if (isset($favMap[$id])) {
        $favs[] = $favMap[$id];
    }
}

$cats = fetch_categories($pdo);
$term = '';
$pageTitle = 'Favoritos — ' . APP_NAME;
$activePage = 'favorites';

require __DIR__ . '/../views/partials/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h1 class="section-title">Favoritos</h1>
        <p class="section-subtitle">
          As peças que guardaste com coração para rever depois.
        </p>
      </div>
    </div>

    <?php if ($favs === []): ?>
      <div class="empty-state">
        <div class="empty-state-title">Ainda não tens favoritos</div>
        <p class="empty-state-text">
          Explora os produtos e carrega no coração para guardar as peças preferidas.
        </p>
        <a href="<?= e(url_for('products.php')) ?>" class="btn btn-primary">Ver produtos</a>
      </div>
    <?php else: ?>
      <div class="product-rail-grid">
        <?php foreach ($favs as $card): ?>
          <?php require __DIR__ . '/../views/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
