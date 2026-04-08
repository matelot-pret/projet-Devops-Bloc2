<?php
define('CACHE_ENABLED', false);
require_once __DIR__ . '/../autoload.php';
use Pocker\Controller\CompareController;
(new CompareController())->handle();
