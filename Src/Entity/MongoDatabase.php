<?php

namespace Src\Entity;

use Src\Helper\Config;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;
use MongoDB\BSON\ObjectId;

class MongoDatabase
{
    private static ?MongoDatabase $instance = null;
    private ?Client $client = null;
    private ?Database $database = null;
    private string $uri;
    private string $dbName;

    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct(string $uri = null, string $dbName = null)
    {
        $this->uri = $uri ?? Config::get('mongodb.uri', 'mongodb://localhost:27017');
        $this->dbName = $dbName ?? Config::get('mongodb.database', 'ecoride');
        
        $this->connect();
    }

    /**
     * Empêche le clonage de l'instance
     */
    private function __clone()
    {
    }

    /**
     * Obtient l'instance unique de la classe MongoDatabase (Singleton)
     */
    public static function getInstance(string $uri = null, string $dbName = null): MongoDatabase
    {
        if (self::$instance === null) {
            self::$instance = new self($uri, $dbName);
        }

        return self::$instance;
    }

    /**
     * Établit la connexion à MongoDB
     */
    private function connect(): void
    {
        try {
            $this->client = new Client($this->uri);
            $this->database = $this->client->selectDatabase($this->dbName);
        } catch (\Exception $e) {
            if (Config::get('app.debug', false)) {
                throw new \Exception("Erreur de connexion à MongoDB: " . $e->getMessage());
            } else {
                error_log("Erreur de connexion à MongoDB: " . $e->getMessage());
                throw new \Exception("Erreur de connexion à MongoDB");
            }
        }
    }

    /**
     * Obtient une collection MongoDB
     */
    public function getCollection(string $collectionName): Collection
    {
        return $this->database->selectCollection($collectionName);
    }

    /**
     * Insère un document dans une collection
     */
    public function insertOne(string $collectionName, array $document): ObjectId
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->insertOne($document);
            return $result->getInsertedId();
        } catch (\Exception $e) {
            $this->logError("Erreur d'insertion", $collectionName, $e, $document);
            throw new \Exception("Erreur d'insertion: " . $e->getMessage());
        }
    }

    /**
     * Insère plusieurs documents dans une collection
     */
    public function insertMany(string $collectionName, array $documents): array
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->insertMany($documents);
            return $result->getInsertedIds();
        } catch (\Exception $e) {
            $this->logError("Erreur d'insertion multiple", $collectionName, $e, $documents);
            throw new \Exception("Erreur d'insertion multiple: " . $e->getMessage());
        }
    }

    /**
     * Trouve un document dans une collection
     */
    public function findOne(string $collectionName, array $filter = [], array $options = []): ?array
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->findOne($filter, $options);
            return $result ? (array)$result : null;
        } catch (\Exception $e) {
            $this->logError("Erreur de recherche", $collectionName, $e, $filter);
            throw new \Exception("Erreur de recherche: " . $e->getMessage());
        }
    }

    /**
     * Trouve plusieurs documents dans une collection
     */
    public function find(string $collectionName, array $filter = [], array $options = []): array
    {
        try {
            $collection = $this->getCollection($collectionName);
            $cursor = $collection->find($filter, $options);
            return iterator_to_array($cursor);
        } catch (\Exception $e) {
            $this->logError("Erreur de recherche multiple", $collectionName, $e, $filter);
            throw new \Exception("Erreur de recherche multiple: " . $e->getMessage());
        }
    }

    /**
     * Met à jour un document dans une collection
     */
    public function updateOne(string $collectionName, array $filter, array $update, array $options = []): int
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->updateOne($filter, $update, $options);
            return $result->getModifiedCount();
        } catch (\Exception $e) {
            $this->logError("Erreur de mise à jour", $collectionName, $e, ['filter' => $filter, 'update' => $update]);
            throw new \Exception("Erreur de mise à jour: " . $e->getMessage());
        }
    }

    /**
     * Met à jour plusieurs documents dans une collection
     */
    public function updateMany(string $collectionName, array $filter, array $update, array $options = []): int
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->updateMany($filter, $update, $options);
            return $result->getModifiedCount();
        } catch (\Exception $e) {
            $this->logError("Erreur de mise à jour multiple", $collectionName, $e, ['filter' => $filter, 'update' => $update]);
            throw new \Exception("Erreur de mise à jour multiple: " . $e->getMessage());
        }
    }

    /**
     * Supprime un document dans une collection
     */
    public function deleteOne(string $collectionName, array $filter): int
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->deleteOne($filter);
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            $this->logError("Erreur de suppression", $collectionName, $e, $filter);
            throw new \Exception("Erreur de suppression: " . $e->getMessage());
        }
    }

    /**
     * Supprime plusieurs documents dans une collection
     */
    public function deleteMany(string $collectionName, array $filter): int
    {
        try {
            $collection = $this->getCollection($collectionName);
            $result = $collection->deleteMany($filter);
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            $this->logError("Erreur de suppression multiple", $collectionName, $e, $filter);
            throw new \Exception("Erreur de suppression multiple: " . $e->getMessage());
        }
    }

    /**
     * Compte les documents dans une collection
     */
    public function count(string $collectionName, array $filter = []): int
    {
        try {
            $collection = $this->getCollection($collectionName);
            return $collection->countDocuments($filter);
        } catch (\Exception $e) {
            $this->logError("Erreur de comptage", $collectionName, $e, $filter);
            throw new \Exception("Erreur de comptage: " . $e->getMessage());
        }
    }

    /**
     * Agrège des documents dans une collection
     */
    public function aggregate(string $collectionName, array $pipeline): array
    {
        try {
            $collection = $this->getCollection($collectionName);
            $cursor = $collection->aggregate($pipeline);
            return iterator_to_array($cursor);
        } catch (\Exception $e) {
            $this->logError("Erreur d'agrégation", $collectionName, $e, $pipeline);
            throw new \Exception("Erreur d'agrégation: " . $e->getMessage());
        }
    }

    /**
     * Log les erreurs
     */
    private function logError(string $message, string $collectionName, \Exception $e, array $data = []): void
    {
        $logMessage = sprintf(
            "%s dans la collection %s: %s\nData: %s\nError: %s",
            $message,
            $collectionName,
            $e->getMessage(),
            json_encode($data),
            $e->getTraceAsString()
        );

        error_log($logMessage);
    }

    /**
     * Obtient le client MongoDB
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Obtient la base de données MongoDB
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Ferme manuellement la connexion
     */
    public function close(): void
    {
        $this->client = null;
        $this->database = null;
        self::$instance = null;
    }
}
