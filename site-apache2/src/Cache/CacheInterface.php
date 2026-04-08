<?php

declare(strict_types=1);

namespace Pocker\Cache;

/**
 * Interface définissant le contrat de tout cache.
 *
 * Une interface est un contrat : elle dit QUOI faire, pas COMMENT.
 * Toute classe qui implémente CacheInterface DOIT avoir ces méthodes.
 *
 * Avantage (principe OCP — Open/Closed) :
 * - Le code qui utilise le cache dépend de l'interface, pas de Redis.
 * - Si on veut passer à Memcached demain, on crée MemcachedCache qui
 *   implémente la même interface. Le reste du code ne change pas.
 * - Sur apache1, on utilise NullCache (qui ne fait rien).
 *   Sur apache2, on utilise RedisCache.
 *   La page PHP ne sait pas laquelle elle a — elle appelle juste get/set.
 */
interface CacheInterface
{
    /**
     * Récupère une valeur depuis le cache.
     * Retourne null si la clé n'existe pas ou a expiré.
     */
    public function get(string $key): mixed;

    /**
     * Stocke une valeur avec une durée de vie en secondes.
     */
    public function set(string $key, mixed $value, int $ttl = 300): bool;

    /**
     * Supprime une clé du cache.
     */
    public function delete(string $key): bool;

    /**
     * Supprime toutes les clés correspondant au pattern (ex: "pokemon_*").
     */
    public function deletePattern(string $pattern): void;

    /**
     * Invalide tout le cache lié aux Pokémon.
     * Appelé après création d'un Pokémon personnalisé.
     */
    public function invalidateAllPokemon(): void;
}
