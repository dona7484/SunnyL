<?php

// Routes d'authentification
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'renderLogin']);
$router->get('/register', [AuthController::class, 'renderRegister']);
$router->post('/login', [AuthController::class, 'loginUser']);
$router->post('/register', [AuthController::class, 'registerUser']);
$router->get('/logout', [AuthController::class, 'logout']);

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
