<?php

namespace Src\Entity;

use MongoDB\BSON\ObjectId;

class CovoiturageMongo
{
    private MongoDatabase $mongoDb;

    public function __construct()
    {
        $this->mongoDb = MongoDatabase::getInstance();
    }

    /**
     * Crée un nouveau covoiturage
     */
    public function create(array $data): string
    {
        $document = [
            'depart' => $data['depart'],
            'arrivee' => $data['arrivee'],
            'date_depart' => new \DateTime($data['date_depart']),
            'prix' => (float)$data['prix'],
            'places_disponibles' => (int)$data['places_disponibles'],
            'conducteur' => [
                'id' => $data['conducteur_id'],
                'nom' => $data['conducteur_nom'],
                'email' => $data['conducteur_email']
            ],
            'passagers' => [],
            'description' => $data['description'] ?? '',
            'vehicule' => [
                'modele' => $data['vehicule_modele'] ?? '',
                'couleur' => $data['vehicule_couleur'] ?? '',
                'plaque' => $data['vehicule_plaque'] ?? ''
            ],
            'statut' => 'disponible',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ];

        $objectId = $this->mongoDb->insertOne('covoiturages', $document);
        return (string)$objectId;
    }

    /**
     * Trouve un covoiturage par son ID
     */
    public function findById(string $id): ?array
    {
        return $this->mongoDb->findOne('covoiturages', ['_id' => new ObjectId($id)]);
    }

    /**
     * Trouve tous les covoiturages disponibles
     */
    public function findAvailable(): array
    {
        return $this->mongoDb->find('covoiturages', [
            'statut' => 'disponible',
            'date_depart' => ['$gte' => new \DateTime()]
        ], [
            'sort' => ['date_depart' => 1]
        ]);
    }

    /**
     * Recherche des covoiturages par critères
     */
    public function search(array $criteria): array
    {
        $filter = ['statut' => 'disponible'];
        
        if (!empty($criteria['depart'])) {
            $filter['depart'] = ['$regex' => $criteria['depart'], '$options' => 'i'];
        }
        
        if (!empty($criteria['arrivee'])) {
            $filter['arrivee'] = ['$regex' => $criteria['arrivee'], '$options' => 'i'];
        }
        
        if (!empty($criteria['date_depart'])) {
            $date = new \DateTime($criteria['date_depart']);
            $filter['date_depart'] = ['$gte' => $date];
        }
        
        if (!empty($criteria['prix_max'])) {
            $filter['prix'] = ['$lte' => (float)$criteria['prix_max']];
        }

        return $this->mongoDb->find('covoiturages', $filter, [
            'sort' => ['date_depart' => 1]
        ]);
    }

    /**
     * Met à jour un covoiturage
     */
    public function update(string $id, array $data): bool
    {
        $update = ['$set' => array_merge($data, ['updated_at' => new \DateTime()])];
        $result = $this->mongoDb->updateOne('covoiturages', ['_id' => new ObjectId($id)], $update);
        return $result > 0;
    }

    /**
     * Supprime un covoiturage
     */
    public function delete(string $id): bool
    {
        $result = $this->mongoDb->deleteOne('covoiturages', ['_id' => new ObjectId($id)]);
        return $result > 0;
    }

    /**
     * Ajoute un passager à un covoiturage
     */
    public function addPassager(string $covoiturageId, array $passager): bool
    {
        $update = [
            '$push' => ['passagers' => $passager],
            '$inc' => ['places_disponibles' => -1],
            '$set' => ['updated_at' => new \DateTime()]
        ];
        
        $result = $this->mongoDb->updateOne('covoiturages', ['_id' => new ObjectId($covoiturageId)], $update);
        return $result > 0;
    }

    /**
     * Retire un passager d'un covoiturage
     */
    public function removePassager(string $covoiturageId, string $passagerId): bool
    {
        $update = [
            '$pull' => ['passagers' => ['id' => $passagerId]],
            '$inc' => ['places_disponibles' => 1],
            '$set' => ['updated_at' => new \DateTime()]
        ];
        
        $result = $this->mongoDb->updateOne('covoiturages', ['_id' => new ObjectId($covoiturageId)], $update);
        return $result > 0;
    }

    /**
     * Trouve les covoiturages d'un conducteur
     */
    public function findByConducteur(string $conducteurId): array
    {
        return $this->mongoDb->find('covoiturages', [
            'conducteur.id' => $conducteurId
        ], [
            'sort' => ['date_depart' => -1]
        ]);
    }

    /**
     * Trouve les covoiturages d'un passager
     */
    public function findByPassager(string $passagerId): array
    {
        return $this->mongoDb->find('covoiturages', [
            'passagers.id' => $passagerId
        ], [
            'sort' => ['date_depart' => -1]
        ]);
    }

    /**
     * Statistiques des covoiturages
     */
    public function getStats(): array
    {
        $pipeline = [
            [
                '$group' => [
                    '_id' => null,
                    'total_covoiturages' => ['$sum' => 1],
                    'prix_moyen' => ['$avg' => '$prix'],
                    'places_total' => ['$sum' => '$places_disponibles']
                ]
            ]
        ];

        $result = $this->mongoDb->aggregate('covoiturages', $pipeline);
        return $result[0] ?? [];
    }
}
