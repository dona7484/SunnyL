<?php
// /var/www/html/SunnyLink/jwt_test_direct.php

// Désactiver le routage
define('SKIP_ROUTER', true);

// Inclure uniquement les fichiers nécessaires
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/JWTManager.php';

// Test du JWT
header('Content-Type: text/plain');
echo "=== TEST JWT ===\n\n";

try {
    $userId = 1;
    $role = 'senior';
    
    echo "1. Génération des tokens...\n";
    $tokens = JWTManager::generateTokens($userId, $role);
    
    echo "Access Token: " . $tokens['access_token'] . "\n\n";
    echo "Refresh Token: " . $tokens['refresh_token'] . "\n\n";
    
    echo "2. Validation du token...\n";
    $validation = JWTManager::validateToken($tokens['access_token'], 'access');
    
    if ($validation) {
        echo "Validation réussie! Contenu: \n";
        print_r($validation);
    } else {
        echo "Échec de la validation.\n";
    }
    
    echo "\n3. Rafraîchissement du token...\n";
    $newTokens = JWTManager::refreshAccessToken($tokens['refresh_token']);
    
    if ($newTokens) {
        echo "Rafraîchissement réussi! Nouveau token: \n";
        echo $newTokens['access_token'] . "\n";
    } else {
        echo "Échec du rafraîchissement.\n";
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}