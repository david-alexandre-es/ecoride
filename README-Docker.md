# 🐳 Configuration Docker pour Ecoride

Ce document explique comment utiliser Docker pour déployer l'application Ecoride.

## 📋 Prérequis

- Docker Desktop installé sur votre machine (https://www.docker.com/products/docker-desktop/)
- Docker Compose installé
- Au moins 4GB de RAM disponible

## 🚀 Démarrage rapide

### 1. Cloner le projet
```bash
git clone https://github.com/david-alexandre-es/ecoride.git
cd ecoride
```

### 2. Configuration de l'environnement
```bash
# Copier le fichier d'exemple
cp env.example .env

# Éditer le fichier .env selon vos besoins
# Les valeurs par défaut sont configurées pour Docker
```

### 3. Construction et démarrage des conteneurs
```bash
# Construire les images et démarrer les services
docker-compose up -d --build

# Ou pour voir les logs en temps réel
docker-compose up --build
```

### 4. Accès à l'application
- **Application principale** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
  - Utilisateur : `root`
  - Mot de passe : `root_password`

## 🛠️ Commandes utiles

### Gestion des conteneurs
```bash
# Démarrer les services
docker-compose up -d

# Arrêter les services
docker-compose down

# Redémarrer les services
docker-compose restart

# Voir les logs
docker-compose logs -f

# Voir les logs d'un service spécifique
docker-compose logs -f app
docker-compose logs -f db
```

### Accès aux conteneurs
```bash
# Accéder au conteneur PHP
docker-compose exec app bash

# Accéder à MySQL
docker-compose exec db mysql -u root -p ecoride_covoiturage

# Exécuter Composer dans le conteneur
docker-compose exec app composer install
```

### Gestion de la base de données
```bash
# Sauvegarder la base de données
docker-compose exec db mysqldump -u root -p ecoride_covoiturage > backup.sql

# Restaurer la base de données
docker-compose exec -T db mysql -u root -p ecoride_covoiturage < backup.sql
```

## 📁 Structure des fichiers Docker

```
ecoride/
├── Dockerfile                 # Configuration de l'image PHP/Apache
├── docker-compose.yml         # Orchestration des services
├── .dockerignore             # Fichiers exclus du contexte Docker
├── docker/
│   ├── apache/
│   │   └── 000-default.conf  # Configuration Apache
│   ├── php/
│   │   └── php.ini          # Configuration PHP
│   └── scripts/
│       └── init.sh          # Script d'initialisation
└── env.example              # Variables d'environnement d'exemple
```

## 🔧 Configuration

### Variables d'environnement
Les principales variables d'environnement sont définies dans le fichier `.env` :

- `DB_HOST` : Hôte de la base de données (par défaut : `db`)
- `DB_NAME` : Nom de la base de données
- `DB_USER` : Utilisateur de la base de données
- `DB_PASSWORD` : Mot de passe de la base de données
- `APP_DEBUG` : Mode debug de l'application
- `APP_URL` : URL de l'application

### Ports utilisés
- `8080` : Application principale
- `8081` : phpMyAdmin
- `3306` : MySQL (exposé localement)

## 🐛 Dépannage

### Problèmes courants

1. **Ports déjà utilisés**
   ```bash
   # Vérifier les ports utilisés
   netstat -tulpn | grep :8080
   
   # Modifier les ports dans docker-compose.yml si nécessaire
   ```

2. **Problèmes de permissions**
   ```bash
   # Corriger les permissions
   docker-compose exec app chown -R www-data:www-data /var/www/html
   ```

3. **Base de données non accessible**
   ```bash
   # Vérifier l'état du conteneur MySQL
   docker-compose ps db
   
   # Voir les logs MySQL
   docker-compose logs db
   ```

4. **Problèmes de cache**
   ```bash
   # Nettoyer le cache Docker
   docker system prune -a
   
   # Reconstruire les images
   docker-compose build --no-cache
   ```

### Logs et debugging
```bash
# Voir tous les logs
docker-compose logs

# Suivre les logs en temps réel
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs app
docker-compose logs db
```

## 🔒 Sécurité

### En production
1. Modifier les mots de passe par défaut
2. Désactiver phpMyAdmin
3. Configurer HTTPS
4. Restreindre l'accès aux ports
5. Utiliser des secrets Docker pour les mots de passe

### Exemple de configuration production
```yaml
# docker-compose.prod.yml
version: '3.8'
services:
  app:
    environment:
      - APP_DEBUG=false
      - DB_PASSWORD=${DB_PASSWORD}
    ports:
      - "80:80"
    # Supprimer phpMyAdmin
```

## 📚 Ressources supplémentaires

- [Documentation Docker](https://docs.docker.com/)
- [Documentation Docker Compose](https://docs.docker.com/compose/)
- [Documentation PHP Docker](https://hub.docker.com/_/php)

## 🤝 Support

Pour toute question ou problème, consultez les logs Docker ou contactez l'équipe de développement.
