<?php

namespace Src\Helper;

use Src\Helper\Config;

class Session
{
    private static bool $started = false;

    /**
     * Démarre la session si elle n'est pas déjà démarrée
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configuration de la session depuis Config
        ini_set('session.name', Config::get('session.name', 'ECORIDE_SESSION'));
        ini_set('session.cookie_lifetime', Config::get('session.timeout', 3600));
        ini_set('session.cookie_httponly', Config::get('session.httponly', true));
        ini_set('session.cookie_secure', Config::get('session.secure', false));
        ini_set('session.cookie_samesite', 'Lax');

        // Régénération d'ID sécurisée
        if (session_start()) {
            self::$started = true;

            // Régénérer l'ID de session périodiquement
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    /**
     * Définit une variable de session avec TTL optionnel
     */
    public static function set(string $key, $value, int $ttl = null): void
    {
        self::start();

        if ($ttl !== null) {
            $_SESSION[$key] = [
                'value' => $value,
                'expires' => time() + $ttl
            ];
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Récupère une variable de session avec gestion du TTL
     */
    public static function get(string $key, $default = null)
    {
        self::start();

        if (!isset($_SESSION[$key])) {
            return $default;
        }

        $value = $_SESSION[$key];

        // Vérifier si c'est une valeur avec TTL
        if (is_array($value) && isset($value['expires']) && isset($value['value'])) {
            if (time() > $value['expires']) {
                self::remove($key);
                return $default;
            }
            return $value['value'];
        }

        return $value;
    }

    /**
     * Supprime une variable de session
     */
    public static function remove(string $key): void
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Vérifie si une variable de session existe et n'est pas expirée
     */
    public static function has(string $key): bool
    {
        self::start();

        if (!isset($_SESSION[$key])) {
            return false;
        }

        $value = $_SESSION[$key];

        // Vérifier si c'est une valeur avec TTL
        if (is_array($value) && isset($value['expires'])) {
            if (time() > $value['expires']) {
                self::remove($key);
                return false;
            }
        }

        return true;
    }

    /**
     * Détruit complètement la session
     */
    public static function destroy(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            // Supprime le cookie de session
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
            self::$started = false;
        }
    }

    /**
     * Nettoie les sessions expirées
     */
    public static function cleanup(): void
    {
        self::start();

        foreach ($_SESSION as $key => $value) {
            if (is_array($value) && isset($value['expires']) && time() > $value['expires']) {
                unset($_SESSION[$key]);
            }
        }
    }
}