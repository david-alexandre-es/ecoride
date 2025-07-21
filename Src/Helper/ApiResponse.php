<?php

namespace Src\Helper;

use Src\Helper\Config;

class ApiResponse
{
    /**
     * Envoie une réponse de succès
     */
    public static function success($data = null, string $message = ''): void
    {
        self::sendJson([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    /**
     * Envoie une réponse d'erreur
     */
    public static function error(string $message, $errors = null, int $code = 400): void
    {
        http_response_code($code);

        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // En mode debug, ajouter plus d'informations
        if (Config::get('app.debug', false)) {
            $response['debug'] = [
                'file' => debug_backtrace()[1]['file'] ?? 'unknown',
                'line' => debug_backtrace()[1]['line'] ?? 'unknown',
                'trace' => array_slice(debug_backtrace(), 1, 3)
            ];
        }

        self::sendJson($response);
    }

    /**
     * Envoie une réponse avec du HTML (pour les popups)
     */
    public static function html(string $html, bool $success = true, string $message = ''): void
    {
        self::sendJson([
            'success' => $success,
            'html' => $html,
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    /**
     * Envoie une réponse de redirection
     */
    public static function redirect(string $url, string $message = ''): void
    {
        // Construire l'URL complète si nécessaire
        if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
            $baseUrl = Config::get('app.url', 'http://localhost:84/ecoride');
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        self::sendJson([
            'success' => true,
            'redirect' => $url,
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    /**
     * Envoie une réponse JSON et arrête l'exécution
     */
    private static function sendJson(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        // En mode debug, formater le JSON
        if (Config::get('app.debug', false)) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($data);
        }

        exit;
    }
}