<?php

declare(strict_types=1);

namespace Pocker\Repository;

use Pocker\Database\DatabaseInterface;

/**
 * Repository des Générations.
 */
class GenerationRepository implements GenerationRepositoryInterface
{
    private static array $VALID_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    public function __construct(private DatabaseInterface $db) {}

    public function findAll(): array
    {
        return $this->db->query('SELECT id, nom, region FROM generation ORDER BY id');
    }

    /**
     * Vérifie qu'un ID de génération est valide.
     * On ne fait pas confiance à $_POST['generation_id'].
     */
    public function isValidId(int $id): bool
    {
        return in_array($id, self::$VALID_IDS, true);
    }
}
