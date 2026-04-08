<?php

declare(strict_types=1);

namespace Pocker\Controller;

class IndexController extends BaseController
{
    public function handle(): void
    {
        $cacheKey = 'stats_globales';
        $dataStart = microtime(true);
        $stats    = $this->cache->get($cacheKey);
        $this->dataSource = 'CACHE Redis';

        if ($stats === null) {
            $this->dataSource = 'PostgreSQL';

            $globalStats = $this->pokemonRepo->getGlobalStats();
            $featured    = $this->pokemonRepo->findFeatured();

            $stats = array_merge($globalStats, ['featured' => $featured]);
            $this->cache->set($cacheKey, $stats);
        }
        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        $totalPokemon  = $stats['total_pokemon']  ?? 0;
        $totalCustom   = $stats['total_custom']   ?? 0;
        $totalTypes    = $stats['total_types']    ?? 0;
        $totalAttaques = $stats['total_attaques'] ?? 0;
        $totalTalents  = $stats['total_talents']  ?? 0;
        $totalGens     = $stats['total_gens']     ?? 0;
        $featured      = $stats['featured']       ?? [];

        $pageTitle      = 'Accueil';
        $dataSource     = $this->dataSource;
        $startTime      = $this->startTime;
        $dataElapsedMs  = $this->dataElapsedMs;
        $h              = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/index.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
