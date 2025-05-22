<?php
require_once __DIR__ . '/core/JWTManager.php';
require_once __DIR__ . '/config/database.php';
session_start();

// Configuration pour l'affichage des résultats
header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Test JWT</title>';
echo '<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} code{background:#f5f5f5;padding:5px;display:block;}</style>';
echo '</head><body>';
echo '<h1>Test du système JWT</h1>';

// Une fonction d'aide pour afficher les résultats
function displayResult($test, $result, $details = null) {
    echo '<div>';
    if ($result === true) {
        echo '<p class="success">✅ ' . $test . ' réussi</p>';
    } else {
        echo '<p class="error">❌ ' . $test . ' échoué</p>';
    }
    if ($details) {
        echo '<code>' . htmlspecialchars(print_r($details, true)) . '</code>';
    }
    echo '</div><hr>';
}

// Test 1: Générer des tokens pour un utilisateur
echo '<h2>Test 1: Génération de tokens</h2>';

try {
    // Simulation d'un utilisateur connecté
    $userId = 1; // ID d'un utilisateur existant
    $role = 'senior'; // ou 'famille'
    
    $tokens = JWTManager::generateTokens($userId, $role);
    
    displayResult(
        'Génération de tokens',
        isset($tokens['access_token']) && isset($tokens['refresh_token']),
        $tokens
    );
    
    // Analyse du token d'accès
    echo '<h3>Contenu du token d\'accès</h3>';
    $accessToken = $tokens['access_token'];
    $accessTokenParts = explode('.', $accessToken);
    $payloadBase64 = $accessTokenParts[1];
    $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadBase64));
    $payload = json_decode($payloadJson, true);
    
    echo '<code>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . '</code>';
    
} catch (Exception $e) {
    displayResult('Génération de tokens', false, 'Erreur: ' . $e->getMessage());
}

// Test 2: Valider un token
echo '<h2>Test 2: Validation de tokens</h2>';

try {
    if (isset($accessToken)) {
        $validation = JWTManager::validateToken($accessToken, 'access');
        displayResult(
            'Validation du token d\'accès',
            $validation !== false,
            $validation
        );
    } else {
        echo '<p class="error">Aucun token d\'accès disponible</p>';
    }
} catch (Exception $e) {
    displayResult('Validation du token', false, 'Erreur: ' . $e->getMessage());
}

// Test 3: Rafraîchir un token
echo '<h2>Test 3: Rafraîchissement de tokens</h2>';

try {
    if (isset($tokens['refresh_token'])) {
        $newTokens = JWTManager::refreshAccessToken($tokens['refresh_token']);
        displayResult(
            'Rafraîchissement du token',
            isset($newTokens['access_token']),
            $newTokens
        );
    } else {
        echo '<p class="error">Aucun refresh token disponible</p>';
    }
} catch (Exception $e) {
    displayResult('Rafraîchissement du token', false, 'Erreur: ' . $e->getMessage());
}

echo '</body></html>';