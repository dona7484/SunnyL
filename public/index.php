<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

// L'autoloader charge toutes les classes
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Lancer le routeur
require_once __DIR__ . '/../core/Router.php';
$router = new Router();
$router->routes();
