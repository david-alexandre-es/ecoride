# 🍃 Intégration MongoDB dans Ecoride

Ce document explique l'intégration de MongoDB dans le projet Ecoride et comment l'utiliser.

## 📋 Qu'est-ce que MongoDB ?

**MongoDB** est une base de données NoSQL orientée documents qui stocke les données sous forme de documents JSON (BSON). Contrairement à MySQL qui est relationnel, MongoDB offre :

### Avantages pour Ecoride :
- **Flexibilité** : Structure de données évolutive sans schéma fixe
- **Performance** : Excellente pour les lectures/écritures fréquentes
- **Scalabilité** : Facile à faire évoluer horizontalement
- **Données complexes** : Parfait pour stocker des objets imbriqués (trajets avec détails)

### Différences avec MySQL :
| Aspect | MySQL | MongoDB |
|--------|-------|---------|
| Structure | Tables relationnelles | Collections de documents |
| Schéma | Fixe | Flexible |
| Requêtes | SQL | API native |
| Relations | Jointures | Documents imbriqués |
| Performance | Optimisé pour lectures | Optimisé pour écritures |

## 🚀 Installation et Configuration

### 1. Installation des dépendances
```bash
composer install
```

### 2. Configuration Docker
```bash
# Démarrer tous les services (MySQL + MongoDB)
docker-compose up -d

# Vérifier que MongoDB fonctionne
docker-compose logs mongodb
```

### 3. Accès aux interfaces
- **Application** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
- **MongoDB Express** : http://localhost:8082

## 📊 Structure des données MongoDB

### Collection `covoiturages`
```json
{
  "_id": "ObjectId('...')",
  "depart": "Paris",
  "arrivee": "Lyon",
  "date_depart": "2024-01-15T14:00:00Z",
  "prix": 25.50,
  "places_disponibles": 3,
  "conducteur": {
    "id": "user123",
    "nom": "Jean Dupont",
    "email": "jean.dupont@email.com"
  },
  "passagers": [
    {
      "id": "passager456",
      "nom": "Marie Martin",
      "email": "marie.martin@email.com",
      "date_reservation": "2024-01-10T10:30:00Z"
    }
  ],
  "vehicule": {
    "modele": "Renault Clio",
    "couleur": "Blanc",
    "plaque": "AB-123-CD"
  },
  "description": "Trajet régulier Paris-Lyon",
  "statut": "disponible",
  "created_at": "2024-01-10T09:00:00Z",
  "updated_at": "2024-01-10T10:30:00Z"
}
```

## 💻 Utilisation en PHP

### 1. Connexion à MongoDB
```php
use Src\Entity\MongoDatabase;

// Connexion automatique via singleton
$mongoDb = MongoDatabase::getInstance();
```

### 2. Opérations CRUD de base
```php
// Insérer un document
$document = ['nom' => 'Test', 'prix' => 25];
$id = $mongoDb->insertOne('covoiturages', $document);

// Rechercher un document
$result = $mongoDb->findOne('covoiturages', ['_id' => new ObjectId($id)]);

// Mettre à jour un document
$mongoDb->updateOne('covoiturages', 
    ['_id' => new ObjectId($id)], 
    ['$set' => ['prix' => 30]]
);

// Supprimer un document
$mongoDb->deleteOne('covoiturages', ['_id' => new ObjectId($id)]);
```

### 3. Utilisation de l'entité CovoiturageMongo
```php
use Src\Entity\CovoiturageMongo;

$covoiturageMongo = new CovoiturageMongo();

// Créer un covoiturage
$data = [
    'depart' => 'Paris',
    'arrivee' => 'Lyon',
    'date_depart' => '2024-01-15 14:00:00',
    'prix' => 25.50,
    'places_disponibles' => 3,
    'conducteur_id' => 'user123',
    'conducteur_nom' => 'Jean Dupont',
    'conducteur_email' => 'jean.dupont@email.com'
];

$covoiturageId = $covoiturageMongo->create($data);

// Rechercher des covoiturages
$disponibles = $covoiturageMongo->findAvailable();
$parisLyon = $covoiturageMongo->search(['depart' => 'Paris', 'arrivee' => 'Lyon']);
```

## 🔍 Requêtes avancées

### 1. Recherche avec filtres
```php
// Recherche par ville de départ
$filter = ['depart' => 'Paris'];
$resultats = $mongoDb->find('covoiturages', $filter);

// Recherche avec regex (recherche partielle)
$filter = ['depart' => ['$regex' => 'Par', '$options' => 'i']];
$resultats = $mongoDb->find('covoiturages', $filter);

// Recherche avec conditions multiples
$filter = [
    'prix' => ['$lte' => 30],
    'places_disponibles' => ['$gt' => 0],
    'date_depart' => ['$gte' => new DateTime()]
];
```

### 2. Agrégation MongoDB
```php
// Statistiques par ville
$pipeline = [
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

$stats = $mongoDb->aggregate('covoiturages', $pipeline);
```

### 3. Recherche dans des documents imbriqués
```php
// Rechercher par conducteur
$filter = ['conducteur.id' => 'user123'];
$covoiturages = $mongoDb->find('covoiturages', $filter);

// Rechercher par passager
$filter = ['passagers.id' => 'passager456'];
$covoiturages = $mongoDb->find('covoiturages', $filter);
```

## 🛠️ Commandes utiles

### Docker
```bash
# Démarrer MongoDB
docker-compose up mongodb -d

# Voir les logs MongoDB
docker-compose logs mongodb

# Accéder au shell MongoDB
docker-compose exec mongodb mongosh

# Sauvegarder la base MongoDB
docker-compose exec mongodb mongodump --db ecoride --out /backup

# Restaurer la base MongoDB
docker-compose exec mongodb mongorestore --db ecoride /backup/ecoride
```

### MongoDB Shell
```javascript
// Se connecter à la base
use ecoride

// Lister les collections
show collections

// Compter les documents
db.covoiturages.countDocuments()

// Rechercher des documents
db.covoiturages.find({depart: "Paris"})

// Agrégation
db.covoiturages.aggregate([
  {$group: {_id: "$depart", count: {$sum: 1}}}
])
```

## 📈 Avantages pour Ecoride

### 1. Performance
- **Indexation automatique** sur `_id`
- **Index géospatial** pour les recherches de proximité
- **Index textuels** pour la recherche de villes

### 2. Flexibilité
- **Évolution du schéma** sans migration
- **Données hétérogènes** dans la même collection
- **Documents imbriqués** pour les détails

### 3. Scalabilité
- **Sharding** pour distribuer les données
- **Réplication** pour la haute disponibilité
- **Cache intégré** pour les performances

## 🔧 Configuration avancée

### Variables d'environnement
```env
MONGODB_URI=mongodb://localhost:27017
MONGODB_DATABASE=ecoride
MONGODB_USERNAME=admin
MONGODB_PASSWORD=admin_password
```

### Configuration PHP
```php
// Dans config/app.php
'mongodb' => [
    'uri' => env('MONGODB_URI', 'mongodb://localhost:27017'),
    'database' => env('MONGODB_DATABASE', 'ecoride'),
    'options' => [
        'connectTimeoutMS' => 5000,
        'serverSelectionTimeoutMS' => 5000,
        'maxPoolSize' => 100
    ]
]
```

## 🧪 Tests et exemples

### Exécuter l'exemple
```bash
php exemple_mongodb.php
```

### Tests unitaires
```bash
# Créer des tests pour MongoDB
php vendor/bin/phpunit --filter MongoDatabaseTest
```

## 📚 Ressources supplémentaires

- [Documentation MongoDB PHP](https://docs.mongodb.com/php-library/current/)
- [MongoDB University](https://university.mongodb.com/)
- [MongoDB Atlas](https://www.mongodb.com/atlas) (Cloud)
- [MongoDB Compass](https://www.mongodb.com/products/compass) (GUI)

## 🤝 Support

Pour toute question sur l'intégration MongoDB :
1. Consultez les logs Docker
2. Vérifiez la connexion MongoDB
3. Testez avec l'exemple fourni
4. Contactez l'équipe de développement
