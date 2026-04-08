<?php

declare(strict_types=1);

namespace Pocker\Repository;

interface GenerationRepositoryInterface
{
    public function findAll(): array;
    public function isValidId(int $id): bool;
}
