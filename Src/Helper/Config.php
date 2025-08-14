<?php

namespace Src\Helper;

class Config
{
    private static ?array $config = null;

    /**
     * Charge la configuration depuis un fichier ou utilise les valeurs par défaut
     */
    private static function loadConfig(): void
    {
        if (self::$config !== null) {
            return;
        }

        // Configuration par défaut
        self::$config = [
            'database' => [
                'host' => '127.0.0.1',
                'name' => 'ecoride_covoiturage',
                'user' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'port' => 3306
            ],
            'mongodb' => [
                'uri' => 'mongodb://localhost:27017',
                'database' => 'ecoride',
                'options' => [
                    'connectTimeoutMS' => 5000,
                    'serverSelectionTimeoutMS' => 5000
                ]
            ],
            'app' => [
                'name' => 'EcoRide',
                'url' => 'http://localhost:84/ecoride',
                'debug' => true,
                'timezone' => 'Europe/Paris'
            ],
            'session' => [
                'name' => 'ECORIDE_SESSION',
                'timeout' => 3600, // 1 heure
                'secure' => false,
                'httponly' => true
            ],
            'api' => [
                'osrm_url' => 'http://router.project-osrm.org/route/v1/driving/',
                'nominatim_url' => 'https://nominatim.openstreetmap.org/search',
                'timeout' => 5
            ],
            'upload' => [
                'max_size' => 5 * 1024 * 1024, // 5MB
                'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
                'path' => '/uploads/'
            ],
            'pagination' => [
                'per_page' => 10,
                'max_per_page' => 100
            ],
            'covoiturage' => [
                'max_places' => 8,
                'max_price' => 100,
                'min_price' => 0,
                'max_distance_km' => 1000
            ],
            'validation' => [
                'min_pseudo_length' => 3,
                'min_password_length' => 6,
                'max_name_length' => 50
            ],
            'cache' => [
                'ttl' => 3600,
                'enabled' => true,
                'path' => '/cache/'
            ],
            'mail' => [
                'enabled' => false,
                'smtp_host' => 'localhost',
                'smtp_port' => 587,
                'from_email' => 'noreply@ecoride.com',
                'from_name' => 'EcoRide'
            ]
        ];

        // Charger la configuration depuis un fichier si elle existe
        $configFile = __DIR__ . '/../../config/app.php';
        if (file_exists($configFile)) {
            $fileConfig = require $configFile;
            self::$config = array_merge_recursive(self::$config, $fileConfig);
        }

        // Surcharger avec les variables d'environnement
        self::loadEnvironmentVariables();
    }

    /**
     * Charge les variables d'environnement
     */
    private static function loadEnvironmentVariables(): void
    {
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }

        // Appliquer les variables d'environnement
        if (isset($_ENV['DB_HOST'])) {
            self::$config['database']['host'] = $_ENV['DB_HOST'];
        }
        if (isset($_ENV['DB_NAME'])) {
            self::$config['database']['name'] = $_ENV['DB_NAME'];
        }
        if (isset($_ENV['DB_USER'])) {
            self::$config['database']['user'] = $_ENV['DB_USER'];
        }
        if (isset($_ENV['DB_PASSWORD'])) {
            self::$config['database']['password'] = $_ENV['DB_PASSWORD'];
        }
        if (isset($_ENV['APP_DEBUG'])) {
            self::$config['app']['debug'] = filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Récupère une valeur de configuration
     */
    public static function get(string $key, $default = null)
    {
        self::loadConfig();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Définit une valeur de configuration
     */
    public static function set(string $key, $value): void
    {
        self::loadConfig();

        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Récupère toute la configuration
     */
    public static function all(): array
    {
        self::loadConfig();
        return self::$config;
    }

    /**
     * Vérifie si une clé existe
     */
    public static function has(string $key): bool
    {
        self::loadConfig();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }
}