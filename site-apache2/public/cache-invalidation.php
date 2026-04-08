<?php
define('DB_SIMULATE_LATENCY_MS', 200);
define('CACHE_ENABLED', true);

require_once __DIR__ . '/../autoload.php';

use Pocker\Controller\CacheInvalidationController;

(new CacheInvalidationController())->handle();
