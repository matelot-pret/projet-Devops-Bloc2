<?php

declare(strict_types=1);

namespace Pocker\Controller;

class WeaknessesController extends BaseController
{
    public function handle(): void
    {
        $cacheKey  = 'weaknesses_table';
        $dataStart = microtime(true);
        $cached    = $this->cache->get($cacheKey);
        $this->dataSource = 'CACHE Redis';

        if ($cached === null) {
            $this->dataSource = 'PostgreSQL';

            $types      = $this->typeRepo->findAll();
            $faiblesses = $this->typeRepo->findWeaknesses();

            // Construction de la matrice en mémoire
            $matrix = [];
            foreach ($faiblesses as $f) {
                $matrix[$f['type_attaquant_id']][$f['type_defenseur_id']] = (float)$f['multiplicateur'];
            }

            $cached = compact('types', 'matrix');
            // TTL long : les types ne changent jamais
            $this->cache->set($cacheKey, $cached, 3600);
        }
        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        $types  = $cached['types'];
        $matrix = $cached['matrix'];

        $pageTitle     = 'Table des Faiblesses';
        $dataSource    = $this->dataSource;
        $startTime     = $this->startTime;
        $dataElapsedMs = $this->dataElapsedMs;
        $h             = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/weaknesses.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
