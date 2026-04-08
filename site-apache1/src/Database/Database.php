<?php

declare(strict_types=1);

namespace Pocker\Database;

use PDO;
use Pocker\Database\DatabaseInterface;
use PDOException;
use PDOStatement;

/**
 * Singleton de connexion PDO à PostgreSQL.
 *
 * PDO (PHP Data Objects) est l'interface standard PHP pour parler à une base
 * de données. Contrairement à pg_query_params(), PDO fonctionne avec tous les
 * SGBD (MySQL, PostgreSQL, SQLite...) et gère nativement les prepared statements.
 *
 * Un prepared statement, c'est une requête SQL envoyée au serveur UNE SEULE FOIS
 * sous forme de modèle avec des emplacements (?) pour les données. Le serveur
 * compile la requête, puis on envoie les données séparément. Il est donc
 * IMPOSSIBLE d'injecter du SQL dans les paramètres : ils sont traités comme
 * des données brutes, jamais comme du code SQL.
 */
class Database implements DatabaseInterface
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Constructeur privé : empêche le new Database() depuis l'extérieur.
     * C'est le pattern Singleton — une seule connexion pour toute la requête.
     */
    private function __construct()
    {
        // Toutes les valeurs viennent des variables d'environnement (.env)
        // jamais écrites en dur dans le code
        $host = getenv('POSTGRES_HOST') ?: 'bd';
        $db   = getenv('POSTGRES_DB')   ?: 'postgres';
        $user = getenv('POSTGRES_USER') ?: 'postgres';
        $pass = getenv('POSTGRES_PASSWORD') ?: '';
        $port = getenv('POSTGRES_PORT') ?: '5432';

        $dsn = "pgsql:host=$host;port=$port;dbname=$db";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                // Lance une exception PDOException sur toute erreur SQL
                // Sans ça, les erreurs sont silencieuses
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                // Retourne les résultats sous forme de tableaux associatifs
                // ['nom' => 'Pikachu'] plutôt que [0 => 'Pikachu', 'nom' => 'Pikachu']
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Désactive l'émulation des prepared statements
                // On veut de VRAIS prepared statements côté serveur PostgreSQL
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // On logue l'erreur technique côté serveur (jamais côté client)
            error_log('Connexion DB échouée : ' . $e->getMessage());
            // On affiche un message générique sans détail technique
            http_response_code(503);
            die('Service temporairement indisponible.');
        }
    }

    /**
     * Point d'accès unique à l'instance.
     * Crée la connexion au premier appel, la réutilise ensuite.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Exécute une requête SELECT et retourne toutes les lignes.
     *
     * Usage : $db->query("SELECT * FROM pokemon WHERE gen_id = ?", [1])
     *
     * Le ? est un placeholder — PDO le remplace par la valeur $params[0]
     * de façon sécurisée (jamais concaténé dans le SQL).
     *
     * @param string $sql    La requête SQL avec des ? comme placeholders
     * @param array  $params Les valeurs à substituer aux ?, dans l'ordre
     * @return array         Tableau de lignes (chaque ligne = tableau associatif)
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->prepare($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('DB query error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return [];
        }
    }

    /**
     * Exécute une requête SELECT et retourne une seule ligne (ou null).
     * Pratique pour les lookups par ID.
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $row  = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('DB queryOne error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return null;
        }
    }

    /**
     * Exécute une requête INSERT/UPDATE/DELETE.
     * Retourne le nombre de lignes affectées.
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->prepare($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('DB execute error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return 0;
        }
    }

    /**
     * Exécute un INSERT et retourne l'ID auto-généré (RETURNING id).
     * Atomic : une seule requête, pas de race condition possible.
     *
     * Exemple : INSERT INTO pokemon (...) VALUES (?) RETURNING id
     */
    public function insertReturningId(string $sql, array $params = []): ?int
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $row  = $stmt->fetch();
            return $row ? (int)$row['id'] : null;
        } catch (PDOException $e) {
            error_log('DB insertReturningId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Démarre une transaction.
     * Utile pour grouper plusieurs opérations : si l'une échoue, toutes
     * sont annulées (rollback). Garantit la cohérence des données.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Prépare et exécute une requête.
     * Méthode interne partagée par query(), queryOne(), execute().
     */
    private function prepare(string $sql, array $params): PDOStatement
    {
        // prepare() envoie le modèle SQL au serveur sans les données
        $stmt = $this->pdo->prepare($sql);
        // execute() envoie les données séparément — injection SQL impossible
        $stmt->execute($params);
        return $stmt;
    }
}
