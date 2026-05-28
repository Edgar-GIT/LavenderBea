<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

require_once BASE_PATH . '/src/php/repositories/categories/find_by_slug.php';
require_once BASE_PATH . '/src/php/repositories/categories/list.php';

require_once BASE_PATH . '/src/php/repositories/cart/clear.php';
require_once BASE_PATH . '/src/php/repositories/cart/delete.php';
require_once BASE_PATH . '/src/php/repositories/cart/list.php';
require_once BASE_PATH . '/src/php/repositories/cart/upsert.php';

require_once BASE_PATH . '/src/php/repositories/favorites/exists.php';
require_once BASE_PATH . '/src/php/repositories/favorites/list.php';
require_once BASE_PATH . '/src/php/repositories/favorites/toggle.php';

require_once BASE_PATH . '/src/php/repositories/logs/list.php';

require_once BASE_PATH . '/src/php/repositories/products/delete.php';
require_once BASE_PATH . '/src/php/repositories/products/featured.php';
require_once BASE_PATH . '/src/php/repositories/products/find.php';
require_once BASE_PATH . '/src/php/repositories/products/find_by_ids.php';
require_once BASE_PATH . '/src/php/repositories/products/list.php';
require_once BASE_PATH . '/src/php/repositories/products/related.php';
require_once BASE_PATH . '/src/php/repositories/products/save.php';

require_once BASE_PATH . '/src/php/repositories/users/active.php';
require_once BASE_PATH . '/src/php/repositories/users/delete.php';
require_once BASE_PATH . '/src/php/repositories/users/find.php';
require_once BASE_PATH . '/src/php/repositories/users/find_by_login.php';
require_once BASE_PATH . '/src/php/repositories/users/list.php';
require_once BASE_PATH . '/src/php/repositories/users/save.php';

boot_app();
$pdo = db();
