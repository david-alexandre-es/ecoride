@echo off
echo Installation de l'extension MongoDB pour XAMPP...
echo.

REM Vérifier si XAMPP est installé
if not exist "C:\xampp\php\php.ini" (
    echo ERREUR: XAMPP n'est pas installé dans C:\xampp
    pause
    exit /b 1
)

echo 1. Téléchargement de l'extension MongoDB...
powershell -Command "Invoke-WebRequest -Uri 'https://pecl.php.net/get/mongodb/1.17.2/windows/8.2/ts/x64/php_mongodb-1.17.2-8.2-ts-vs16-x64.zip' -OutFile 'mongodb_extension.zip'"

if not exist "mongodb_extension.zip" (
    echo ERREUR: Impossible de télécharger l'extension
    pause
    exit /b 1
)

echo 2. Extraction de l'extension...
powershell -Command "Expand-Archive -Path 'mongodb_extension.zip' -DestinationPath '.' -Force"

echo 3. Copie de l'extension vers XAMPP...
copy "php_mongodb.dll" "C:\xampp\php\ext\" /Y

echo 4. Configuration de php.ini...
echo Ajout de l'extension MongoDB à php.ini...

REM Sauvegarder le php.ini original
copy "C:\xampp\php\php.ini" "C:\xampp\php\php.ini.backup" /Y

REM Ajouter l'extension MongoDB
echo. >> "C:\xampp\php\php.ini"
echo ; MongoDB Extension >> "C:\xampp\php\php.ini"
echo extension=mongodb >> "C:\xampp\php\php.ini"

echo 5. Nettoyage des fichiers temporaires...
del "mongodb_extension.zip" /Q
del "php_mongodb.dll" /Q

echo.
echo Installation terminée !
echo Veuillez redémarrer Apache dans XAMPP Control Panel
echo.
pause
