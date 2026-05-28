<?php
declare(strict_types=1);

//preparacao dos dados comuns do cabecalho publico
$pageTitle = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';
$term = $term ?? search_value();
$cats = $cats ?? fetch_categories($pdo);
$usr = current_user($pdo);
$adm = is_admin($pdo);
$cartN = cart_count($pdo);
$favN = favorite_count($pdo);
$favUrl = url_for('favorites.php');
$cartUrl = url_for('cart.php');
$searchUrl = url_for('products.php');
$sugUrl = base_url('src/php/actions/search/suggest.php');
$togUrl = base_url('src/php/actions/favorites/toggle.php');
?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, viewport-fit=cover"
    />
    <title><?= e($pageTitle) ?></title>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    />
    <link rel="stylesheet" href="<?= e(base_url('src/css/style.css')) ?>" />
  </head>
  <body
    data-search-url="<?= e($sugUrl) ?>"
    data-favorite-url="<?= e($togUrl) ?>"
    data-is-logged="<?= $usr ? '1' : '0' ?>"
    data-active-page="<?= e($activePage) ?>"
  >
    <div class="page-shell">
      <header class="navbar">
        <div class="navbar-inner">
          <a href="<?= e(url_for('index.php')) ?>" class="brand">
            <img
              src="<?= e(base_url('img/logo-nav2.png')) ?>"
              alt="Lavender Bea"
              class="brand-logo"
            />
          </a>

          <nav class="nav-links" aria-label="Navegação principal">
            <a
              href="<?= e(url_for('index.php')) ?>"
              class="nav-link <?= e(active_class($activePage === 'home')) ?>"
            >
              Home
            </a>
            <a
              href="<?= e(url_for('make-piece.php')) ?>"
              class="nav-link <?= e(active_class($activePage === 'make-piece')) ?>"
            >
              Create a Piece
            </a>
            <div class="nav-dropdown">
              <a
                href="<?= e(url_for('products.php')) ?>"
                class="nav-link <?= e(active_class($activePage === 'products' || $activePage === 'product' || $activePage === 'favorites')) ?>"
              >
                Produtos
              </a>
              <div class="nav-dropdown-menu">
                <?php foreach ($cats as $cat): ?>
                  <a
                    href="<?= e(url_for('products.php', ['cat' => $cat['slug']])) ?>"
                    class="nav-dropdown-item"
                  >
                    <?= e($cat['nome']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
            <a
              href="<?= e(url_for('index.php', ['section' => 'contact'])) ?>"
              class="nav-link <?= e(active_class($activePage === 'contact')) ?>"
            >
              Contact Us
            </a>
            <a
              href="<?= e(url_for('index.php', ['section' => 'about'])) ?>"
              class="nav-link <?= e(active_class($activePage === 'about')) ?>"
            >
              About Us
            </a>
          </nav>

          <form
            class="nav-search"
            method="get"
            action="<?= e($searchUrl) ?>"
            data-search-form
            data-live-search-group
          >
            <input
              id="nav-search"
              class="nav-search-input"
              type="search"
              name="q"
              value="<?= e($term) ?>"
              placeholder="Procura por nome ou ID..."
              autocomplete="off"
              data-live-search-input
            />
            <button class="nav-search-submit" type="submit" aria-label="Pesquisar">
              🔍
            </button>
            <div class="nav-search-results hidden" data-search-results data-live-search-results></div>
          </form>

          <div class="nav-actions">
            <button
              id="fav-button"
              class="nav-heart<?= $favN > 0 ? ' is-filled' : '' ?>"
              type="button"
              aria-label="Favoritos"
              data-favorites-url="<?= e($favUrl) ?>"
            >
              ♥
            </button>
            <a
              href="<?= e($cartUrl) ?>"
              class="nav-cart-link"
              aria-label="Abrir cesto"
              title="Cesto"
            >
              <span class="nav-cart-icon">🛒</span>
              <?php if ($cartN > 0): ?>
                <span class="nav-cart-count"><?= e((string) $cartN) ?></span>
              <?php endif; ?>
            </a>
            <?php if ($usr): ?>
              <button
                class="nav-user-btn"
                type="button"
                data-user-menu-trigger
                aria-label="Abrir menu da conta"
              >
                <?= e(display_user_name($usr)) ?>
              </button>
            <?php else: ?>
              <a href="<?= e(url_for('auth.php')) ?>" class="nav-auth-link">Login / Conta</a>
            <?php endif; ?>
            <button
              class="nav-icon-btn nav-toggle"
              type="button"
              aria-label="Menu"
            >
              ☰
            </button>
          </div>
        </div>
      </header>

      <main class="page-content">
