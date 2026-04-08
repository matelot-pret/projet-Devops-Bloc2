<?php

declare(strict_types=1);

namespace Pocker\Cache;

/**
 * Cache factice (Null Object Pattern) pour apache1.
 *
 * Toutes les méthodes ne font rien / retournent null.
 * Le code des pages est identique sur apache1 et apache2 —
 * seule l'implémentation du cache change.
 *
 * Remplace l'ancien cache_disabled.php avec ses stubs de fonctions globales.
 * Ici, c'est propre : une classe qui implémente le même contrat (CacheInterface).
 */
class NullCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return null; // Toujours un cache miss
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        return true; // On fait semblant d'avoir réussi
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function deletePattern(string $pattern): void
    {
        // Rien à faire
    }

    public function invalidateAllPokemon(): void
    {
        // Rien à faire
    }
}
