<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure l'Autoloader et démarrer le routeur
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Instanciation du routeur
$route = new Router();
$route->routes();

