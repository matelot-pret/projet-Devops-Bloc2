<?php

declare(strict_types=1);

namespace Pocker\Cache;

use Redis;
use Exception;

/**
 * Implémentation du cache utilisant Redis.
 * Utilisée sur apache2 (avec cache).
 *
 * Les données sont sérialisées en JSON avant stockage.
 * On évite serialize()/unserialize() de PHP qui peut exécuter du code
 * arbitraire si les données sont corrompues ou manipulées (Object Injection).
 * JSON ne peut contenir que des données — aucun risque d'exécution de code.
 */
class RedisCache implements CacheInterface
{
    private static ?RedisCache $instance = null;
    private ?Redis $redis = null;
    private bool $connected = false;

    private function __construct()
    {
        $host = getenv('REDIS_HOST') ?: 'redis';
        $pass = getenv('REDIS_PASSWORD') ?: '';

        try {
            $this->redis = new Redis();
            $this->redis->connect($host, 6379, 2.0); // timeout 2s
            if ($pass !== '') {
                $this->redis->auth($pass);
            }
            $this->connected = true;
        } catch (Exception $e) {
            error_log('Redis connection failed: ' . $e->getMessage());
            $this->connected = false;
        }
    }

    public static function getInstance(): RedisCache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key): mixed
    {
        if (!$this->connected) return null;

        $val = $this->redis->get($key);
        if ($val === false) return null;

        // json_decode retourne null si le JSON est invalide — pas d'exécution de code
        return json_decode($val, true);
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        if (!$this->connected) return false;
        // json_encode sérialise les données en texte JSON pur
        return (bool)$this->redis->setex($key, $ttl, json_encode($value));
    }

    public function delete(string $key): bool
    {
        if (!$this->connected) return false;
        return (bool)$this->redis->del($key);
    }

    public function deletePattern(string $pattern): void
    {
        if (!$this->connected) return;
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            // Suppression en une seule commande Redis — plus efficace
            $this->redis->del($keys);
        }
    }

    public function invalidateAllPokemon(): void
    {
        $this->deletePattern('pokemons_list_*');
        $this->deletePattern('pokemon_detail_*');
        $this->deletePattern('pokemon_compare_*');
        $this->delete('weaknesses_table');
        $this->delete('custom_list');
        $this->delete('stats_globales');
    }
}
