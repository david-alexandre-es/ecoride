<?php

namespace Src\Entity;

use Src\Helper\Config;

class Database
{
    private static ?Database $instance = null;
    private ?\PDO $pdo = null;
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private string $charset;
    private array $options;


    /**
     * Constructeur privé pour empêcher l'instanciation directe
     *
     */
    private function __construct(
        string $host = null,
        string $db = null,
        string $user = null,
        string $pass = null,
        string $charset = null
    )
    {
        // Utiliser la configuration si les paramètres ne sont pas fournis
        $this->host = $host ?? Config::get('database.host', '127.0.0.1');
        $this->db = $db ?? Config::get('database.name', 'ecoride_covoiturage');
        $this->user = $user ?? Config::get('database.user', 'root');
        $this->pass = $pass ?? Config::get('database.password', '');
        $this->charset = $charset ?? Config::get('database.charset', 'utf8mb4');

        $this->options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connect();
    }

    /**
     * Empêche le clonage de l'instance
     */
    private function __clone()
    {
    }

    /**
     * Obtient l'instance unique de la classe Database (Singleton)
     */
    public static function getInstance(
        string $host = null,
        string $db = null,
        string $user = null,
        string $pass = null,
        string $charset = null
    ): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($host, $db, $user, $pass, $charset);
        }

        return self::$instance;
    }

    /**
     * Établit la connexion à la base de données
     * private : cette méthode est interne à la classe, elle ne peut pas être appelée de l’extérieur.
     * void : elle ne retourne rien.
     * Son but : établir une connexion PDO à la base de données.
     */
    private function connect(): void
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        try {
            $this->pdo = new \PDO($dsn, $this->user, $this->pass, $this->options);
        } catch (\PDOException $e) {
            // En mode debug, afficher l'erreur, sinon log seulement
            if (Config::get('app.debug', false)) {
                throw new \PDOException("Erreur de connexion à la base de données: " . $e->getMessage());
            } else {
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                throw new \PDOException("Erreur de connexion à la base de données");
            }
        }
    }

    private function logError(string $message, string $sql, \PDOException $e, array $params = []): void
    {
        $logMessage = sprintf(
            "%s: %s\nSQL: %s\nParams: %s\nError: %s",
            $message,
            $e->getMessage(),
            $sql,
            json_encode($params),
            $e->getTraceAsString()
        );

        error_log($logMessage);
    }

    /**
     * Exécute une requête simple et retourne le résultat
     */
    public function query(string $sql): array
    {
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            $this->logError("Erreur de requête", $sql, $e);
            throw new \PDOException("Erreur de requête: " . $e->getMessage());
        }
    }

    /**
     * Exécute une requête préparée avec des paramètres
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            $this->logError("Erreur d'exécution", $sql, $e, $params);
            throw new \PDOException("Erreur d'exécution: " . $e->getMessage());
        }
    }

    /**
     * Récupère une seule ligne de résultat
     */
    public function fetchRow(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Récupère toutes les lignes de résultat
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une seule valeur (première colonne de la première ligne)
     */
    public function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Insère des données dans une table
     */
    public function insert(string $sql, array $params): int
    {

        $this->execute($sql, $params);
        return (int)$this->pdo->lastInsertId();
    }


    /**
     * Met à jour des données dans une table
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        $params = [];

        $i = 0;
        foreach ($data as $column => $value) {
            $paramName = "set_" . $i;
            $setParts[] = "{$column} = :{$paramName}";
            $params[$paramName] = $value;
            $i++;
        }

        $setClause = implode(', ', $setParts);

        // Transformer les paramètres WHERE en paramètres nommés
        $whereClause = $where;
        $i = 0;
        foreach ($whereParams as $value) {
            $paramName = "where_" . $i;
            // Remplacer le premier ? rencontré par le paramètre nommé
            $pos = strpos($whereClause, '?');
            if ($pos !== false) {
                $whereClause = substr_replace($whereClause, ":{$paramName}", $pos, 1);
            }
            $params[$paramName] = $value;
            $i++;
        }

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";

        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Supprime des données d'une table
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Obtient l'objet PDO sous-jacent
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Ferme manuellement la connexion
     */
    public function close(): void
    {
        $this->pdo = null;
        self::$instance = null;
    }


}