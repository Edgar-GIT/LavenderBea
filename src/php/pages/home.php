<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//tratamento simples do formulario de contacto
if (is_post()) {
    try {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'contact') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));

            if ($name === '' || $email === '' || $message === '') {
                throw new RuntimeException('Preenche nome, email e mensagem.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Indica um email válido.');
            }

            redirect('index.php', ['section' => 'contact']);
        }
    } 
    catch (Throwable $error) {
        redirect('index.php', ['section' => 'contact']);
    }
}

//leitura dos dados da homepage
$cats = fetch_categories($pdo);
$feat = fetch_products($pdo, ['featured' => true, 'limit' => 4]);
$pageTitle = APP_NAME . ' — Camisolas ilustradas';
$activePage = 'home';
$term = '';

require __DIR__ . '/../views/partials/header.php';
?>
<section id="home" class="hero container">
  <div class="hero-content">
    <div class="eyebrow">Drop de peças ilustradas</div>
    <h1 class="hero-title">
      Camisolas que
      <span>vestem o teu mood</span>.
    </h1>
    <p class="hero-subtitle">
      Sweats, t-shirts, totes e prints ilustrados pela Lavender Bea, criados
      peça a peça para o teu closet (e paredes) favorito.
    </p>

    <div class="hero-actions">
      <a href="<?= e(url_for('products.php')) ?>" class="btn btn-primary">
        Ver produtos
      </a>
      <a
        href="<?= e(url_for('make-piece.php')) ?>"
        class="btn btn-ghost"
      >
        Make my piece
      </a>
    </div>

    <div class="hero-metas">
      <div class="hero-meta">
        <strong>Peças únicas</strong>
        ilustradas à mão, produzidas em pequenos lotes.
      </div>
      <div class="hero-meta">
        <strong>Made to order</strong>
        encomendas personalizadas para o teu estilo.
      </div>
    </div>
  </div>

  <div class="hero-media">
    <video
      class="hero-video"
      src="<?= e(base_url('vd/v1.mp4')) ?>"
      loop
      muted
      autoplay
      playsinline
    ></video>
    <div class="hero-media-overlay"></div>
    <div class="hero-badge">Studio · Porto</div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Categorias</h2>
        <p class="section-subtitle">
          Escolhe por tipo de peça. O resto é moodboard, café e e-mails.
        </p>
      </div>
      <span class="pill">
        <span class="pill-dot"></span>
        <span>Lavender Bea · Drop</span>
      </span>
    </div>

    <div id="category-grid" class="category-grid">
      <?php foreach ($cats as $cat): ?>
        <a
          href="<?= e(url_for('products.php', ['cat' => $cat['slug']])) ?>"
          class="category-card"
          data-category-id="<?= e($cat['slug']) ?>"
        >
          <div class="category-media">
            <img
              src="<?= e(base_url(category_cover($cat['slug']))) ?>"
              alt="<?= e($cat['nome']) ?>"
            />
            <div class="category-gradient"></div>
          </div>
          <div class="category-body">
            <div class="category-name"><?= e($cat['nome']) ?></div>
            <div class="category-meta"><?= e(category_text($cat['slug'])) ?></div>
            <div class="category-cta">Ver categoria</div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div id="product-rail" class="product-rail">
      <div class="product-rail-header">
        <div>
          <div id="rail-title" class="product-rail-title">Produtos em destaque</div>
          <div class="product-rail-subtitle">
            Seleções atuais que o admin marcou como relevantes.
          </div>
        </div>
        <a href="<?= e(url_for('products.php')) ?>" class="badge-soft">
          Ver catálogo completo
        </a>
      </div>

      <div id="rail-grid" class="product-rail-grid">
        <?php if ($feat === []): ?>
          <div class="helper-text">Ainda não há produtos em destaque.</div>
        <?php else: ?>
          <?php foreach ($feat as $card): ?>
            <?php require __DIR__ . '/../views/partials/product-card.php'; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section id="about" class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">About us</h2>
        <p class="section-subtitle">
          Quem está por trás das ilustrações, das camisolas e dos e-mails.
        </p>
      </div>
    </div>

    <div class="about-grid">
      <div class="about-row">
        <div class="about-row-text">
          <p>
            Lavender Bea é o pequeno universo ilustrado da Bea Leite. Entre
            cadernos, tablets e cafés demorados, nascem sweats, t-shirts, totes
            e prints que vivem entre o conforto e a estética.
          </p>
          <p>
            Cada peça é pensada como uma pequena história que se veste: um
            personagem, um símbolo ou aquela frase que só faz sentido para o teu
            grupo.
          </p>
        </div>
        <div class="about-row-media">
          <img
            src="<?= e(base_url('img/b1.jpeg')) ?>"
            alt="Detalhe de camisola ilustrada"
          />
        </div>
      </div>

      <div class="about-row">
        <div class="about-row-text">
          <p>
            O processo mistura sketches no caderno, iPad, referências que me
            envias e muita troca de mensagens até sentirmos que a peça está
            exatamente como queres.
          </p>
        </div>
        <div class="about-row-media">
          <img
            src="<?= e(base_url('img/b2.jpeg')) ?>"
            alt="Print ilustrado Lavender Bea"
          />
        </div>
      </div>

      <div class="about-row">
        <div class="about-row-text">
          <p>
            Produzimos em pequenas quantidades, com atenção ao detalhe, e
            abrimos espaço para encomendas personalizadas para ti, para o teu
            grupo de amigos ou para a tua marca.
          </p>
        </div>
        <div class="about-row-media">
          <img
            src="<?= e(base_url('img/b3.jpeg')) ?>"
            alt="Tote bag ilustrada Lavender Bea"
          />
        </div>
      </div>
    </div>
  </div>
</section>

<section id="make-piece" class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Make your piece</h2>
        <p class="section-subtitle">
          Transforma uma ideia solta num hoodie, t-shirt, tote ou print só teu.
        </p>
      </div>
    </div>

    <div class="make-piece">
      <div class="make-piece-media">
        <video
          src="<?= e(base_url('vd/v2.mp4')) ?>"
          autoplay
          muted
          loop
          playsinline
        ></video>
      </div>
      <div class="make-piece-text">
        <p>
          Tens uma ideia, referência ou frase que queres ver numa peça? Envia o
          teu briefing e construímos juntas uma peça única, do sketch ao envio.
        </p>
        <div class="make-piece-steps">
          <div class="make-piece-step">
            <strong>1 · Ideia</strong>
            Conta o mood, referências, frases, cores e o tipo de peça.
          </div>
          <div class="make-piece-step">
            <strong>2 · Sketch</strong>
            Recebes propostas de desenho para afinar detalhes.
          </div>
          <div class="make-piece-step">
            <strong>3 · Piece</strong>
            Produção da peça e envio para o teu closet ou parede.
          </div>
        </div>

        <div class="mt-md">
          <a
            href="<?= e(url_for('make-piece.php')) ?>"
            class="btn btn-primary"
          >
            Make my piece
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="contact" class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Contactos</h2>
        <p class="section-subtitle">
          Para encomendas, colaborações ou apenas para dizer olá.
        </p>
      </div>
    </div>

    <div class="contact-grid">
      <form method="post" class="contact-form">
        <input type="hidden" name="action" value="contact" />

        <div class="field-group">
          <label class="field-label" for="contact-name">Nome</label>
          <input
            id="contact-name"
            name="name"
            type="text"
            class="field-input"
            placeholder="Como te chamas?"
            required
          />
        </div>
        <div class="field-group">
          <label class="field-label" for="contact-email">Email</label>
          <input
            id="contact-email"
            name="email"
            type="email"
            class="field-input"
            placeholder="Onde respondo?"
            required
          />
        </div>
        <div class="field-group">
          <label class="field-label" for="contact-phone">Telemóvel</label>
          <input
            id="contact-phone"
            name="phone"
            type="tel"
            class="field-input"
            placeholder="Opcional, para contacto rápido"
          />
        </div>
        <div class="field-group">
          <label class="field-label" for="contact-message">Mensagem</label>
          <textarea
            id="contact-message"
            name="message"
            class="field-textarea"
            placeholder="Conta-me o que tens em mente."
            required
          ></textarea>
          <p class="field-hint">
            Ex.: encomenda específica, orçamento, parceria ou dúvidas sobre tamanhos.
          </p>
        </div>
        <div class="text-right mt-sm">
          <button class="btn btn-primary" type="submit">Enviar</button>
        </div>
      </form>

      <aside class="contact-meta">
        <p class="contact-meta-intro">
          Podes também falar diretamente pelos canais onde o projeto vive no dia
          a dia.
        </p>
        <div class="contact-links">
          <a
            href="https://www.vinted.pt/member/67074353-bealeite2003"
            target="_blank"
            rel="noreferrer"
            class="contact-link"
          >
            <span class="contact-link-icon contact-link-icon-vinted" aria-hidden="true">
              <span>Vi</span>
            </span>
            <span class="contact-link-copy">
              <span class="contact-link-tag">Vinted</span>
              <span class="contact-link-main">@bealeite2003</span>
            </span>
          </a>
          <a
            href="https://www.instagram.com/lavender__beal"
            target="_blank"
            rel="noreferrer"
            class="contact-link"
          >
            <span class="contact-link-icon contact-link-icon-instagram" aria-hidden="true">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm8.2 1.9H8A4.1 4.1 0 0 0 3.9 8v8a4.1 4.1 0 0 0 4.1 4.1h8a4.1 4.1 0 0 0 4.1-4.1V8A4.1 4.1 0 0 0 16 3.9ZM17.5 5.4a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 6.7A5.3 5.3 0 1 1 6.7 12 5.3 5.3 0 0 1 12 6.7Zm0 1.9A3.4 3.4 0 1 0 15.4 12 3.4 3.4 0 0 0 12 8.6Z"
                />
              </svg>
            </span>
            <span class="contact-link-copy">
              <span class="contact-link-tag">Instagram</span>
              <span class="contact-link-main">@lavender__beal</span>
            </span>
          </a>
          <a
            href="mailto:portalcriativo.beatriz@gmail.com"
            class="contact-link"
          >
            <span class="contact-link-icon contact-link-icon-mail" aria-hidden="true">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11Zm2.2-.6 6.8 5.2 6.8-5.2H5.2Zm14 2-6.6 5a1 1 0 0 1-1.2 0l-6.6-5v9.4c0 .5.4.9.9.9h12.6c.5 0 .9-.4.9-.9V7.9Z"
                />
              </svg>
            </span>
            <span class="contact-link-copy">
              <span class="contact-link-tag">Email</span>
              <span class="contact-link-main">portalcriativo.beatriz@gmail.com</span>
            </span>
          </a>
        </div>
        <div class="contact-dots" aria-label="Acesso rápido aos contactos">
          <a
            href="https://www.vinted.pt/member/67074353-bealeite2003"
            target="_blank"
            rel="noreferrer"
            class="contact-dot contact-dot-vinted"
            aria-label="Abrir Vinted"
          >
            <span>Vi</span>
          </a>
          <a
            href="https://www.instagram.com/lavender__beal"
            target="_blank"
            rel="noreferrer"
            class="contact-dot contact-dot-instagram"
            aria-label="Abrir Instagram"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm8.2 1.9H8A4.1 4.1 0 0 0 3.9 8v8a4.1 4.1 0 0 0 4.1 4.1h8a4.1 4.1 0 0 0 4.1-4.1V8A4.1 4.1 0 0 0 16 3.9ZM17.5 5.4a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 6.7A5.3 5.3 0 1 1 6.7 12 5.3 5.3 0 0 1 12 6.7Zm0 1.9A3.4 3.4 0 1 0 15.4 12 3.4 3.4 0 0 0 12 8.6Z"
              />
            </svg>
          </a>
          <a
            href="mailto:portalcriativo.beatriz@gmail.com"
            class="contact-dot contact-dot-mail"
            aria-label="Enviar email"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11Zm2.2-.6 6.8 5.2 6.8-5.2H5.2Zm14 2-6.6 5a1 1 0 0 1-1.2 0l-6.6-5v9.4c0 .5.4.9.9.9h12.6c.5 0 .9-.4.9-.9V7.9Z"
              />
            </svg>
          </a>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
