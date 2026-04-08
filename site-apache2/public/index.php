<?php
// Point d'entrée apache2 — avec cache Redis
define('CACHE_ENABLED', true);

require_once __DIR__ . '/../autoload.php';

use Pocker\Controller\IndexController;

(new IndexController())->handle();
