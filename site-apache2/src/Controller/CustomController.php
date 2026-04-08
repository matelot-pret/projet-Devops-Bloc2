<?php

declare(strict_types=1);

namespace Pocker\Controller;

class CustomController extends BaseController
{
    public function handle(): void
    {
        $cacheKey  = 'custom_list';
        $dataStart = microtime(true);
        $cached    = $this->cache->get($cacheKey);
        $this->dataSource = 'CACHE Redis';

        if ($cached === null) {
            $this->dataSource = 'PostgreSQL';

            $pokemonList = $this->pokemonRepo->findAllCustom();
            $cached      = compact('pokemonList');
            $this->cache->set($cacheKey, $cached);
        }
        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        $pokemonList = $cached['pokemonList'];

        $pageTitle     = 'Mes Pokémon';
        $dataSource    = $this->dataSource;
        $startTime     = $this->startTime;
        $dataElapsedMs = $this->dataElapsedMs;
        $h             = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/custom.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
