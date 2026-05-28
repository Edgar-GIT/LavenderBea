<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//geracao de username para novas contas
function build_auth_username(PDO $pdo, string $name, string $email): string{
    $base = trim($name);

    if ($base === '') {
        $base = strstr($email, '@', true) ?: $email;
    }

    $base = ascii_identifier($base);
    $base = strtolower($base);
    $base = preg_replace('/[^a-z0-9._-]+/', '', str_replace(' ', '.', $base)) ?? '';
    $base = trim($base, '._-');

    if ($base === '') {
        $base = 'utilizador';
    }

    if (strlen($base) < 3) {
        $base = str_pad($base, 3, 'x');
    }

    $base = substr($base, 0, 24);
    $cand = $base;
    $suf = 1;

    while (fetch_user_by_login($pdo, $cand)) {
        $txt = (string) $suf;
        $cand = substr($base, 0, max(1, 24 - strlen($txt))) . $txt;
        $suf += 1;
    }

    return $cand;
}

if (is_logged_in($pdo)) {
    redirect(is_admin($pdo) ? 'admin.php' : 'account.php');
}

$cats = fetch_categories($pdo);
$errs = [];
$dupErr = '';
$login = '';
$regName = '';
$regMail = '';

//tratamento de login e registo
if (is_post()) {
    try {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'login') {
            $login = trim((string) ($_POST['login'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($login === '' || $password === '') {
                throw new RuntimeException('Preenche o utilizador/email e a password.');
            }

            $user = fetch_user_by_login($pdo, $login);

            if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
                throw new RuntimeException('Credenciais inválidas.');
            }

            if ((int) ($user['ativo'] ?? 0) !== 1) {
                throw new RuntimeException('Esta conta está desativada.');
            }

            login_user($user, $pdo);
            redirect(($user['role'] ?? '') === 'admin' ? 'admin.php' : 'account.php');
        }

        if ($action === 'register') {
            $regName = trim((string) ($_POST['name'] ?? ''));
            $regMail = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($regName === '' || $regMail === '' || $password === '') {
                throw new RuntimeException('Preenche nome, email e password.');
            }

            if (!filter_var($regMail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Indica um email válido.');
            }

            if (strlen($password) < 8) {
                throw new RuntimeException('A password deve ter pelo menos 8 caracteres.');
            }

            $username = build_auth_username($pdo, $regName, $regMail);

            $userId = save_user($pdo, [
                'nome' => $regName,
                'username' => $username,
                'email' => $regMail,
                'telemovel' => '',
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'cliente',
                'ip_registo' => $_SERVER['REMOTE_ADDR'] ?? null,
                'ativo' => 1,
            ]);

            $user = fetch_user($pdo, $userId);

            if (!$user) {
                throw new RuntimeException('A conta foi criada, mas não foi possível iniciar sessão.');
            }

            login_user($user, $pdo);
            redirect('account.php');
        }
    } 
    catch (Throwable $error) {
        if (($action ?? '') === 'register' && is_unique_error($error)) {
            $dupErr = 'Já existe uma conta com esse email ou utilizador.';
        } 
        else {
            $errs[] = $error->getMessage();
        }
    }
}

//layout dados base
$pageTitle = 'Entrar — ' . APP_NAME;
$activePage = 'auth';

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
    <?php if ($dupErr !== ''): ?>
      <div class="site-toast-stack auth-error-popup">
        <div class="site-toast"><?= e($dupErr) ?></div>
      </div>
    <?php endif; ?>

    <div class="auth-grid">
      <div class="auth-panel">
        <h1 class="section-title">Entrar</h1>
        <p class="section-subtitle">
          Entra com o teu email ou nome de utilizador.
        </p>

        <form method="post" class="contact-form">
          <input type="hidden" name="action" value="login" />

          <div class="field-group">
            <label class="field-label" for="login-identifier">Email ou utilizador</label>
            <input
              id="login-identifier"
              name="login"
              type="text"
              class="field-input"
              placeholder="Email ou utilizador"
              value="<?= e($login) ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="login-password">Password</label>
            <input
              id="login-password"
              name="password"
              type="password"
              class="field-input"
              placeholder="••••••••"
              required
            />
          </div>

          <div class="text-right mt-sm">
            <button class="btn btn-primary" type="submit">Login</button>
          </div>

          <div class="auth-reset-row">
            <a href="#" class="auth-reset-link" aria-disabled="true">
              Esqueci-me da password · Repor password
            </a>
          </div>
        </form>
      </div>

      <div class="auth-panel auth-panel-secondary">
        <h2 class="section-title">Criar conta</h2>
        <p class="section-subtitle">
          O utilizador é gerado automaticamente e depois podes entrar com email ou conta.
        </p>

        <form method="post" class="contact-form">
          <input type="hidden" name="action" value="register" />

          <div class="field-group">
            <label class="field-label" for="reg-name">Nome</label>
            <input
              id="reg-name"
              name="name"
              type="text"
              class="field-input"
              placeholder="O teu nome ou @"
              value="<?= e($regName) ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="reg-email">Email</label>
            <input
              id="reg-email"
              name="email"
              type="email"
              class="field-input"
              placeholder="Onde recebes updates e confirmações"
              value="<?= e($regMail) ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="reg-password">Password</label>
            <input
              id="reg-password"
              name="password"
              type="password"
              class="field-input"
              placeholder="Cria uma password segura"
              required
            />
          </div>

          <div class="text-right mt-sm">
            <button class="btn btn-ghost" type="submit">Criar conta</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
