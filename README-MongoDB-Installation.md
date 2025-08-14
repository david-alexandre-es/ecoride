# 🍃 Guide d'installation MongoDB pour Ecoride

## 📋 Problème rencontré

L'extension MongoDB pour PHP n'est pas installée sur votre système XAMPP. Voici les solutions pour résoudre ce problème.

## 🚀 Solutions disponibles

### **Option 1 : Installation de MongoDB Community Server (Recommandée)**

1. **Télécharger MongoDB Community Server :**
   - Allez sur : https://www.mongodb.com/try/download/community
   - Sélectionnez : Windows x64
   - Téléchargez l'installateur

2. **Installer MongoDB :**
   - Exécutez l'installateur téléchargé
   - Choisissez "Complete" installation
   - Laissez les options par défaut
   - Installez MongoDB Compass (interface graphique)

3. **Démarrer MongoDB :**
   - Ouvrez Services (services.msc)
   - Trouvez "MongoDB Server"
   - Démarrez le service

4. **Tester la connexion :**
   - Ouvrez MongoDB Compass
   - Connectez-vous à : `mongodb://localhost:27017`

### **Option 2 : MongoDB Atlas (Cloud - Gratuit)**

1. **Créer un compte MongoDB Atlas :**
   - Allez sur : https://www.mongodb.com/atlas
   - Créez un compte gratuit

2. **Créer un cluster :**
   - Choisissez "Free" tier
   - Sélectionnez un provider (AWS, Google Cloud, Azure)
   - Choisissez une région proche

3. **Configurer l'accès :**
   - Créez un utilisateur de base de données
   - Ajoutez votre IP à la liste blanche (0.0.0.0/0 pour tout autoriser)

4. **Obtenir l'URL de connexion :**
   - Cliquez sur "Connect"
   - Choisissez "Connect your application"
   - Copiez l'URL de connexion

5. **Mettre à jour la configuration :**
   ```php
   // Dans Src/Helper/Config.php
   'mongodb' => [
       'uri' => 'mongodb+srv://username:password@cluster.mongodb.net/ecoride?retryWrites=true&w=majority',
       'database' => 'ecoride'
   ]
   ```

### **Option 3 : Docker (Si disponible)**

Si vous avez Docker installé :

```bash
# Démarrer MongoDB avec Docker
docker run -d -p 27017:27017 --name mongodb mongo:6.0

# Vérifier que MongoDB fonctionne
docker ps

# Accéder au shell MongoDB
docker exec -it mongodb mongosh
```

## 🔧 Installation de l'extension PHP MongoDB

### **Méthode 1 : Installation manuelle**

1. **Télécharger l'extension :**
   - Allez sur : https://pecl.php.net/package/mongodb
   - Téléchargez la version compatible avec PHP 8.2 Windows x64

2. **Installer l'extension :**
   - Copiez `php_mongodb.dll` dans `C:\xampp\php\ext\`
   - Ajoutez `extension=mongodb` dans `C:\xampp\php\php.ini`
   - Redémarrez Apache

### **Méthode 2 : Via Composer (Alternative)**

```bash
# Installer sans vérification de l'extension
composer install --ignore-platform-req=ext-mongodb

# Créer une classe wrapper pour gérer l'absence de l'extension
```

## 🧪 Test de l'installation

Après l'installation, testez avec :

```bash
php test_mongodb_simple.php
```

## 📊 Utilisation dans Ecoride

### **Avec MongoDB local :**
```php
use Src\Entity\MongoDatabase;

$mongoDb = MongoDatabase::getInstance();
$covoiturageMongo = new CovoiturageMongo();

// Créer un covoiturage
$data = [
    'depart' => 'Paris',
    'arrivee' => 'Lyon',
    'prix' => 25.50
];

$id = $covoiturageMongo->create($data);
```

### **Avec MongoDB Atlas :**
```php
// Configuration dans Config.php
'mongodb' => [
    'uri' => 'mongodb+srv://username:password@cluster.mongodb.net/ecoride',
    'database' => 'ecoride'
]
```

## 🛠️ Dépannage

### **Erreur : "Class MongoDB\Client not found"**
- L'extension MongoDB n'est pas installée
- Vérifiez que `extension=mongodb` est dans php.ini
- Redémarrez Apache

### **Erreur : "Connection refused"**
- MongoDB n'est pas démarré
- Vérifiez le service MongoDB
- Vérifiez le port 27017

### **Erreur : "Authentication failed"**
- Vérifiez les identifiants MongoDB Atlas
- Vérifiez la liste blanche IP

## 📚 Ressources

- [Documentation MongoDB](https://docs.mongodb.com/)
- [MongoDB Atlas](https://www.mongodb.com/atlas)
- [Extension PHP MongoDB](https://pecl.php.net/package/mongodb)
- [MongoDB Compass](https://www.mongodb.com/products/compass)

## 🤝 Support

Pour toute question :
1. Vérifiez les logs d'erreur
2. Testez la connexion MongoDB
3. Consultez la documentation officielle
4. Contactez l'équipe de développement
