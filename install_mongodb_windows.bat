@echo off
echo Installation de MongoDB Community Server pour Windows...
echo.

echo 1. Téléchargement de MongoDB Community Server...
echo Téléchargement en cours depuis https://www.mongodb.com/try/download/community...

REM Ouvrir le navigateur pour télécharger MongoDB
start https://www.mongodb.com/try/download/community

echo.
echo 2. Instructions d'installation:
echo    - Téléchargez la version Windows x64
echo    - Exécutez l'installateur
echo    - Choisissez "Complete" installation
echo    - Laissez les options par défaut
echo    - Installez MongoDB Compass (interface graphique)
echo.

echo 3. Après l'installation, MongoDB sera disponible sur:
echo    - Port: 27017
echo    - URL: mongodb://localhost:27017
echo.

echo 4. Pour démarrer MongoDB manuellement:
echo    - Ouvrez Services (services.msc)
echo    - Trouvez "MongoDB Server"
echo    - Démarrez le service
echo.

echo 5. Pour tester la connexion:
echo    - Ouvrez MongoDB Compass
echo    - Connectez-vous à: mongodb://localhost:27017
echo.

echo 6. Alternative: Installation via Chocolatey
echo    Si vous avez Chocolatey installé:
echo    choco install mongodb
echo.

pause
