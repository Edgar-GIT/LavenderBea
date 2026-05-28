<?php
declare(strict_types=1);

require __DIR__ . '/../core/bootstrap.php';

//upload opcional da referencia visual
function store_make_piece_reference(array $file): string{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível carregar a referência visual.');
    }

    $size = (int) ($file['size'] ?? 0);

    if ($size <= 0 || $size > MAX_IMAGE_SIZE) {
        throw new RuntimeException('A referência visual deve ter no máximo 5 MB.');
    }

    $info = @getimagesize($file['tmp_name'] ?? '');

    if (!$info || !isset($info['mime'])) {
        throw new RuntimeException('Seleciona uma imagem válida em JPG, PNG ou WEBP.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$info['mime']])) {
        throw new RuntimeException('Formato da referência visual não suportado.');
    }

    $dir = APP_STORAGE_PATH . '/make_piece';

    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException('Não foi possível preparar a pasta das referências.');
    }

    $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$info['mime']];
    $path = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('Falhou o upload da referência visual.');
    }

    return $name;
}

//tratamento do formulario de pedido
if (is_post()) {
    try {
        $email = trim((string) ($_POST['email'] ?? ''));
        $type = trim((string) ($_POST['pieceType'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($email === '' || $type === '' || $message === '') {
            throw new RuntimeException('Preenche email, tipo de peça e briefing.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Indica um email válido.');
        }

        $types = ['sweat', 'tshirt', 'tote', 'print'];

        if (!in_array($type, $types, true)) {
            throw new RuntimeException('Escolhe um tipo de peça válido.');
        }

        $ref = store_make_piece_reference($_FILES['reference'] ?? []);

        $row = implode(';', [
            date('c'),
            $email,
            $type,
            preg_replace('/\s+/', ' ', $message) ?? $message,
            $ref,
        ]) . PHP_EOL;

        file_put_contents(APP_STORAGE_PATH . '/make_piece_requests.csv', $row, FILE_APPEND | LOCK_EX);

        redirect('make-piece.php');
    } 
    catch (Throwable $error) {
        redirect('make-piece.php');
    }
}

$cats = fetch_categories($pdo);
$pageTitle = 'Make your piece — ' . APP_NAME;
$activePage = 'make-piece';
$term = '';

require __DIR__ . '/../views/partials/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h1 class="section-title">Make your piece</h1>
        <p class="section-subtitle section-subtitle-wide">
          Escreve o teu briefing, escolhe o tipo de peça e anexa referências visuais para a Lavender Bea preparar a tua ideia.
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
          Conta-me tudo: cores, frases, referências visuais, tipo de peça, datas importantes e o mood geral.
          Quanto mais contexto tiver, mais a peça fica mesmo tua.
        </p>
        <form method="post" enctype="multipart/form-data" class="contact-form">
          <div class="field-group">
            <label class="field-label" for="mp-email">Email</label>
            <input
              id="mp-email"
              name="email"
              type="email"
              class="field-input"
              placeholder="Onde respondo com o orçamento?"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="mp-type">Tipo de peça</label>
            <select id="mp-type" name="pieceType" class="field-select" required>
              <option value="">Escolhe uma opção</option>
              <option value="sweat">Sweat</option>
              <option value="tshirt">T-shirt</option>
              <option value="tote">Tote bag</option>
              <option value="print">Print</option>
            </select>
          </div>

          <div class="field-group">
            <label class="field-label" for="mp-message">Briefing</label>
            <textarea
              id="mp-message"
              name="message"
              class="field-textarea"
              placeholder="Explica o conceito, referências, cores, frases, quantidades e tamanhos..."
              required
            ></textarea>
          </div>

          <div class="field-group">
            <label class="field-label" for="mp-reference">Referência visual</label>
            <input
              id="mp-reference"
              name="reference"
              type="file"
              class="field-input"
              accept=".jpg,.jpeg,.png,.webp"
            />
            <p class="field-hint">Opcional, mas ajuda bastante para perceber o mood.</p>
          </div>

          <div class="text-right mt-sm">
            <button class="btn btn-primary" type="submit">Enviar briefing</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../views/partials/footer.php'; ?>
