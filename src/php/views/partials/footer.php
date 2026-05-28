<?php
declare(strict_types=1);

//preparacao dos dados do rodape publico
$usr = $usr ?? current_user($pdo);
$adm = $adm ?? is_admin($pdo);
$accUrl = $adm ? url_for('admin.php') : url_for('account.php');
$askCart = should_show_cart_merge_prompt($pdo);
$guestN = $askCart ? array_sum(guest_cart_items()) : 0;
?>
      </main>

      <footer class="site-footer">
        <div class="container footer-inner">
          <span>© Lavender Bea — Todos os direitos reservados.</span>
          <div class="footer-actions">
            <div class="footer-links">
              <a href="<?= e(url_for('products.php')) ?>" class="footer-link">Catálogo</a>
              <a href="<?= e(url_for('index.php', ['section' => 'contact'])) ?>" class="footer-link">Contactos</a>
            </div>
            <div class="footer-socials" aria-label="Redes sociais Lavender Bea">
              <a
                href="https://www.instagram.com/lavender__beal"
                target="_blank"
                rel="noreferrer"
                class="footer-social-link footer-social-link-instagram"
                aria-label="Instagram Lavender Bea"
              >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path
                    d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm8.2 1.9H8A4.1 4.1 0 0 0 3.9 8v8a4.1 4.1 0 0 0 4.1 4.1h8a4.1 4.1 0 0 0 4.1-4.1V8A4.1 4.1 0 0 0 16 3.9ZM17.5 5.4a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 6.7A5.3 5.3 0 1 1 6.7 12 5.3 5.3 0 0 1 12 6.7Zm0 1.9A3.4 3.4 0 1 0 15.4 12 3.4 3.4 0 0 0 12 8.6Z"
                  />
                </svg>
              </a>
              <a
                href="https://www.vinted.pt/member/67074353-bealeite2003"
                target="_blank"
                rel="noreferrer"
                class="footer-social-link footer-social-link-vinted"
                aria-label="Vinted Lavender Bea"
              >
                <span>Vi</span>
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>

    <?php if ($usr): ?>
      <div class="account-overlay hidden" data-user-menu-overlay>
        <button
          type="button"
          class="account-overlay-backdrop"
          data-user-menu-close
          aria-label="Fechar menu da conta"
        ></button>

        <div class="account-menu-panel">
          <div class="account-menu-kicker"><?= e(display_user_name($usr)) ?></div>
          <p class="account-menu-text">
            <?= e($usr['email']) ?>
          </p>
          <div class="account-menu-actions">
            <a href="<?= e($accUrl) ?>" class="btn btn-ghost btn-block">
              <?= $adm ? 'Abrir painel' : 'Abrir conta' ?>
            </a>
            <a href="<?= e(url_for('cart.php')) ?>" class="btn btn-ghost btn-block">
              Ver cesto
            </a>
            <a href="<?= e(url_for('logout.php')) ?>" class="btn btn-danger btn-block">
              Logout
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($askCart): ?>
      <div class="cart-merge-overlay">
        <div class="cart-merge-panel">
          <div class="account-menu-kicker">Cesto temporário</div>
          <p class="account-menu-text">
            Tens <?= e((string) $guestN) ?> artigo(s) escolhidos antes do login.
            Queres juntar esses artigos ao cesto da tua conta?
          </p>
          <form method="post" action="<?= e(base_url('src/php/actions/cart/merge.php')) ?>" class="cart-merge-actions">
            <button class="btn btn-primary btn-block" type="submit" name="decision" value="merge">
              Juntar ao meu cesto
            </button>
            <button class="btn btn-ghost btn-block" type="submit" name="decision" value="discard">
              Ignorar cesto temporário
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <script src="<?= e(base_url('src/js/core.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/validators.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/navigation.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/favorites.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/account.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/shop.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/admin.js')) ?>"></script>
    <script src="<?= e(base_url('src/js/site.js')) ?>"></script>
  </body>
</html>
