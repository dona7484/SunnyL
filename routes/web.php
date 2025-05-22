<?php

// Routes d'authentification
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'renderLogin']);
$router->get('/register', [AuthController::class, 'renderRegister']);
$router->post('/login', [AuthController::class, 'loginUser']);
$router->post('/register', [AuthController::class, 'registerUser']);
$router->get('/logout', [AuthController::class, 'logout']);
// Routes JWT
$router->get('/auth/token', [AuthController::class, 'getToken']);
$router->post('/auth/refresh', [AuthController::class, 'refreshToken']);
// Routes d'authentification API
$router->post('/api/auth/login', [ApiAuthController::class, 'login']);
$router->post('/api/auth/refresh', [ApiAuthController::class, 'refresh']);
$router->post('/api/auth/logout', [ApiAuthController::class, 'logout']);
// Dashboard et profil
$router->get('/dashboard', [HomeController::class, 'dashboard']);

// 📸 Galerie photo
$router->get('/photo/form', [PhotoController::class, 'form']);
$router->post('/photo/upload', [PhotoController::class, 'uploadPhoto']);
$router->get('/photo/gallery', [PhotoController::class, 'gallery']);
$router->get('/photo/slideshow', [PhotoController::class, 'getAllForSlideshow']);

// 💬 Messages
$router->get('/message/send', [MessageController::class, 'send']);
$router->post('/message/send', [MessageController::class, 'send']);
$router->get('/message/received', [MessageController::class, 'received']);
$router->post('/message/sendAudio', [MessageController::class, 'sendAudio']);
$router->post('/message/markAsRead', [MessageController::class, 'markAsRead']);

// 🛎️ Notifications et alertes
$router->get('/notifications/{id}', [NotificationController::class, 'getNotifications']);
$router->post('/notification/check', [NotificationController::class, 'check']);
$router->post('/notification/subscribe', [NotificationController::class, 'subscribe']);
$router->post('/alerts/check', [AlertController::class, 'check']);

// 📅 Événements
$router->get('/event/index', [EventController::class, 'index']);
$router->get('/event/create', [EventController::class, 'create']);
$router->post('/event/store', [EventController::class, 'store']);

// 👨‍👩‍👧‍👦 Relations
$router->get('/relation/create', [RelationController::class, 'create']);
$router->post('/relation/store', [RelationController::class, 'store']);

// Spotify
$router->get('/spotify/auth', [SpotifyController::class, 'auth']);
$router->get('/spotify/callback', [SpotifyController::class, 'authCallback']);
$router->get('/spotify/player', [SpotifyController::class, 'player']);
$router->post('/spotify/control', [SpotifyController::class, 'control']);


$router->get('/parametres', [ParametresController::class, 'index']);
$router->post('/parametres/updateProfile', [ParametresController::class, 'updateProfile']);
$router->post('/parametres/updateNotifications', [ParametresController::class, 'updateNotifications']);
$router->post('/parametres/updateTheme', [ParametresController::class, 'updateTheme']);
$router->post('/parametres/updatePassword', [ParametresController::class, 'updatePassword']);
$router->post('/parametres/removeParent', [ParametresController::class, 'removeParent']);
$router->post('/relation/add', [RelationController::class, 'add']);

$router->get('/support/faq', [SupportController::class, 'faq']);
$router->get('/support/contact', [SupportController::class, 'contact']);
$router->post('/support/contact', [SupportController::class, 'contact']);


$router->get('/relation/create', [RelationController::class, 'create']);
$router->post('/relation/store', [RelationController::class, 'store']);
// Importer le middleware
require_once __DIR__ . '/../core/middleware/ApiProtectionMiddleware.php';

// Routes API protégées pour les événements
$router->apiGet('/api/events', function($userData) {
    $controller = new ApiEventController();
    $controller->getEvents($userData);
}, ApiProtectionMiddleware::protect(['senior', 'famille']));

$router->apiPost('/api/events', function($userData) {
    $controller = new ApiEventController();
    $controller->createEvent($userData);
}, ApiProtectionMiddleware::protect(['famille']));

// Route API pour les notifications
$router->apiGet('/api/notifications', function($userData) {
    $controller = new NotificationController();
    $controller->getUserNotifications($userData->user_id);
}, ApiProtectionMiddleware::protect());

// Route API pour les photos
$router->apiGet('/api/photos', function($userData) {
    header('Content-Type: application/json');
    $photos = Photo::getByUserId($userData->user_id);
    echo json_encode(['success' => true, 'photos' => $photos]);
}, ApiProtectionMiddleware::protect());

// Route API pour le profil utilisateur
$router->apiGet('/api/profile', function($userData) {
    $user = User::getById($userData->user_id);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
}, ApiProtectionMiddleware::protect());