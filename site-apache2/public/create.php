<?php
define('CACHE_ENABLED', true);
require_once __DIR__ . '/../autoload.php';
use Pocker\Controller\CreateController;
(new CreateController())->handle();
