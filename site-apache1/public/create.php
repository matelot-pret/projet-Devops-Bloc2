<?php
define('CACHE_ENABLED', false);
require_once __DIR__ . '/../autoload.php';
use Pocker\Controller\CreateController;
(new CreateController())->handle();
