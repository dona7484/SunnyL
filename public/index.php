<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// L'autoloader charge toutes les classes
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Lancer le routeur
require_once __DIR__ . '/../core/Router.php';
$router = new Router();
$router->routes();
