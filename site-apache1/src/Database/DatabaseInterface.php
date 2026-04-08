<?php

declare(strict_types=1);

namespace Pocker\Database;

/**
 * Interface de la base de données.
 * Permet de substituer Database par un mock dans les tests.
 */
interface DatabaseInterface
{
    public function query(string $sql, array $params = []): array;
    public function queryOne(string $sql, array $params = []): ?array;
    public function execute(string $sql, array $params = []): int;
    public function insertReturningId(string $sql, array $params = []): ?int;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
}
