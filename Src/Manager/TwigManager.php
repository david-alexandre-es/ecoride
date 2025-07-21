<?php

namespace Src\Manager;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Src\Helper\Config;

class TwigManager
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        
        // Configuration selon le mode debug
        $twigConfig = [];
        
        if (Config::get('app.debug', false)) {
            $twigConfig['debug'] = true;
            $twigConfig['auto_reload'] = true;
            // SUPPRIMÉ : strict_variables causait des erreurs
        } else {
            $twigConfig['cache'] = __DIR__ . '/../../cache/twig';
            $twigConfig['auto_reload'] = false;
        }
        
        $this->twig = new Environment($loader, $twigConfig);
        
        // Ajouter l'extension debug si nécessaire
        if (Config::get('app.debug', false)) {
            $this->twig->addExtension(new DebugExtension());
        }
        
        // Ajouter des variables globales
        $this->twig->addGlobal('app_name', Config::get('app.name', 'EcoRide'));
        $this->twig->addGlobal('app_url', Config::get('app.url', 'http://localhost:84/ecoride'));
        $this->twig->addGlobal('app_debug', Config::get('app.debug', false));
    }

    public function render(string $template, array $data = []): string
    {
        try {
            // Ajouter des variables par défaut pour éviter les erreurs
            $defaultData = [
                'tab_js' => [],
                'user_connecte' => false,
                'dossier' => 'home'
            ];
            
            $data = array_merge($defaultData, $data);
            
            return $this->twig->render($template, $data);
        } catch (\Exception $e) {
            error_log("Erreur Twig lors du rendu de $template: " . $e->getMessage());
            
            if (Config::get('app.debug', false)) {
                throw $e;
            }
            
            // En production, afficher une page d'erreur simple
            return '<h1>Erreur 500</h1><p>Une erreur est survenue lors du rendu de la page.</p>';
        }
    }
}