<?php

declare(strict_types=1);

namespace Pocker\Controller;

/**
 * Contrôleur de la page de démonstration de l'invalidation du cache.
 *
 * Scénario pédagogique :
 *
 * 1. On choisit un Pokémon et on modifie son nom.
 * 2. La modification est écrite en base de données (PostgreSQL).
 * 3. Selon le toggle "Invalidation activée" :
 *    - OFF → le cache Redis n'est PAS invalidé après la modification.
 *             La lecture depuis Redis retourne l'ANCIEN nom.
 *             La lecture depuis PostgreSQL retourne le NOUVEAU nom.
 *             → On voit clairement que cache et DB sont désynchronisés.
 *    - ON  → le cache est invalidé après la modification.
 *             Les deux lectures retournent le NOUVEAU nom.
 *             → Le cache reste cohérent avec la base.
 *
 * 4. Sans invalidation, le TTL finit par expirer (300s) et Redis se
 *    resynchronise automatiquement — le TTL est le filet de sécurité.
 */
class CacheInvalidationController extends BaseController
{
    public function handle(): void
    {
        $selectedId       = (int)($_GET['id'] ?? 0);
        $invalidationOn   = isset($_GET['invalidation']) && $_GET['invalidation'] === '1';
        $message          = '';
        $modified         = false;

        // ── POST : modification du nom ────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId         = (int)($_POST['id'] ?? 0);
            $newNom         = trim($_POST['nom'] ?? '');
            $invalidationOn = isset($_POST['invalidation']);

            if ($postId > 0 && $newNom !== '' && mb_strlen($newNom) <= 100
                && preg_match('/^[\p{L}\p{N}\s\-]+$/u', $newNom)) {

                // Écriture en base — toujours, que l'invalidation soit on ou off
                $ok = $this->pokemonRepo->updateNom($postId, $newNom);

                if ($ok) {
                    $modified   = true;
                    $selectedId = $postId;

                    if ($invalidationOn) {
                        // ON : on invalide la clé Redis pour ce Pokémon
                        // → prochain cache->get() sera un MISS → Redis sera resynchronisé
                        $this->cache->delete('pokemon_detail_' . $postId);
                        $message = 'invalidation_on';
                    } else {
                        // OFF : on ne touche pas au cache
                        // → Redis garde encore l'ancien nom jusqu'à expiration du TTL
                        $message = 'invalidation_off';
                    }
                }
            }

            // Redirect-after-POST pour éviter le resoumission du formulaire au F5
            $inv = $invalidationOn ? '1' : '0';
            header("Location: /cache-invalidation.php?id=$selectedId&invalidation=$inv&msg=$message");
            exit;
        }

        $message        = $_GET['msg'] ?? '';
        $invalidationOn = isset($_GET['invalidation']) && $_GET['invalidation'] === '1';

        // ── Données pour l'affichage ──────────────────────────────────────
        $pokemon         = null;
        $fromCache       = null;   // valeur lue depuis Redis (peut être stale)
        $fromDb          = null;   // valeur lue directement en PostgreSQL (toujours fraîche)
        $cacheKeyDetail  = '';

        if ($selectedId > 0) {
            $cacheKeyDetail = 'pokemon_detail_' . $selectedId;

            // Lecture 1 : depuis le cache Redis (ou miss si invalidé/expiré)
            $_t0           = microtime(true);
            $cachedData    = $this->cache->get($cacheKeyDetail);
            $cacheReadMs   = round((microtime(true) - $_t0) * 1000, 2);

            if ($cachedData !== null) {
                $fromCache = $cachedData['pokemon'] ?? null;
            }

            // Lecture 2 : directement en base de données (bypass total du cache)
            $_t1       = microtime(true);
            $fromDb    = $this->pokemonRepo->findByIdDirect($selectedId);
            $dbReadMs  = round((microtime(true) - $_t1) * 1000, 2);

            // Si le cache était vide, on le peuple maintenant avec les données fraîches
            if ($cachedData === null && $fromDb !== null) {
                $types      = $this->pokemonRepo->findTypes($selectedId);
                $talents    = $this->pokemonRepo->findTalents($selectedId);
                $attaques   = $this->pokemonRepo->findAttaques($selectedId);
                $evolutions = $this->pokemonRepo->findEvolutions($selectedId);
                $this->cache->set($cacheKeyDetail, compact(
                    'pokemon', 'types', 'talents', 'attaques', 'evolutions'
                ) + ['pokemon' => $fromDb]);
            }

            $pokemon = $fromDb; // pour le formulaire de modification
        }

        // ── Recherche de Pokémon ──────────────────────────────────────────
        $searchResults = [];
        $searchQuery   = trim($_GET['q'] ?? '');
        if ($searchQuery !== '' && mb_strlen($searchQuery) >= 2) {
            $searchResults = $this->pokemonRepo->search($searchQuery);
        }

        $this->dataAccessMs = $cacheReadMs ?? 0.0;
        $this->dataSource   = $fromCache !== null ? 'CACHE Redis' : 'PostgreSQL';

        $pageTitle    = 'Démo Invalidation Cache';
        $dataSource   = $this->dataSource;
        $startTime    = $this->startTime;
        $dataAccessMs = $this->dataAccessMs;
        $h            = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/cache-invalidation.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
