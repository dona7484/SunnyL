<?php

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'renderLogin']);
$router->get('/register', [AuthController::class, 'renderRegister']);
$router->post('/login', [AuthController::class, 'loginUser']);
$router->post('/register', [AuthController::class, 'registerUser']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/profile', [DashboardController::class, 'profile']);

// 📸 Galerie photo (à ajouter maintenant)
$router->post('/photos/upload', [PhotoController::class, 'uploadPhoto']);
$router->get('/photos/{id}', [PhotoController::class, 'getPhotos']);

// 🛎️ Notifications et alertes
$router->get('/notifications/{id}', [NotificationController::class, 'getNotifications']);
$router->post('/alerts/check', [AlertController::class, 'check']);
