
<?php
// Démarrer la session au tout début
// session_start();

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Instanciation du routeur
$route = new Router();
$route->routes();
