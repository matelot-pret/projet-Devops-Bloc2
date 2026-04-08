<?php

declare(strict_types=1);

namespace Pocker\Service;

use Pocker\Repository\PokemonRepositoryInterface;
use Pocker\Repository\TypeRepositoryInterface;
use Pocker\Repository\GenerationRepositoryInterface;
use Pocker\Cache\CacheInterface;
use Pocker\Database\DatabaseInterface;
use function mb_strlen;
use function mb_substr;

/**
 * Service métier pour la création et la validation des Pokémon.
 *
 * SRP : ce fichier contient la logique métier (règles de validation, calcul
 * des stats) — pas de SQL, pas d'affichage.
 *
 * La logique de création était éparpillée dans create.php. Ici elle est
 * centralisée, testable, et réutilisable.
 */
class PokemonService
{
    /**
     * Règles par génération : max types, max attaques, pool de stats.
     * Définies ici côté serveur — on ne fait JAMAIS confiance aux valeurs
     * envoyées par le JavaScript du navigateur.
     */
    private const GEN_RULES = [
        1 => ['maxTypes' => 1, 'maxAtt' => 3, 'pool' => 320],
        2 => ['maxTypes' => 1, 'maxAtt' => 3, 'pool' => 340],
        3 => ['maxTypes' => 2, 'maxAtt' => 5, 'pool' => 360],
        4 => ['maxTypes' => 2, 'maxAtt' => 6, 'pool' => 380],
        5 => ['maxTypes' => 2, 'maxAtt' => 6, 'pool' => 400],
        6 => ['maxTypes' => 2, 'maxAtt' => 9, 'pool' => 420],
        7 => ['maxTypes' => 2, 'maxAtt' => 9, 'pool' => 440],
        8 => ['maxTypes' => 2, 'maxAtt' => 9, 'pool' => 460],
        9 => ['maxTypes' => 2, 'maxAtt' => 9, 'pool' => 480],
    ];

    private const STAT_MIN = 1;
    private const STAT_MAX = 255;

    public function __construct(
        private PokemonRepositoryInterface    $pokemonRepo,
        private TypeRepositoryInterface       $typeRepo,
        private GenerationRepositoryInterface $genRepo,
        private CacheInterface                $cache,
        private DatabaseInterface             $db
    ) {}

    /**
     * Valide et crée un Pokémon personnalisé.
     *
     * Toutes les entrées utilisateur sont validées côté serveur avant
     * toute interaction avec la base de données.
     *
     * @param array $input  Données brutes du formulaire POST
     * @return array{ok: bool, error: string, id: ?int}
     */
    public function createCustom(array $input): array
    {
        // ── 1. Validation du nom ──────────────────────────────────────────
        $nom = trim($input['nom'] ?? '');
        if ($nom === '') {
            return ['ok' => false, 'error' => 'Le nom est obligatoire.', 'id' => null];
        }
        if (mb_strlen($nom) > 100) {
            return ['ok' => false, 'error' => 'Le nom est trop long (max 100 caractères).', 'id' => null];
        }
        // Autorise uniquement lettres, chiffres, tirets, espaces
        if (!preg_match('/^[\p{L}\p{N}\s\-]+$/u', $nom)) {
            return ['ok' => false, 'error' => 'Le nom contient des caractères non autorisés.', 'id' => null];
        }

        // ── 2. Validation de la génération ───────────────────────────────
        $genId = (int)($input['generation_id'] ?? 0);
        if (!$this->genRepo->isValidId($genId)) {
            return ['ok' => false, 'error' => 'Génération invalide.', 'id' => null];
        }
        $rules = self::GEN_RULES[$genId];

        // ── 3. Validation des types ───────────────────────────────────────
        $rawTypeIds = $input['type_ids'] ?? [];
        if (!is_array($rawTypeIds) || empty($rawTypeIds)) {
            return ['ok' => false, 'error' => 'Choisissez au moins un type.', 'id' => null];
        }
        // Vérifie que les IDs existent réellement en base (whitelist DB)
        $typeIds = $this->typeRepo->filterValidTypeIds($rawTypeIds);
        $typeIds = array_slice($typeIds, 0, $rules['maxTypes']);

        if (empty($typeIds)) {
            return ['ok' => false, 'error' => 'Types invalides.', 'id' => null];
        }

        // ── 4. Nombre d'attaques ──────────────────────────────────────────
        $nbAttaques = min((int)($input['nb_attaques'] ?? 1), $rules['maxAtt']);
        $nbAttaques = max(1, $nbAttaques);

        // ── 5. Stats ──────────────────────────────────────────────────────
        $autoStats = isset($input['auto_stats']);
        if ($autoStats) {
            $stats = $this->computeAutoStats($genId, $typeIds, $rules['pool']);
        } else {
            $stats = $this->validateManualStats($input, $rules['pool']);
            if (isset($stats['error'])) {
                return ['ok' => false, 'error' => $stats['error'], 'id' => null];
            }
        }

        // ── 6. Taille / poids ─────────────────────────────────────────────
        $taille = max(1, min(500,  (int)($input['taille'] ?? 10)));
        $poids  = max(1, min(10000,(int)($input['poids']  ?? 50)));

        // ── 7. Description ────────────────────────────────────────────────
        // On limite la longueur — htmlspecialchars sera fait à l'affichage
        $description = mb_substr(trim($input['description'] ?? ''), 0, 500);

        // ── 8. CSRF ───────────────────────────────────────────────────────
        if (!$this->validateCsrf($input['csrf_token'] ?? '')) {
            return ['ok' => false, 'error' => 'Token de sécurité invalide. Rechargez la page.', 'id' => null];
        }

        // ── 9. Insertion en transaction ───────────────────────────────────
        // La transaction garantit que soit tout est inséré, soit rien
        try {
            $this->db->beginTransaction();

            // RETURNING id = atomique, pas de race condition
            $newId = $this->pokemonRepo->insertCustom(
                $nom, $genId,
                $stats['pv'], $stats['attaque'], $stats['defense'],
                $stats['atk_spe'], $stats['def_spe'], $stats['vitesse'],
                $taille, $poids, $description
            );

            if (!$newId) {
                $this->db->rollback();
                return ['ok' => false, 'error' => 'Erreur lors de la création.', 'id' => null];
            }

            $this->pokemonRepo->attachTypes($newId, $typeIds);
            $this->pokemonRepo->attachRandomAttaques($newId, $typeIds, $nbAttaques);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            error_log('createCustom transaction failed: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Erreur interne. Veuillez réessayer.', 'id' => null];
        }

        // ── 10. Invalidation du cache ──────────────────────────────────────
        $this->cache->invalidateAllPokemon();

        return ['ok' => true, 'error' => '', 'id' => $newId];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Méthodes privées
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcule automatiquement les stats selon la génération et les types.
     */
    private function computeAutoStats(int $genId, array $typeIds, int $pool): array
    {
        $profile = [
            'pv' => 1.2, 'attaque' => 1.0, 'defense' => 1.0,
            'atk_spe' => 1.0, 'def_spe' => 1.0, 'vitesse' => 0.8,
        ];

        $typeProfiles = [
            'fire'     => ['atk_spe' => 1.5, 'vitesse' => 1.2, 'defense' => 0.8],
            'water'    => ['pv' => 1.3, 'def_spe' => 1.3],
            'electric' => ['vitesse' => 1.5, 'atk_spe' => 1.3, 'defense' => 0.7],
            'grass'    => ['pv' => 1.2, 'def_spe' => 1.2, 'vitesse' => 0.9],
            'fighting' => ['attaque' => 1.6, 'defense' => 1.2, 'atk_spe' => 0.6],
            'psychic'  => ['atk_spe' => 1.6, 'vitesse' => 1.2, 'defense' => 0.8],
            'rock'     => ['defense' => 1.5, 'attaque' => 1.3, 'vitesse' => 0.7],
            'ghost'    => ['atk_spe' => 1.4, 'def_spe' => 1.3, 'pv' => 0.9],
            'dragon'   => ['attaque' => 1.3, 'atk_spe' => 1.3, 'pv' => 1.1],
            'dark'     => ['attaque' => 1.3, 'vitesse' => 1.2, 'def_spe' => 0.9],
            'steel'    => ['defense' => 1.6, 'def_spe' => 1.2, 'vitesse' => 0.7],
            'fairy'    => ['def_spe' => 1.4, 'pv' => 1.2, 'attaque' => 0.8],
            'ice'      => ['atk_spe' => 1.3, 'vitesse' => 1.1],
            'ground'   => ['attaque' => 1.4, 'defense' => 1.2, 'vitesse' => 0.8],
            'poison'   => ['atk_spe' => 1.2, 'def_spe' => 1.2, 'pv' => 1.1],
            'flying'   => ['vitesse' => 1.4, 'attaque' => 1.2, 'defense' => 0.9],
            'bug'      => ['attaque' => 1.1, 'vitesse' => 1.1],
            'normal'   => ['pv' => 1.3],
        ];

        // Récupère les noms de types depuis leurs IDs
        foreach ($typeIds as $tid) {
            $placeholders = '?';
            $row = $this->db->queryOne('SELECT nom FROM type WHERE id = ?', [(int)$tid]);
            if ($row && isset($typeProfiles[strtolower($row['nom'])])) {
                foreach ($typeProfiles[strtolower($row['nom'])] as $stat => $mult) {
                    $profile[$stat] = ($profile[$stat] ?? 1.0) * $mult;
                }
            }
        }

        $totalWeight = array_sum($profile);
        $stats       = [];
        $remaining   = $pool;
        $keys        = array_keys($profile);

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $stats[$key] = max(self::STAT_MIN, min(self::STAT_MAX, $remaining));
            } else {
                $val         = (int)round($pool * $profile[$key] / $totalWeight);
                $val         = max(self::STAT_MIN, min(self::STAT_MAX, $val));
                $stats[$key] = $val;
                $remaining  -= $val;
            }
        }

        return $stats;
    }

    /**
     * Valide les stats saisies manuellement.
     * Retourne ['error' => ...] si invalides, ou le tableau de stats.
     */
    private function validateManualStats(array $input, int $pool): array
    {
        $keys  = ['pv', 'attaque', 'defense', 'atk_spe', 'def_spe', 'vitesse'];
        $stats = [];

        foreach ($keys as $key) {
            $val         = max(self::STAT_MIN, min(self::STAT_MAX, (int)($input[$key] ?? 50)));
            $stats[$key] = $val;
        }

        $total = array_sum($stats);
        if ($total > $pool) {
            return ['error' => "Total des stats trop élevé. Maximum : $pool points (actuellement : $total)."];
        }

        return $stats;
    }

    /**
     * Valide le token CSRF.
     *
     * CSRF (Cross-Site Request Forgery) : un site malveillant peut soumettre
     * un formulaire à votre place. Le token CSRF empêche ça : il est généré
     * côté serveur, stocké en session, et vérifié à la soumission.
     * Un site tiers ne connaît pas ce token et ne peut pas le forger.
     */
    private function validateCsrf(string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if ($sessionToken === '' || $token === '') return false;

        // hash_equals() est résistant aux timing attacks
        // (comparaison en temps constant pour éviter les attaques par mesure de temps)
        return hash_equals($sessionToken, $token);
    }

    /**
     * Génère un token CSRF et le stocke en session.
     * À appeler lors de l'affichage du formulaire.
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            // random_bytes génère des octets cryptographiquement aléatoires
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
