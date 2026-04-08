<?php

declare(strict_types=1);

namespace Pocker\Controller;

class DetailController extends BaseController
{
    public function handle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/list.php');
        }

        $cacheKey = 'pokemon_detail_' . $id;
        $dataStart = microtime(true);
        $cached   = $this->cache->get($cacheKey);
        $this->dataSource = 'CACHE Redis';

        if ($cached === null) {
            $this->dataSource = 'PostgreSQL';

            $pokemon    = $this->pokemonRepo->findById($id);
            if (!$pokemon) {
                $this->redirect('/list.php');
            }

            $types      = $this->pokemonRepo->findTypes($id);
            $talents    = $this->pokemonRepo->findTalents($id);
            $attaques   = $this->pokemonRepo->findAttaques($id);
            $evolutions = $this->pokemonRepo->findEvolutions($id);

            $cached = compact('pokemon', 'types', 'talents', 'attaques', 'evolutions');
            $this->cache->set($cacheKey, $cached);
        }
        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        // Accès explicite aux clés — jamais extract()
        $pokemon    = $cached['pokemon'];
        $types      = $cached['types'];
        $talents    = $cached['talents'];
        $attaques   = $cached['attaques'];
        $evolutions = $cached['evolutions'];

        $statMax        = ['pv' => 255, 'attaque' => 190, 'defense' => 230,
                           'atk_spe' => 194, 'def_spe' => 230, 'vitesse' => 200];
        $pageTitle      = ucfirst($pokemon['nom']);
        $dataSource     = $this->dataSource;
        $startTime      = $this->startTime;
        $dataElapsedMs  = $this->dataElapsedMs;
        $h              = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/detail.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
