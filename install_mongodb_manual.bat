@echo off
echo Installation manuelle de l'extension MongoDB pour XAMPP...
echo.

REM Vérifier si XAMPP est installé
if not exist "C:\xampp\php\php.ini" (
    echo ERREUR: XAMPP n'est pas installé dans C:\xampp
    pause
    exit /b 1
)

echo 1. Téléchargement de l'extension MongoDB...
powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://pecl.php.net/get/mongodb/1.17.2/windows/8.2/ts/x64/php_mongodb-1.17.2-8.2-ts-vs16-x64.zip' -OutFile 'mongodb_extension.zip'}"

if not exist "mongodb_extension.zip" (
    echo ERREUR: Impossible de télécharger l'extension
    echo Tentative avec une version alternative...
    powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://github.com/mongodb/mongo-php-driver/releases/download/1.17.2/php_mongodb-1.17.2-8.2-ts-vs16-x64.zip' -OutFile 'mongodb_extension.zip'}"
)

echo 2. Extraction de l'extension...
powershell -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; [System.IO.Compression.ZipFile]::ExtractToDirectory('mongodb_extension.zip', '.')"

echo 3. Copie de l'extension vers XAMPP...
if exist "php_mongodb.dll" (
    copy "php_mongodb.dll" "C:\xampp\php\ext\" /Y
    echo Extension copiée avec succès
) else (
    echo ERREUR: Fichier php_mongodb.dll non trouvé
    dir *.dll
    pause
    exit /b 1
)

echo 4. Configuration de php.ini...
echo Ajout de l'extension MongoDB à php.ini...

REM Sauvegarder le php.ini original
copy "C:\xampp\php\php.ini" "C:\xampp\php\php.ini.backup" /Y

REM Vérifier si l'extension est déjà configurée
findstr /C:"extension=mongodb" "C:\xampp\php\php.ini" >nul
if errorlevel 1 (
    echo. >> "C:\xampp\php\php.ini"
    echo ; MongoDB Extension >> "C:\xampp\php\php.ini"
    echo extension=mongodb >> "C:\xampp\php\php.ini"
    echo Extension MongoDB ajoutée à php.ini
) else (
    echo Extension MongoDB déjà configurée dans php.ini
)

echo 5. Nettoyage des fichiers temporaires...
del "mongodb_extension.zip" /Q 2>nul
del "php_mongodb.dll" /Q 2>nul

echo.
echo Installation terminée !
echo Veuillez redémarrer Apache dans XAMPP Control Panel
echo.
pause
