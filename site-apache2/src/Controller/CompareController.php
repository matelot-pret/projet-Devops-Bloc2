<?php

declare(strict_types=1);

namespace Pocker\Controller;

class CompareController extends BaseController
{
    public function handle(): void
    {
        // Recherche AJAX — point d'entrée séparé
        if (isset($_GET['search_pokemon'])) {
            $this->handleAjaxSearch();
            return;
        }

        $id1 = (int)($_GET['id1'] ?? 0);
        $id2 = (int)($_GET['id2'] ?? 0);

        $pokemon1 = $pokemon2 = $types1 = $types2 = null;
        $dataStart = microtime(true);

        if ($id1 > 0 && $id2 > 0) {
            // Clé symétrique : compare(1,25) == compare(25,1)
            $cacheKey = 'pokemon_compare_' . min($id1, $id2) . '_' . max($id1, $id2);
            $cached   = $this->cache->get($cacheKey);
            $this->dataSource = 'CACHE Redis';

            if ($cached === null) {
                $this->dataSource = 'PostgreSQL';

                $pokemon1 = $this->pokemonRepo->findById($id1);
                $pokemon2 = $this->pokemonRepo->findById($id2);
                $types1   = $this->pokemonRepo->findTypes($id1);
                $types2   = $this->pokemonRepo->findTypes($id2);

                $cached = compact('pokemon1', 'pokemon2', 'types1', 'types2');
                $this->cache->set($cacheKey, $cached);
            }

            $pokemon1 = $cached['pokemon1'];
            $pokemon2 = $cached['pokemon2'];
            $types1   = $cached['types1'];
            $types2   = $cached['types2'];
        } else {
            $this->dataSource = 'PostgreSQL';
        }

        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        $allPokemon = $this->pokemonRepo->findFirstHundred();

        $pageTitle     = 'Comparer';
        $dataSource    = $this->dataSource;
        $startTime     = $this->startTime;
        $dataElapsedMs = $this->dataElapsedMs;
        $h             = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/compare.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }

    /**
     * Endpoint AJAX pour la recherche de Pokémon dans compare.php.
     * Retourne du JSON — pas de HTML.
     */
    private function handleAjaxSearch(): void
    {
        $query = trim($_GET['search_pokemon'] ?? '');

        // Longueur minimale pour éviter des requêtes trop larges
        if (mb_strlen($query) < 1) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $results = $this->pokemonRepo->search($query);

        header('Content-Type: application/json');
        // JSON_HEX_TAG protège contre l'injection HTML dans le JSON
        echo json_encode($results, JSON_HEX_TAG | JSON_HEX_AMP);
        exit;
    }
}
