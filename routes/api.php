<?php
// routes/api.php - Définition des routes API
// Assurez-vous que $router est l'instance créée dans api.php

// Importer les contrôleurs et middlewares nécessaires
// L'autoloader devrait s'en charger, mais pour la clarté :
// require_once __DIR__ . '/../Controllers/ApiAuthController.php';
// require_once __DIR__ . '/../Controllers/ApiEventController.php';
// require_once __DIR__ . '/../Controllers/NotificationController.php';
// require_once __DIR__ . '/../models/Photo.php';
// require_once __DIR__ . '/../models/User.php';
// require_once __DIR__ . '/../core/middleware/ApiProtectionMiddleware.php';


// --- Routes d'authentification Publiques ---
$router->apiPost('/auth/login', function() {
    $controller = new ApiAuthController();
    $controller->login();
});

$router->apiPost('/auth/refresh', function() {
    $controller = new ApiAuthController();
    $controller->refresh();
});

// --- Routes Protégées ---

// Logout (nécessite un token valide pour identifier la session/token à révoquer)
$router->apiPost('/auth/logout', function($userData) { // Modifié pour accepter $userData s'il est passé par un middleware
    $controller = new ApiAuthController();
    // La méthode logout dans ApiAuthController devra être adaptée pour utiliser $userData si besoin,
    // ou continuer à utiliser le refresh_token du body. Pour l'instant, on assume qu'elle le prend du body.
    $controller->logout();
}, ApiProtectionMiddleware::protect()); // Protégé pour s'assurer qu'un utilisateur authentifié fait la demande


// Événements
$router->apiGet('/events', function($userData) {
    $controller = new ApiEventController();
    $controller->getEvents($userData);
}, ApiProtectionMiddleware::protect(['senior', 'famille']));

$router->apiPost('/events', function($userData) {
    $controller = new ApiEventController();
    $controller->createEvent($userData);
}, ApiProtectionMiddleware::protect(['famille']));


// Notifications
$router->apiGet('/notifications', function($userData) {
    $controller = new NotificationController();
    // getUserNotifications dans NotificationController doit être adapté pour accepter $userData
    // ou l'ID utilisateur directement. Supposons qu'il utilise $_SESSION pour l'instant
    // ou qu'on le modifie pour prendre $userData->user_id.
    // Pour l'API, il est préférable de passer explicitement l'ID.
    // On va supposer que getUserNotifications est modifié pour prendre l'ID.
    // Note: La méthode getUserNotifications dans NotificationController.php utilise $_SESSION['user_id'].
    // Pour une API stateless, il faudrait passer $userData->user_id.
    // Temporairement, pour que ça fonctionne avec le code existant de NotificationController:
    $_SESSION['user_id'] = $userData->user_id; // Simuler la session pour le contrôleur existant
    $controller->getUserNotifications();
    unset($_SESSION['user_id']); // Nettoyer la simulation
}, ApiProtectionMiddleware::protect());


// Photos
$router->apiGet('/photos', function($userData) {
    header('Content-Type: application/json');
    $photos = Photo::getByUserId($userData->user_id); // [cite: 274]
    echo json_encode(['success' => true, 'photos' => $photos]);
}, ApiProtectionMiddleware::protect());


// Profil Utilisateur
$router->apiGet('/profile', function($userData) {
    $user = User::getById($userData->user_id); // [cite: 313]
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
            // Ne pas inclure le mot de passe hashé
        ]
    ]);
}, ApiProtectionMiddleware::protect());

// Ajoutez ici d'autres routes API nécessaires...
// Exemple:
// $router->apiGet('/items/{id}', function($userData, $id) {
//     // Logique pour récupérer un item avec $id pour $userData
//     echo json_encode(['item_id' => $id, 'user' => $userData->user_id]);
// }, ApiProtectionMiddleware::protect());