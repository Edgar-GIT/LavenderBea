<?php
declare(strict_types=1);

//card reutilizavel para listagens do catalogo
$item = $card ?? null;

if (!is_array($item)) {
    return;
}

$id = (int) ($item['id'] ?? 0);
$url = url_for('product.php', ['id' => $id]);
$fav = $id > 0 ? is_favorite_product($pdo, $id) : false;
$code = product_public_code($item);
?>
<article class="product-card" data-product-id="<?= e((string) $id) ?>">
  <button
    class="product-fav-btn<?= $fav ? ' is-favorite' : '' ?>"
    type="button"
    aria-label="<?= $fav ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>"
    data-favorite-toggle
    data-product-id="<?= e((string) $id) ?>"
  >
    ♥
  </button>

  <a href="<?= e($url) ?>" class="catalog-card-link">
    <div class="product-card-media">
      <img
        src="<?= e(product_image($item['imagem'] ?? null)) ?>"
        alt="<?= e($item['nome']) ?>"
      />
    </div>
    <div class="product-card-body">
      <div class="product-card-topline">
        <span class="product-card-category"><?= e($item['categoria_nome']) ?></span>
        <span class="product-badge"><?= e($code) ?></span>
      </div>
      <div class="product-name"><?= e($item['nome']) ?></div>
      <p class="product-card-note">
        <?= e(excerpt($item['descricao'] ?? 'Produto criado no catálogo Lavender Bea.', 150)) ?>
      </p>
      <div class="product-meta-line">
        <span class="product-price"><?= e(format_price($item['preco'])) ?></span>
        <span class="badge-soft"><?= (int) $item['stock'] > 0 ? 'Disponível' : 'Sem stock' ?></span>
      </div>
      <div class="product-meta-line">
        <span class="product-stock<?= (int) $item['stock'] > 0 ? '' : ' out' ?>">
          <?= (int) $item['stock'] > 0 ? e((string) $item['stock']) . ' em stock' : 'Reposicao em breve' ?>
        </span>
        <span class="product-card-linkhint">Ver produto</span>
      </div>
    </div>
  </a>
</article>
