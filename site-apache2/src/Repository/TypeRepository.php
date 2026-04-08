<?php

declare(strict_types=1);

namespace Pocker\Repository;

use Pocker\Database\DatabaseInterface;

/**
 * Repository des Types.
 * Fournit la liste des types valides — utilisée aussi comme whitelist
 * pour valider les filtres utilisateur.
 */
class TypeRepository implements TypeRepositoryInterface
{
    /** @var array Cache mémoire des types valides (évite de requêter plusieurs fois) */
    private ?array $validTypeNames = null;

    public function __construct(private DatabaseInterface $db) {}

    /**
     * Tous les types triés par nom.
     */
    public function findAll(): array
    {
        return $this->db->query('SELECT id, nom, couleur FROM type ORDER BY nom');
    }

    /**
     * Table des faiblesses (multiplicateurs entre types).
     */
    public function findWeaknesses(): array
    {
        return $this->db->query(
            'SELECT type_attaquant_id, type_defenseur_id, multiplicateur
             FROM type_faiblesse'
        );
    }

    /**
     * Retourne la liste des noms de types valides (whitelist).
     * Utilisée pour valider les paramètres GET avant de les utiliser en SQL.
     */
    public function getValidTypeNames(): array
    {
        if ($this->validTypeNames === null) {
            $rows = $this->db->query('SELECT nom FROM type');
            $this->validTypeNames = array_column($rows, 'nom');
        }
        return $this->validTypeNames;
    }

    /**
     * Vérifie si un nom de type est valide.
     */
    public function isValidType(string $name): bool
    {
        return in_array(strtolower($name), array_map('strtolower', $this->getValidTypeNames()), true);
    }

    /**
     * Récupère les IDs de types valides depuis un tableau d'IDs non vérifiés.
     * Filtre les IDs qui n'existent pas en base.
     */
    public function filterValidTypeIds(array $rawIds): array
    {
        if (empty($rawIds)) return [];

        $intIds       = array_map('intval', $rawIds);
        $placeholders = implode(',', array_fill(0, count($intIds), '?'));

        $rows = $this->db->query(
            "SELECT id FROM type WHERE id IN ($placeholders)",
            $intIds
        );

        return array_column($rows, 'id');
    }
}
