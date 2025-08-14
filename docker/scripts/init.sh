#!/bin/bash

# Script d'initialisation pour Docker
echo "🚀 Initialisation du projet Ecoride..."

# Création des répertoires nécessaires
echo "📁 Création des répertoires..."
mkdir -p /var/www/html/uploads
mkdir -p /var/www/html/cache
mkdir -p /var/www/html/logs

# Configuration des permissions
echo "🔐 Configuration des permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/uploads
chmod -R 777 /var/www/html/cache
chmod -R 777 /var/www/html/logs

# Installation des dépendances Composer si nécessaire
if [ ! -d "/var/www/html/vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-dev --optimize-autoloader
fi

# Vérification de la base de données
echo "🗄️ Vérification de la base de données..."
until mysql -h db -u ecoride_user -pecoride_password -e "SELECT 1" >/dev/null 2>&1; do
    echo "⏳ Attente de la base de données..."
    sleep 2
done

echo "✅ Initialisation terminée !"
echo "🌐 Application accessible sur : http://localhost:8080"
echo "🗄️ phpMyAdmin accessible sur : http://localhost:8081"
