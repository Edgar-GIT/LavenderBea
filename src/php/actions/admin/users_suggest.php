<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';
require_admin_page($pdo);

$term = trim((string) ($_GET['q'] ?? ''));

if ($term === '') {
    exit;
}

$all = fetch_users($pdo);
$items = array_filter($all, static function (array $user) use ($term): bool {
    $nome = stripos((string) $user['nome'], $term) !== false;
    $username = stripos((string) $user['username'], $term) !== false;
    $email = stripos((string) $user['email'], $term) !== false;
    return $nome || $username || $email;
});

if ($items === []) {
    ?>
    <div class="nav-search-empty">Sem resultados para essa pesquisa.</div>
    <?php
    exit;
}

foreach (array_slice($items, 0, 8) as $user):
    ?>
    <a
      href="<?= e(url_for('admin.php', ['section' => 'users', 'edit_user' => $user['id']])) ?>"
      class="nav-search-item"
    >
      <span class="nav-search-item-name"><?= e($user['nome']) ?></span>
      <span class="nav-search-item-meta">
        @<?= e($user['username']) ?> · <?= e($user['email']) ?>
      </span>
    </a>
    <?php
endforeach;
