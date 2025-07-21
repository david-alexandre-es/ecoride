<?php

require_once dirname(__FILE__) . '/../src/Entity/Database.php';
require_once dirname(__FILE__) . '/../src/Helper/Config.php';

use src\Entity\Database;
use Src\Helper\Config;

try {
    // Initialiser la timezone
    date_default_timezone_set(Config::get('app.timezone', 'Europe/Paris'));

    // Initialiser la base de données avec la configuration
    $bdd = Database::getInstance();

    // Configuration des sessions
    ini_set('session.name', Config::get('session.name', 'ECORIDE_SESSION'));
    ini_set('session.cookie_lifetime', Config::get('session.timeout', 3600));
    ini_set('session.cookie_httponly', Config::get('session.httponly', true));
    ini_set('session.cookie_secure', Config::get('session.secure', false));

    // Configuration des erreurs selon le mode debug
    if (Config::get('app.debug', false)) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ERROR | E_PARSE);
    }

} catch (Exception $e) {
    if (Config::get('app.debug', false)) {
        echo "Erreur de configuration : " . $e->getMessage();
    } else {
        error_log("Erreur de configuration : " . $e->getMessage());
        echo "Erreur de configuration";
    }
    exit;
}