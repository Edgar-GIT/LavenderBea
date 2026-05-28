<?php
declare(strict_types=1);

require __DIR__ . '/../../core/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido.';
    exit;
}

require_login_page($pdo);

$decision = (string) ($_POST['decision'] ?? '');

if ($decision === 'merge') {
    merge_guest_cart_to_user($pdo);
    redirect('cart.php');
}

clear_guest_cart();
redirect(is_admin($pdo) ? 'admin.php' : 'account.php');

