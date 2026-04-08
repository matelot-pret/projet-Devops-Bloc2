<?php

declare(strict_types=1);

namespace Pocker\Repository;

interface TypeRepositoryInterface
{
    public function findAll(): array;
    public function findWeaknesses(): array;
    public function getValidTypeNames(): array;
    public function isValidType(string $name): bool;
    public function filterValidTypeIds(array $rawIds): array;
}
