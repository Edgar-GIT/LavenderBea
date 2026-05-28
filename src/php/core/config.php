<?php
declare(strict_types=1);

define('APP_NAME', 'Lavender Bea');
define('BASE_PATH', dirname(__DIR__, 3));
define('DB_HOST', getenv('LAVENDER_DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('LAVENDER_DB_PORT') ?: '3306');
define('DB_NAME', getenv('LAVENDER_DB_NAME') ?: 'lavender_bea');
define('DB_USER', getenv('LAVENDER_DB_USER') ?: 'root');
define('DB_PASS', getenv('LAVENDER_DB_PASS') ?: 'root');
define('DEFAULT_TIMEZONE', 'Europe/Lisbon');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('APP_STORAGE_PATH', getenv('LAVENDER_STORAGE_PATH') ?: BASE_PATH . '/uploads');
define('PRODUCT_UPLOAD_DIR', APP_STORAGE_PATH . '/products');
define('ADMIN_NAME', 'Administrador Lavender Bea');
define('ADMIN_USERNAME', 'admin');
define('ADMIN_EMAIL', 'admin@lavenderbea.local');
define('ADMIN_PASSWORD', 'edgarL123#');

date_default_timezone_set(DEFAULT_TIMEZONE);

?>