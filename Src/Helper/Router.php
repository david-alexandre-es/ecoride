<?php

namespace Src\Helper;

use Src\Helper\Session;

class Router
{
    private $routes = [];

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function dispatch($url)
    {
        foreach ($this->routes as $name => $route) {
            $pattern = $route['path'];


            // Remplacement des variables dynamiques (#id# -> regex group)
            foreach ($route['pattern'] as $key => $regex) {
                $pattern = str_replace("#$key#", "($regex)", $pattern);
            }

            // Construction du pattern regex final
            $pattern = "#^" . $pattern . "$#";

            // Vérifie si l'URL correspond à ce pattern
            if (preg_match($pattern, $url, $matches)) {
                $controllerName = $route['controller'];
                $method = $route['method'];
                $authRequired= $route['authRequired'] ?? false;

                if  ($authRequired && !Session::has('user')){
                    header('Location: /ecoride/login');
                    exit;
                }

                if (class_exists(class: $controllerName)) {
                    $controller = new $controllerName();

                    if (method_exists($controller, $method)) {
                        // Supprimer le premier match complet pour ne garder que les captures
                        array_shift($matches);

                        // Appel de la méthode avec les paramètres extraits
                        return call_user_func_array([$controller, $method], $matches);
                    } else {
                        echo "Méthode '$method' introuvable.";
                        return;
                    }
                } else {
                    echo "Contrôleur '$controllerName' introuvable.";
                    return;
                }
            }
        }

        // Aucun match trouvé
        echo "Page non trouvée.";
    }
}
