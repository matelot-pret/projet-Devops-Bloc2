<?php

declare(strict_types=1);

namespace Pocker\Controller;

use Pocker\Database\Database;
use Pocker\Database\DatabaseInterface;
use Pocker\Cache\CacheInterface;
use Pocker\Cache\RedisCache;
use Pocker\Cache\NullCache;
use Pocker\Repository\PokemonRepository;
use Pocker\Repository\PokemonRepositoryInterface;
use Pocker\Repository\TypeRepository;
use Pocker\Repository\TypeRepositoryInterface;
use Pocker\Repository\GenerationRepository;
use Pocker\Repository\GenerationRepositoryInterface;
use Pocker\Service\PokemonService;

/**
 * Contrôleur de base.
 *
 * Regroupe tout ce qui est commun à chaque page :
 * - Démarrage de session (pour CSRF)
 * - Désactivation de display_errors
 * - Instanciation des dépendances (DB, cache, repositories, service)
 * - Méthodes utilitaires (redirect, h, ...)
 *
 * DRY : avant, chaque page répétait define('CACHE_ENABLED', true),
 * require_once 'includes/db.php', require_once 'includes/cache.php'.
 * Maintenant c'est ici, une seule fois.
 *
 * Principe DI (Dependency Injection) : le contrôleur reçoit ses dépendances
 * plutôt que de les créer lui-même, ce qui facilite les tests.
 */
abstract class BaseController
{
    protected DatabaseInterface             $db;
    protected CacheInterface       $cache;
    protected PokemonRepositoryInterface    $pokemonRepo;
    protected TypeRepositoryInterface       $typeRepo;
    protected GenerationRepositoryInterface $genRepo;
    protected PokemonService       $pokemonService;
    protected float                $startTime;
    protected string               $dataSource = 'PostgreSQL';
    /** Temps passé uniquement sur l'accès aux données (cache ou DB), en ms. */
    protected float                $dataElapsedMs = 0.0;

    public function __construct()
    {
        // ── Sécurité de base ─────────────────────────────────────────────
        // On désactive l'affichage des erreurs côté client.
        // Les erreurs sont loguées côté serveur via error_log().
        // Afficher les erreurs PHP donne à un attaquant le schéma de la
        // base, les chemins de fichiers, les noms de variables...
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL); // On les capture quand même, pour les logs

        // ── Session pour CSRF ─────────────────────────────────────────────
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,  // Cookie inaccessible au JavaScript
                'cookie_samesite' => 'Strict', // Protection CSRF supplémentaire
            ]);
        }

        // ── Timer de performance ──────────────────────────────────────────
        $this->startTime = microtime(true);

        // ── Dépendances ───────────────────────────────────────────────────
        $this->db    = Database::getInstance();
        $this->cache = $this->resolveCache();

        $this->pokemonRepo = new PokemonRepository($this->db);
        $this->typeRepo    = new TypeRepository($this->db);
        $this->genRepo     = new GenerationRepository($this->db);

        $this->pokemonService = new PokemonService(
            $this->pokemonRepo,
            $this->typeRepo,
            $this->genRepo,
            $this->cache,
            $this->db
        );
    }

    /**
     * Détermine quelle implémentation de cache utiliser.
     *
     * CACHE_ENABLED est défini dans le point d'entrée (index.php) de chaque
     * site : true pour apache2, false pour apache1.
     *
     * OCP : on peut ajouter MemcachedCache sans toucher à ce code —
     * on ajouterait juste une condition ici et une nouvelle classe.
     */
    private function resolveCache(): CacheInterface
    {
        if (defined('CACHE_ENABLED') && CACHE_ENABLED) {
            return RedisCache::getInstance();
        }
        return new NullCache();
    }

    /**
     * Redirige vers une URL et arrête l'exécution.
     * Validation de l'URL pour éviter les redirections ouvertes (Open Redirect).
     */
    protected function redirect(string $path): never
    {
        // On n'accepte que des chemins relatifs (commençant par /)
        // Pas de http://, pas de //external.com, etc.
        // str_starts_with('/') ne suffit pas : //evil.com commence aussi par /
        if (!preg_match('#^/[^/]#', $path) && $path !== '/') {
            $path = '/';
        }
        header('Location: ' . $path);
        exit;
    }

    /**
     * Échappe une valeur pour l'affichage HTML.
     * Alias court de htmlspecialchars() avec les bonnes options.
     *
     * À utiliser sur TOUTE valeur affichée dans le HTML pour éviter le XSS.
     * XSS (Cross-Site Scripting) : injection de code JavaScript malveillant
     * dans la page via des données non échappées.
     */
    protected function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Retourne le temps de réponse en millisecondes.
     */
    protected function getElapsedMs(): float
    {
        return round((microtime(true) - $this->startTime) * 1000, 2);
    }

    /**
     * Méthode principale à implémenter dans chaque contrôleur.
     */
    abstract public function handle(): void;
}
