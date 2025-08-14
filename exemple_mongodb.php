<?php

require_once 'vendor/autoload.php';

use Src\Entity\MongoDatabase;
use Src\Entity\CovoiturageMongo;

// Exemple d'utilisation de MongoDB dans Ecoride

try {
    // Initialisation de MongoDB
    $mongoDb = MongoDatabase::getInstance();
    $covoiturageMongo = new CovoiturageMongo();

    echo "=== Exemple d'utilisation MongoDB dans Ecoride ===\n\n";

    // 1. Créer un nouveau covoiturage
    echo "1. Création d'un nouveau covoiturage...\n";
    $nouveauCovoiturage = [
        'depart' => 'Paris',
        'arrivee' => 'Lyon',
        'date_depart' => '2024-01-15 14:00:00',
        'prix' => 25.50,
        'places_disponibles' => 3,
        'conducteur_id' => 'user123',
        'conducteur_nom' => 'Jean Dupont',
        'conducteur_email' => 'jean.dupont@email.com',
        'description' => 'Trajet régulier Paris-Lyon',
        'vehicule_modele' => 'Renault Clio',
        'vehicule_couleur' => 'Blanc',
        'vehicule_plaque' => 'AB-123-CD'
    ];

    $covoiturageId = $covoiturageMongo->create($nouveauCovoiturage);
    echo "Covoiturage créé avec l'ID: $covoiturageId\n\n";

    // 2. Rechercher le covoiturage créé
    echo "2. Recherche du covoiturage...\n";
    $covoiturage = $covoiturageMongo->findById($covoiturageId);
    if ($covoiturage) {
        echo "Covoiturage trouvé:\n";
        echo "- Départ: " . $covoiturage['depart'] . "\n";
        echo "- Arrivée: " . $covoiturage['arrivee'] . "\n";
        echo "- Prix: " . $covoiturage['prix'] . "€\n";
        echo "- Conducteur: " . $covoiturage['conducteur']['nom'] . "\n\n";
    }

    // 3. Recherche de covoiturages disponibles
    echo "3. Recherche de covoiturages disponibles...\n";
    $covoituragesDisponibles = $covoiturageMongo->findAvailable();
    echo "Nombre de covoiturages disponibles: " . count($covoituragesDisponibles) . "\n\n";

    // 4. Recherche par critères
    echo "4. Recherche par critères...\n";
    $criteria = [
        'depart' => 'Paris',
        'prix_max' => 30
    ];
    $resultats = $covoiturageMongo->search($criteria);
    echo "Résultats de recherche: " . count($resultats) . " covoiturages trouvés\n\n";

    // 5. Ajouter un passager
    echo "5. Ajout d'un passager...\n";
    $passager = [
        'id' => 'passager456',
        'nom' => 'Marie Martin',
        'email' => 'marie.martin@email.com',
        'date_reservation' => new DateTime()
    ];
    
    $success = $covoiturageMongo->addPassager($covoiturageId, $passager);
    if ($success) {
        echo "Passager ajouté avec succès!\n\n";
    }

    // 6. Statistiques
    echo "6. Statistiques des covoiturages...\n";
    $stats = $covoiturageMongo->getStats();
    if (!empty($stats)) {
        echo "Total covoiturages: " . $stats['total_covoiturages'] . "\n";
        echo "Prix moyen: " . round($stats['prix_moyen'], 2) . "€\n";
        echo "Places totales: " . $stats['places_total'] . "\n\n";
    }

    // 7. Exemple d'agrégation MongoDB
    echo "7. Exemple d'agrégation MongoDB...\n";
    $pipeline = [
        [
            '$match' => [
                'statut' => 'disponible'
            ]
        ],
        [
            '$group' => [
                '_id' => '$depart',
                'count' => ['$sum' => 1],
                'avg_price' => ['$avg' => '$prix']
            ]
        ],
        [
            '$sort' => ['count' => -1]
        ]
    ];

    $aggregation = $mongoDb->aggregate('covoiturages', $pipeline);
    echo "Statistiques par ville de départ:\n";
    foreach ($aggregation as $result) {
        echo "- " . $result['_id'] . ": " . $result['count'] . " trajets, prix moyen: " . round($result['avg_price'], 2) . "€\n";
    }

    echo "\n=== Exemple terminé avec succès! ===\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
