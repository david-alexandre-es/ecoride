<?php


include __DIR__ .'/../include/config.php';
$routes = require __DIR__ .'/../include/routes.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Src\Helper\Router;
use Src\Helper\Session;

$router = new Router($routes);

Session::start();

$uri = $_SERVER['REQUEST_URI'];
$router->dispatch($uri);
