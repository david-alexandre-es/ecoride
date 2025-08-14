<?php

echo "=== Test MongoDB pour Ecoride ===\n\n";

// 1. Vérifier si l'extension MongoDB est installée
echo "1. Vérification de l'extension MongoDB...\n";
if (extension_loaded('mongodb')) {
    echo "✅ Extension MongoDB installée\n";
} else {
    echo "❌ Extension MongoDB non installée\n";
    echo "   Tentative d'installation via Composer...\n";
}

// 2. Vérifier si les classes MongoDB sont disponibles
echo "\n2. Vérification des classes MongoDB...\n";
try {
    if (class_exists('MongoDB\Client')) {
        echo "✅ Classe MongoDB\\Client disponible\n";
    } else {
        echo "❌ Classe MongoDB\\Client non disponible\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

// 3. Test de connexion simple (si possible)
echo "\n3. Test de connexion MongoDB...\n";
try {
    // Essayer de créer une connexion MongoDB
    $client = new MongoDB\Client('mongodb://localhost:27017');
    echo "✅ Connexion MongoDB réussie\n";
    
    // Lister les bases de données
    $databases = $client->listDatabases();
    echo "📊 Bases de données disponibles:\n";
    foreach ($databases as $db) {
        echo "   - " . $db->getName() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "   MongoDB n'est probablement pas démarré\n";
}

// 4. Test de nos classes personnalisées
echo "\n4. Test de nos classes personnalisées...\n";
try {
    require_once 'vendor/autoload.php';
    
    if (class_exists('Src\Entity\MongoDatabase')) {
        echo "✅ Classe MongoDatabase disponible\n";
    } else {
        echo "❌ Classe MongoDatabase non disponible\n";
    }
    
    if (class_exists('Src\Entity\CovoiturageMongo')) {
        echo "✅ Classe CovoiturageMongo disponible\n";
    } else {
        echo "❌ Classe CovoiturageMongo non disponible\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

// 5. Informations système
echo "\n5. Informations système...\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Extensions chargées:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos($ext, 'mongo') !== false) {
        echo "   - " . $ext . " ✅\n";
    }
}

echo "\n=== Test terminé ===\n";
echo "\nPour démarrer MongoDB localement:\n";
echo "1. Installez MongoDB Community Server\n";
echo "2. Ou utilisez Docker: docker run -d -p 27017:27017 --name mongodb mongo:6.0\n";
echo "3. Ou utilisez MongoDB Atlas (cloud)\n";
