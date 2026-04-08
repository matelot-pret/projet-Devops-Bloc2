<?php
// Point d'entrée apache1 — SANS cache
define('CACHE_ENABLED', false);

require_once __DIR__ . '/../autoload.php';

use Pocker\Controller\IndexController;

(new IndexController())->handle();
