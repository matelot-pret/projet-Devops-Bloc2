<?php

declare(strict_types=1);

namespace Pocker\Repository;

use Pocker\Database\DatabaseInterface;

/**
 * Repository des Pokémon.
 *
 * Un Repository est la couche dont la SEULE responsabilité est de parler
 * à la base de données pour une entité donnée (ici : Pokémon).
 * Il ne fait pas de logique métier, pas d'affichage, pas de cache.
 * Juste du SQL → données.
 *
 * Principe SRP (Single Responsibility) : si on change de base de données
 * ou qu'on modifie une requête, on ne touche qu'à ce fichier.
 *
 * Tous les paramètres utilisateur passent par des ? (placeholders PDO).
 * Aucune concaténation de chaînes dans le SQL — injection SQL impossible.
 */
class PokemonRepository implements PokemonRepositoryInterface
{
    public function __construct(private DatabaseInterface $db) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Lecture
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Récupère un Pokémon par son ID avec sa génération.
     */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT p.*, g.nom AS gen_nom, g.region
             FROM pokemon p
             LEFT JOIN generation g ON g.id = p.generation_id
             WHERE p.id = ?',
            [$id]
        );
    }

    /**
     * Récupère la liste paginée avec filtres.
     *
     * Construction sécurisée de la clause WHERE :
     * - Les conditions sont accumulées dans $conditions (chaînes SQL fixes)
     * - Les valeurs utilisateur sont accumulées dans $params (jamais dans le SQL)
     * - PDO substitue les ? aux positions correspondantes
     *
     * @param string $search     Terme de recherche (nom)
     * @param string $filterType Nom du type à filtrer
     * @param string $filterGen  ID de génération à filtrer
     * @param int    $limit      Nombre de résultats par page
     * @param int    $offset     Décalage (page * limit)
     * @return array{list: array, total: int}
     */
    public function findPaginated(
        string $search,
        string $filterType,
        string $filterGen,
        int $limit,
        int $offset
    ): array {
        $conditions = ['p.is_custom = FALSE'];
        $params     = [];

        if ($search !== '') {
            // LOWER() pour recherche insensible à la casse
            $conditions[] = '(LOWER(p.nom) LIKE ? OR p.numero::text LIKE ?)';
            $searchParam  = '%' . strtolower($search) . '%';
            // Le même paramètre est utilisé deux fois : on l'ajoute deux fois
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($filterGen !== '' && ctype_digit($filterGen)) {
            // ctype_digit() vérifie que c'est bien un entier positif
            $conditions[] = 'p.generation_id = ?';
            $params[]     = (int)$filterGen;
        }

        if ($filterType !== '') {
            // Sous-requête corrélée : cherche un type correspondant pour ce Pokémon
            // Le nom de type vient d'une whitelist vérifiée dans TypeRepository
            $conditions[] = 'EXISTS (
                SELECT 1 FROM pokemon_type pt2
                JOIN type t2 ON t2.id = pt2.type_id
                WHERE pt2.pokemon_id = p.id AND LOWER(t2.nom) = LOWER(?)
            )';
            $params[] = $filterType;
        }

        $where = implode(' AND ', $conditions);

        // Requête de comptage (même filtre, sans LIMIT/OFFSET)
        $countRow = $this->db->queryOne(
            "SELECT COUNT(DISTINCT p.id) AS c FROM pokemon p WHERE $where",
            $params
        );
        $total = (int)($countRow['c'] ?? 0);

        // Requête paginée — LIMIT et OFFSET sont aussi des paramètres PDO
        $list = $this->db->query(
            "SELECT p.id, p.nom, p.numero, p.pv, p.attaque, p.defense,
                    p.image_url, p.generation_id,
                    array_agg(DISTINCT t.nom ORDER BY t.nom) AS types
             FROM pokemon p
             LEFT JOIN pokemon_type pt ON pt.pokemon_id = p.id
             LEFT JOIN type t ON t.id = pt.type_id
             WHERE $where
             GROUP BY p.id, p.nom, p.numero, p.pv, p.attaque,
                      p.defense, p.image_url, p.generation_id
             ORDER BY p.numero ASC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );

        return ['list' => $list, 'total' => $total];
    }

    /**
     * Récupère les types d'un Pokémon.
     */
    public function findTypes(int $pokemonId): array
    {
        return $this->db->query(
            'SELECT t.nom, t.couleur
             FROM type t
             JOIN pokemon_type pt ON pt.type_id = t.id
             WHERE pt.pokemon_id = ?',
            [$pokemonId]
        );
    }

    /**
     * Récupère les talents d'un Pokémon.
     */
    public function findTalents(int $pokemonId): array
    {
        return $this->db->query(
            'SELECT t.nom, t.description, pt.est_cache
             FROM talent t
             JOIN pokemon_talent pt ON pt.talent_id = t.id
             WHERE pt.pokemon_id = ?
             ORDER BY pt.est_cache',
            [$pokemonId]
        );
    }

    /**
     * Récupère les attaques d'un Pokémon (max 20).
     */
    public function findAttaques(int $pokemonId): array
    {
        return $this->db->query(
            'SELECT a.nom, a.puissance, a.precision, a.categorie,
                    t.nom AS type_nom, t.couleur
             FROM attaque a
             JOIN pokemon_attaque pa ON pa.attaque_id = a.id
             LEFT JOIN type t ON t.id = a.type_id
             WHERE pa.pokemon_id = ?
             ORDER BY a.nom
             LIMIT 20',
            [$pokemonId]
        );
    }

    /**
     * Récupère la chaîne d'évolution d'un Pokémon.
     */
    public function findEvolutions(int $pokemonId): array
    {
        return $this->db->query(
            "SELECT 'from' AS direction, p2.id, p2.nom, p2.image_url, e.condition
             FROM evolution e
             JOIN pokemon p2 ON p2.id = e.pokemon_cible_id
             WHERE e.pokemon_source_id = ?
             UNION ALL
             SELECT 'to' AS direction, p2.id, p2.nom, p2.image_url, e.condition
             FROM evolution e
             JOIN pokemon p2 ON p2.id = e.pokemon_source_id
             WHERE e.pokemon_cible_id = ?",
            [$pokemonId, $pokemonId]
        );
    }

    /**
     * Récupère tous les Pokémon personnalisés avec leurs types et génération.
     */
    public function findAllCustom(): array
    {
        return $this->db->query(
            'SELECT p.id, p.nom, p.pv, p.attaque, p.defense,
                    p.atk_spe, p.def_spe, p.vitesse,
                    p.generation_id, p.created_at,
                    array_agg(DISTINCT t.nom ORDER BY t.nom) AS types,
                    g.nom AS gen_nom
             FROM pokemon p
             LEFT JOIN pokemon_type pt ON pt.pokemon_id = p.id
             LEFT JOIN type t ON t.id = pt.type_id
             LEFT JOIN generation g ON g.id = p.generation_id
             WHERE p.is_custom = TRUE
             GROUP BY p.id, p.nom, p.pv, p.attaque, p.defense,
                      p.atk_spe, p.def_spe, p.vitesse,
                      p.generation_id, p.created_at, g.nom
             ORDER BY p.created_at DESC'
        );
    }

    /**
     * Statistiques globales pour la page d'accueil.
     */
    public function getGlobalStats(): array
    {
        // Une seule requête avec des sous-requêtes au lieu de 6 requêtes séparées
        return $this->db->queryOne(
            'SELECT
                (SELECT COUNT(*) FROM pokemon WHERE is_custom = FALSE) AS total_pokemon,
                (SELECT COUNT(*) FROM pokemon WHERE is_custom = TRUE)  AS total_custom,
                (SELECT COUNT(*) FROM type)                            AS total_types,
                (SELECT COUNT(*) FROM attaque)                         AS total_attaques,
                (SELECT COUNT(*) FROM talent)                          AS total_talents,
                (SELECT COUNT(*) FROM generation)                      AS total_gens'
        ) ?? [];
    }

    /**
     * 6 Pokémon aléatoires pour la vitrine de la page d'accueil.
     */
    public function findFeatured(): array
    {
        return $this->db->query(
            'SELECT p.id, p.nom, p.image_url,
                    array_agg(t.nom) AS types
             FROM pokemon p
             LEFT JOIN pokemon_type pt ON pt.pokemon_id = p.id
             LEFT JOIN type t ON t.id = pt.type_id
             WHERE p.is_custom = FALSE
               AND p.image_url IS NOT NULL
               AND p.image_url != \'\'
             GROUP BY p.id, p.nom, p.image_url
             ORDER BY RANDOM()
             LIMIT 6'
        );
    }

    /**
     * Recherche de Pokémon par nom (pour l'autocomplétion de compare.php).
     * Limitée à 10 résultats.
     */
    public function search(string $query): array
    {
        return $this->db->query(
            'SELECT id, nom, numero
             FROM pokemon
             WHERE LOWER(nom) LIKE ?
             ORDER BY numero
             LIMIT 10',
            ['%' . strtolower($query) . '%']
        );
    }

    /**
     * 100 premiers Pokémon pour le select de compare.php.
     */
    public function findFirstHundred(): array
    {
        return $this->db->query(
            'SELECT id, nom, numero FROM pokemon ORDER BY numero LIMIT 100'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Écriture
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Insère un Pokémon personnalisé et retourne son ID en une seule requête.
     *
     * RETURNING id est une fonctionnalité PostgreSQL qui retourne l'ID généré
     * directement dans la réponse de l'INSERT. C'est atomique : pas de race
     * condition possible (contrairement à un SELECT séparé après INSERT).
     *
     * Toutes les valeurs sont des paramètres ? — aucune interpolation de chaîne.
     */
    public function insertCustom(
        string $nom,
        int    $generationId,
        int    $pv,
        int    $attaque,
        int    $defense,
        int    $atkSpe,
        int    $defSpe,
        int    $vitesse,
        int    $taille,
        int    $poids,
        string $description
    ): ?int {
        return $this->db->insertReturningId(
            'INSERT INTO pokemon
                (nom, numero, pv, attaque, defense, atk_spe, def_spe,
                 vitesse, taille, poids, description, generation_id, is_custom)
             VALUES (?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
             RETURNING id',
            [
                strtolower($nom),
                $pv, $attaque, $defense, $atkSpe, $defSpe, $vitesse,
                $taille, $poids, $description, $generationId,
            ]
        );
    }

    /**
     * Associe des types à un Pokémon.
     * Utilise une transaction pour garantir que tous les types sont insérés
     * ou aucun (cohérence).
     */
    public function attachTypes(int $pokemonId, array $typeIds): void
    {
        foreach ($typeIds as $typeId) {
            $this->db->execute(
                'INSERT INTO pokemon_type (pokemon_id, type_id) VALUES (?, ?)',
                [$pokemonId, (int)$typeId]
            );
        }
    }

    /**
     * Associe des attaques aléatoires selon les types du Pokémon.
     *
     * Les IDs des attaques viennent de la base de données (ORDER BY RANDOM())
     * — jamais de l'utilisateur. Pas de risque d'injection.
     */
    public function attachRandomAttaques(int $pokemonId, array $typeIds, int $nb): void
    {
        if (empty($typeIds)) return;

        // Génère les placeholders : ?,?,? pour autant de type IDs
        $placeholders = implode(',', array_fill(0, count($typeIds), '?'));

        $moves = $this->db->query(
            "SELECT id FROM attaque
             WHERE type_id IN ($placeholders)
             ORDER BY RANDOM()
             LIMIT ?",
            array_merge(array_map('intval', $typeIds), [$nb])
        );

        foreach ($moves as $move) {
            $this->db->execute(
                'INSERT INTO pokemon_attaque (pokemon_id, attaque_id) VALUES (?, ?)',
                [$pokemonId, (int)$move['id']]
            );
        }
    }
}
