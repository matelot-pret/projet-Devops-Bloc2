<?php

declare(strict_types=1);

namespace Pocker\Controller;

/**
 * Contrôleur de la page de démonstration du cache.
 *
 * Cette page fait deux choses :
 * 1. Affiche l'état du cache en temps réel (hit / miss / TTL restant)
 * 2. Permet de vider le cache manuellement pour observer le cycle complet
 */
class DemoController extends BaseController
{
    public function handle(): void
    {
        // Action : vider le cache (bouton "Vider le cache")
        if (isset($_POST['action']) && $_POST['action'] === 'flush') {
            $this->cache->invalidateAllPokemon();
            header('Location: /demo.php');
            exit;
        }

        // ── Mesure DB : requête PostgreSQL avec latence simulée ──────────
        $tDb   = microtime(true);
        $demo  = $this->pokemonRepo->findPaginated('', '', '', 6, 0);
        $dbMs  = round((microtime(true) - $tDb) * 1000, 2);

        // ── Mesure cache : lecture Redis pure ─────────────────────────────
        // On isole le microtime() uniquement autour du get() Redis.
        // C'est la vraie mesure — pas de PHP, pas de rendu autour.
        $cacheKey = 'demo_benchmark';
        $tCache   = microtime(true);
        $cached   = $this->cache->get($cacheKey);
        $cacheMs  = round((microtime(true) - $tCache) * 1000, 2);

        if ($cached === null) {
            $this->cache->set($cacheKey, ['ts' => time()], 60);
            $cacheHit = false;
        } else {
            $cacheHit = true;
        }

        // dataAccessMs pour la bannière = le temps de l'accès données principal
        $this->dataAccessMs = $cacheHit ? $cacheMs : $dbMs;
        $this->dataSource   = $cacheHit ? 'CACHE Redis' : 'PostgreSQL';

        $pageTitle  = 'Démonstration Cache';
        $dataSource = $this->dataSource;
        $startTime  = $this->startTime;
        $dataAccessMs = $this->dataAccessMs;
        $isCached   = defined('CACHE_ENABLED') && CACHE_ENABLED;
        $latencyMs  = defined('DB_SIMULATE_LATENCY_MS') ? DB_SIMULATE_LATENCY_MS : 0;
        $h          = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/demo.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
