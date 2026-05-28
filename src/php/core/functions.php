<?php
declare(strict_types=1);

//iniciar sessao e criar pastas de armazenamento
function boot_app(): void{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    foreach ([APP_STORAGE_PATH, PRODUCT_UPLOAD_DIR] as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        @chmod($directory, 0777);
    }
}

//conversao tags
function e(null|string|int|float $value): string{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

//prefix basing
function base_url(string $path = ''): string{
    static $prefix = null;

    if ($prefix === null) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''); //normalizar
        $marker = '/src/php/'; //procura o marcador para defenir o prefixo
        $position = strpos($script, $marker);

        if ($position !== false) { //caso o marcador seja encontrado
            $prefix = substr($script, 0, $position);
        }
        else { //usa o diretorio do codigo a correr atualmente como prefixo
            $dir = str_replace('\\', '/', dirname($script));
            $prefix = $dir === '/' ? '' : rtrim($dir, '/');
        }
    }

    $path = ltrim($path, '/');

    if ($path === '') {
        return ($prefix ?: '') . '/';
    }

    return ($prefix ?: '') . '/' . $path;
}

//montagem de url with query
function url_for(string $path, array $query = []): string{
    if ($path === 'index.php') {
        $path = 'index.php';
    } 
    elseif (preg_match('/^[A-Za-z0-9_-]+\.php$/', $path)) {
        $path = 'src/php/pages/' . $path;
    }

    $url = base_url($path);
    $query = array_filter(
        $query,
        static fn ($value) => $value !== null && $value !== ''
    );

    if ($query === []) {
        return $url;
    }

    return $url . '?' . http_build_query($query);
}

//redirecionamentos
function redirect(string $path, array $query = []): never{
    header('Location: ' . url_for($path, $query));
    exit;
}

//method checker
function is_post(): bool{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

//price formating
function format_price(float|int|string $value): string{
    return number_format((float) $value, 2, ',', '.') . '€';
}

//date formating
function format_datetime(?string $value): string{
    if (!$value) {
        return '—';
    }

    $date = date_create($value);

    return $date ? $date->format('d/m/Y H:i') : '—';
}

//resumos para listagens e descricoes
function excerpt(?string $value, int $length = 140): string{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? '';

    if (strlen($value) <= $length) {
        return $value;
    }

    return rtrim(substr($value, 0, $length - 3)) . '...';
}

//image choosing
function product_image(?string $path): string{
    if ($path && is_file(BASE_PATH . '/' . ltrim($path, '/'))) {
        return base_url(ltrim($path, '/'));
    }

    $filename = basename((string) $path);

    if ($filename !== '' && $filename !== '.' && $filename !== '..') {
        $uploadPath = PRODUCT_UPLOAD_DIR . '/' . $filename;

        if (is_file($uploadPath)) {
            return base_url('uploads/products/' . $filename);
        }
    }

    return category_cover('default');
}

//imagens de apoio das categorias na home
function category_cover(string $slug): string{
    return match ($slug) {
        'sweats' => 'img/b1.jpeg',
        't-shirts' => 'img/b2.jpeg',
        'tote-bags' => 'img/b3.jpeg',
        'prints' => 'img/b4.jpeg',
        default => 'img/b5.jpeg',
    };
}

//textos menu
function category_text(string $slug): string{
    return match ($slug) {
        'sweats' => 'Sweats confortaveis com acabamento artesanal e presenca forte.',
        't-shirts' => 'T-shirts leves, desenhadas para o dia a dia e para pequenos drops.',
        'tote-bags' => 'Tote bags praticas para levar a marca contigo todos os dias.',
        'prints' => 'Prints ilustrados para parede, estudio ou espaco pessoal.',
        default => 'Peças desenvolvidas pela Lavender Bea com produção pequena.',
    };
}

//normalização
function ascii_identifier(string $value): string{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($ascii !== false) {
            $value = $ascii;
        }
    } 
    else {
        $value = strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c', 'Ñ' => 'N', 'ñ' => 'n',
        ]);
    }

    return $value;
}

//prefixos ID
function product_code_prefix(string $slug): string{
    return match ($slug) {
        'sweats' => 'SW',
        't-shirts' => 'TS',
        'tote-bags' => 'BG',
        'prints' => 'PR',
        default => 'PD',
    };
}

//public id 
function product_public_code(?array $product): string{
    if (!$product) {
        return '';
    }

    $code = trim((string) ($product['codigo'] ?? ''));

    if ($code !== '') {
        return $code;
    }

    $id = (int) ($product['id'] ?? 0);
    return $id > 0 ? 'PD-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT) : '—';
}

//normalizacao
function normalize_category_slug(?string $slug): ?string{
    $slug = trim((string) $slug);

    if ($slug === '') {
        return null;
    }

    return match ($slug) {
        'sweat', 'sweats' => 'sweats',
        'tshirt', 'tshirts', 't-shirt', 't-shirts' => 't-shirts',
        'tote', 'totes', 'tote-bag', 'tote-bags' => 'tote-bags',
        'print', 'prints' => 'prints',
        default => $slug,
    };
}

//short username display
function display_user_name(?array $user): string{
    if (!$user) {
        return '';
    }

    $name = trim((string) ($user['nome'] ?? ''));

    if ($name === '') {
        return (string) ($user['username'] ?? 'Conta');
    }

    $parts = preg_split('/\s+/', $name) ?: [];
    return $parts[0] ?? $name;
}

//user state
function current_user(PDO $pdo): ?array{
    static $cache = [];

    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        return null;
    }

    if (array_key_exists($userId, $cache)) {
        return $cache[$userId];
    }

    $stmt = $pdo->prepare('SELECT * FROM utilizadores WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch() ?: null;

    if (!$user || (int) $user['ativo'] !== 1) {
        unset($_SESSION['user_id']);
        $cache[$userId] = null;
        return null;
    }

    $cache[$userId] = $user;

    return $user;
}

//active user check
function is_logged_in(PDO $pdo): bool{
    return current_user($pdo) !== null;
}

//admin check
function is_admin(PDO $pdo): bool{
    return (current_user($pdo)['role'] ?? '') === 'admin';
}

//inicio sessao depois auntenticacao
function login_user(array $user, ?PDO $pdo = null): void{
    $_SESSION['user_id'] = (int) $user['id'];

    if (($user['role'] ?? '') !== 'admin' && guest_cart_items() !== []) {
        $_SESSION['cart_merge_pending'] = 1;
    } 
    else {
        unset($_SESSION['cart_merge_pending']);
    }
}

//logout
function logout_user(): void{
    unset($_SESSION['user_id'], $_SESSION['cart_merge_pending']);
}

//protecao de paginas privadas
function require_login_page(PDO $pdo): void{
    if (is_logged_in($pdo)) {
        return;
    }

    redirect('auth.php');
}

//protecao de paginas de administracao
function require_admin_page(PDO $pdo): void{
    if (is_admin($pdo)) {
        return;
    }

    redirect('index.php');
}

//leitura de pesquisa segura
function search_value(): string{
    return trim((string) ($_GET['q'] ?? ''));
}

//classe de ativo para menus e filtros
function active_class(bool $condition, string $class = 'is-active'): string{
    return $condition ? $class : '';
}

//checked state for checkboxes
function checked_attr(bool $checked): string{
    return $checked ? 'checked' : '';
}

//checked state for select options
function selected_attr(string|int $value, string|int $current): string{
    return (string) $value === (string) $current ? 'selected' : '';
}

//normalizacao de mapa produto => quantidade
function normalize_cart_map(array $cart): array{
    $clean = [];

    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $clean[$productId] = $quantity;
    }

    return $clean;
}

//cesto temporario de visitantes
function guest_cart_items(): array{
    $cart = $_SESSION['guest_cart'] ?? $_SESSION['cart'] ?? [];
    $cart = normalize_cart_map((array) $cart);

    $_SESSION['guest_cart'] = $cart;
    unset($_SESSION['cart']);

    return $cart;
}

//limpar cesto temporario
function clear_guest_cart(): void{
    unset($_SESSION['guest_cart'], $_SESSION['cart'], $_SESSION['cart_merge_pending']);
}

//cesto atual, em base de dados se existir login
function cart_items(?PDO $pdo = null): array{
    if ($pdo) {
        $user = current_user($pdo);

        if ($user) {
            return fetch_cart_map($pdo, (int) $user['id']);
        }
    }

    return guest_cart_items();
}

//quantidade total de artigos no carrinho
function cart_count(?PDO $pdo = null): int{
    return array_sum(cart_items($pdo));
}

//favoritos da conta atual guardados em base de dados
function favorite_items(PDO $pdo): array{
    $user = current_user($pdo);

    if (!$user) {
        return [];
    }

    return fetch_favorite_ids($pdo, (int) $user['id']);
}

//indicacao rapida se um produto esta favoritado
function is_favorite_product(PDO $pdo, int $productId): bool{
    $user = current_user($pdo);

    if (!$user) {
        return false;
    }

    return favorite_exists($pdo, (int) $user['id'], $productId);
}

//alteracao do estado favorito do produto
function toggle_favorite_product(PDO $pdo, int $productId): bool{
    $user = current_user($pdo);

    if (!$user) {
        throw new RuntimeException('Tens de iniciar sessão para usar os favoritos.');
    }

    return toggle_favorite_row($pdo, (int) $user['id'], $productId);
}

//indicacao da quantidade de favoritos da conta atual
function favorite_count(PDO $pdo): int{
    return count(favorite_items($pdo));
}

//insercao simples de artigos no carrinho
function add_to_cart(int $pid, int $qty, ?PDO $pdo = null): void{
    if ($pdo) {
        $user = current_user($pdo);

        if ($user) {
            $product = fetch_product($pdo, $pid);

            if (!$product || (int) $product['stock'] <= 0) {
                throw new RuntimeException('Este produto está sem stock.');
            }

            $cart = cart_items($pdo);
            $next = min(
                (int) $product['stock'],
                (int) ($cart[$pid] ?? 0) + max(1, $qty)
            );

            set_cart_item_row($pdo, (int) $user['id'], $pid, $next);
            return;
        }
    }

    $cart = guest_cart_items();
    $cart[$pid] = ($cart[$pid] ?? 0) + max(1, $qty);
    $_SESSION['guest_cart'] = $cart;
}

//atualizacao de quantidades do carrinho
function set_cart_quantity(int $pid, int $qty, ?PDO $pdo = null): void{
    if ($pdo) {
        $user = current_user($pdo);

        if ($user) {
            if ($qty <= 0) {
                delete_cart_item_row($pdo, (int) $user['id'], $pid);
                return;
            }

            set_cart_item_row($pdo, (int) $user['id'], $pid, $qty);
            return;
        }
    }

    $cart = guest_cart_items();

    if ($qty <= 0) {
        unset($cart[$pid]);
    }
    else {
        $cart[$pid] = $qty;
    }

    $_SESSION['guest_cart'] = $cart;
}

//limpeza total do carrinho
function clear_cart(?PDO $pdo = null): void{
    if ($pdo) {
        $user = current_user($pdo);

        if ($user) {
            clear_cart_rows($pdo, (int) $user['id']);
            return;
        }
    }

    clear_guest_cart();
}

//estado da pergunta para juntar cesto visitante
function should_show_cart_merge_prompt(PDO $pdo): bool{
    return current_user($pdo) !== null
        && !empty($_SESSION['cart_merge_pending'])
        && guest_cart_items() !== [];
}

//juntar cesto de visitante ao cesto da conta
function merge_guest_cart_to_user(PDO $pdo): void{
    $user = current_user($pdo);

    if (!$user) {
        return;
    }

    foreach (guest_cart_items() as $pid => $qty) {
        $product = fetch_product($pdo, (int) $pid);

        if (!$product || (int) $product['stock'] <= 0) {
            continue;
        }

        add_to_cart((int) $pid, min((int) $qty, (int) $product['stock']), $pdo);
    }

    clear_guest_cart();
}

//remocao segura de imagem antiga
function delete_uploaded_image(?string $rel): void{
    if (!$rel) {
        return;
    }

    $name = basename($rel);

    if ($name === '' || $name === '.' || $name === '..') {
        return;
    }

    $path = PRODUCT_UPLOAD_DIR . '/' . $name;

    if (is_file($path)) {
        unlink($path);
    }
}

//upload seguro das imagens de produto
function upload_product_image(array $file, ?string $oldImg = null): ?string{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return $oldImg;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível carregar a imagem do produto.');
    }

    $size = (int) ($file['size'] ?? 0);

    if ($size <= 0 || $size > MAX_IMAGE_SIZE) {
        throw new RuntimeException('A imagem deve ter no maximo 5 MB.');
    }

    $info = @getimagesize($file['tmp_name'] ?? '');

    if (!$info || !isset($info['mime'])) {
        throw new RuntimeException('Seleciona uma imagem valida em JPG, PNG ou WEBP.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$info['mime']])) {
        throw new RuntimeException('Formato de imagem não suportado.');
    }

    if (!is_dir(PRODUCT_UPLOAD_DIR) && !mkdir(PRODUCT_UPLOAD_DIR, 0777, true) && !is_dir(PRODUCT_UPLOAD_DIR)) {
        throw new RuntimeException('Não foi possível preparar a pasta de uploads do produto.');
    }

    $name = date('YmdHis') . '_' . uniqid() . '.' . $allowed[$info['mime']];
    $path = PRODUCT_UPLOAD_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('Falhou o upload da imagem do produto.');
    }

    delete_uploaded_image($oldImg);

    return $name;
}

//erro duplicado
function is_unique_error(Throwable $error): bool{
    return str_contains($error->getMessage(), '1062')
        || str_contains($error->getMessage(), 'SQLSTATE[23000]');
}

//mensagem segura para erros da base de dados
function public_error_message(Throwable $error, string $fallback = 'Não foi possível concluir a operação.'): string{
    if (is_unique_error($error)) {
        return 'Já existe uma conta com esse utilizador ou email.';
    }

    if ($error instanceof PDOException) {
        return $fallback;
    }

    return $error->getMessage();
}
