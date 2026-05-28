<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';
require_login_page($pdo);

$cats = fetch_categories($pdo);
$usr = current_user($pdo);
$errs = [];

//tratamento das alteracoes de perfil
if ($usr && is_post()) {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'profile') {
            $nome = trim((string) ($_POST['nome'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $telemovel = trim((string) ($_POST['telemovel'] ?? ''));

            if ($nome === '' || $username === '' || $email === '') {
                throw new RuntimeException('Nome, utilizador e email são obrigatórios.');
            }

            if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
                throw new RuntimeException('O nome de utilizador tem formato inválido.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Indica um email válido.');
            }

            save_user($pdo, [
                'nome' => $nome,
                'username' => $username,
                'email' => $email,
                'telemovel' => $telemovel,
                'password_hash' => null,
                'role' => $usr['role'],
                'ativo' => (int) $usr['ativo'],
            ], (int) $usr['id']);

            redirect('account.php');
        }

        if ($action === 'password') {
            $oldPw = (string) ($_POST['current_password'] ?? '');
            $newPw = (string) ($_POST['new_password'] ?? '');
            $chkPw = (string) ($_POST['confirm_password'] ?? '');

            if (!password_verify($oldPw, $usr['password_hash'])) {
                throw new RuntimeException('A password atual não está correta.');
            }

            if (strlen($newPw) < 8) {
                throw new RuntimeException('A nova password deve ter pelo menos 8 caracteres.');
            }

            if ($newPw !== $chkPw) {
                throw new RuntimeException('A confirmação da nova password não coincide.');
            }

            save_user($pdo, [
                'nome' => $usr['nome'],
                'username' => $usr['username'],
                'email' => $usr['email'],
                'telemovel' => $usr['telemovel'],
                'password_hash' => password_hash($newPw, PASSWORD_DEFAULT),
                'role' => $usr['role'],
                'ativo' => (int) $usr['ativo'],
            ], (int) $usr['id']);

            redirect('account.php');
        }
    }
    catch (Throwable $error) {
        $errs[] = public_error_message(
            $error,
            'Não foi possível atualizar a tua conta.'
        );
    }
}

$usr = current_user($pdo);
$pageTitle = 'A minha conta — ' . APP_NAME;
$activePage = 'account';

require __DIR__ . '/../views/partials/header.php';
?>
<section class="section">
  <div class="container">
    <?php if ($errs !== []): ?>
      <div class="error-stack compact-error">
        <?php foreach ($errs as $error): ?>
          <div class="page-alert page-alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="section-header">
      <div>
        <h1 class="section-title">A minha conta</h1>
        <p class="section-subtitle">
          Gere os teus dados, a password e acede rapidamente ao teu cesto atual.
        </p>
      </div>
    </div>

    <div class="account-grid">
      <aside class="summary-card">
        <div class="summary-card-title">Resumo da conta</div>
        <div class="summary-row">
          <span>Nome</span>
          <strong><?= e($usr['nome']) ?></strong>
        </div>
        <div class="summary-row">
          <span>Utilizador</span>
          <strong><?= e($usr['username']) ?></strong>
        </div>
        <div class="summary-row">
          <span>Email</span>
          <strong><?= e($usr['email']) ?></strong>
        </div>
        <div class="summary-row">
          <span>Role</span>
          <strong><?= e($usr['role']) ?></strong>
        </div>
        <div class="summary-row">
          <span>Criada em</span>
          <strong><?= e(format_datetime($usr['created_at'])) ?></strong>
        </div>
        <div class="summary-actions">
          <a href="<?= e(url_for('cart.php')) ?>" class="btn btn-primary">Abrir cesto</a>
          <a href="<?= e(url_for('logout.php')) ?>" class="btn btn-danger">Terminar sessão</a>
        </div>
      </aside>

      <div class="account-forms">
        <div class="auth-panel">
          <h2 class="section-title">Dados pessoais</h2>
          <form method="post" class="contact-form">
            <input type="hidden" name="action" value="profile" />

            <div class="field-group">
              <label class="field-label" for="acc-name">Nome</label>
              <input
                id="acc-name"
                name="nome"
                type="text"
                class="field-input"
                value="<?= e($usr['nome']) ?>"
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="acc-username">Utilizador</label>
              <input
                id="acc-username"
                name="username"
                type="text"
                class="field-input"
                value="<?= e($usr['username']) ?>"
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="acc-email">Email</label>
              <input
                id="acc-email"
                name="email"
                type="email"
                class="field-input"
                value="<?= e($usr['email']) ?>"
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="acc-phone">Telemóvel</label>
              <input
                id="acc-phone"
                name="telemovel"
                type="text"
                class="field-input"
                value="<?= e($usr['telemovel']) ?>"
              />
            </div>

            <div class="text-right mt-sm">
              <button class="btn btn-primary" type="submit">Guardar dados</button>
            </div>
          </form>
        </div>

        <div class="auth-panel auth-panel-secondary">
          <h2 class="section-title">Alterar password</h2>
          <form method="post" class="contact-form">
            <input type="hidden" name="action" value="password" />

            <div class="field-group">
              <label class="field-label" for="acc-current-password">Password atual</label>
              <input
                id="acc-current-password"
                name="current_password"
                type="password"
                class="field-input"
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="acc-new-password">Nova password</label>
              <input
                id="acc-new-password"
                name="new_password"
                type="password"
                class="field-input"
                required
              />
            </div>

            <div class="field-group">
              <label class="field-label" for="acc-confirm-password">Confirmar nova password</label>
              <input
                id="acc-confirm-password"
                name="confirm_password"
                type="password"
                class="field-input"
                required
              />
            </div>

            <div class="text-right mt-sm">
              <button class="btn btn-ghost" type="submit">Atualizar password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
