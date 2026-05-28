<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? ('Admin — ' . APP_NAME);
$sec = $sec ?? 'dashboard';
$me = $me ?? current_user($pdo);
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
    class="admin-body"
    data-search-url="<?= e(base_url('src/php/actions/admin/users_suggest.php')) ?>"
  >
    <div class="admin-shell">
      <header class="admin-topbar">
        <div class="container admin-topbar-inner">
          <div>
            <div class="admin-eyebrow">Lavender Bea</div>
            <h1 class="admin-brand-title">Painel de administração</h1>
          </div>
          <div class="admin-topbar-actions">
            <span class="badge-soft">Admin: <?= e($me['username'] ?? '') ?></span>
            <a href="<?= e(url_for('index.php')) ?>" class="btn btn-ghost">Voltar ao site</a>
            <a href="<?= e(url_for('logout.php')) ?>" class="btn btn-danger">Logout</a>
          </div>
        </div>
      </header>

      <div class="container admin-layout">
        <aside class="admin-sidebar">
          <nav class="admin-nav" aria-label="Navegação do admin">
            <a
              href="<?= e(url_for('admin.php', ['section' => 'dashboard'])) ?>"
              class="admin-nav-link <?= e(active_class($sec === 'dashboard')) ?>"
            >
              Painel
            </a>
            <a
              href="<?= e(url_for('admin.php', ['section' => 'users'])) ?>"
              class="admin-nav-link <?= e(active_class($sec === 'users')) ?>"
            >
              Utilizadores
            </a>
            <a
              href="<?= e(url_for('admin.php', ['section' => 'products'])) ?>"
              class="admin-nav-link <?= e(active_class($sec === 'products')) ?>"
            >
              Produtos
            </a>
            <a
              href="<?= e(url_for('admin.php', ['section' => 'logs'])) ?>"
              class="admin-nav-link <?= e(active_class($sec === 'logs')) ?>"
            >
              Logs
            </a>
          </nav>
          <div class="admin-sidebar-note">
            O admin trabalha aqui sem misturar o layout público da loja.
          </div>
        </aside>

        <main class="admin-main">
