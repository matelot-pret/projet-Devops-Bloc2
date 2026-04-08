<?php

declare(strict_types=1);

namespace Pocker\Controller;

/**
 * Contrôleur de la liste paginée des Pokémon.
 */
class ListController extends BaseController
{
    private const PER_PAGE = 24;

    public function handle(): void
    {
        $perPage = self::PER_PAGE;

        // ── Lecture et validation des paramètres GET ──────────────────────
        // On ne fait jamais confiance aux données GET/POST brutes.

        $search     = trim($_GET['q']    ?? '');
        $filterGen  = trim($_GET['gen']  ?? '');
        $filterType = trim($_GET['type'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $offset     = ($page - 1) * $perPage;

        // Whitelist : le filtre type doit être un type existant en base
        if ($filterType !== '' && !$this->typeRepo->isValidType($filterType)) {
            $filterType = '';
        }

        // Whitelist : la génération doit être un entier positif
        if ($filterGen !== '' && !ctype_digit($filterGen)) {
            $filterGen = '';
        }

        // ── Cache ─────────────────────────────────────────────────────────
        $cacheKey = 'pokemons_list_' . md5("$filterType|$filterGen|$search|$page");
        $dataStart = microtime(true);
        $cached   = $this->cache->get($cacheKey);
        $this->dataSource = 'CACHE Redis';

        if ($cached === null) {
            $this->dataSource = 'PostgreSQL';

            $result = $this->pokemonRepo->findPaginated(
                $search, $filterType, $filterGen, $perPage, $offset
            );
            $pokemonList = $result['list'];
            $totalCount  = $result['total'];
            $totalPages  = max(1, (int)ceil($totalCount / $perPage));

            $allTypes = $this->typeRepo->findAll();
            $allGens  = $this->genRepo->findAll();

            // On stocke uniquement les données — pas d'objet, pas de closures
            $cached = compact('pokemonList', 'totalCount', 'totalPages', 'allTypes', 'allGens');
            $this->cache->set($cacheKey, $cached);
        }
        $this->dataElapsedMs = round((microtime(true) - $dataStart) * 1000, 2);

        // Accès explicite aux clés — pas d'extract() dangereux
        $pokemonList = $cached['pokemonList'];
        $totalCount  = $cached['totalCount'];
        $totalPages  = $cached['totalPages'];
        $allTypes    = $cached['allTypes'];
        $allGens     = $cached['allGens'];

        $pageTitle      = 'Pokédex';
        $dataSource     = $this->dataSource;
        $startTime      = $this->startTime;
        $dataElapsedMs  = $this->dataElapsedMs;
        $h              = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/list.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
