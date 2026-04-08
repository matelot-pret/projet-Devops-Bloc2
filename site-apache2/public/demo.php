<?php
// Latence simulée par requête PostgreSQL (ms) — rend la démo cache visible
define('DB_SIMULATE_LATENCY_MS', 200);
// Point d'entrée apache2 — avec cache Redis
define('CACHE_ENABLED', true);

require_once __DIR__ . '/../autoload.php';

use Pocker\Controller\DemoController;

(new DemoController())->handle();
