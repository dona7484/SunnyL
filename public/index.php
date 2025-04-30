<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

// Charger l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';
// Démarrer la session avant toute sortie
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log pour le débogage
if (isset($_SESSION['user_id'])) {
    error_log("Session utilisateur active - ID: " . $_SESSION['user_id'] . ", Rôle: " . ($_SESSION['role'] ?? 'non défini'));
} else {
    error_log("Aucune session utilisateur active");
}

// L'autoloader charge toutes les classes
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Lancer le routeur
require_once __DIR__ . '/../core/Router.php';
$router = new Router();
$router->routes();