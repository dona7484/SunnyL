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
// Ajouter les routes pour l'upload et la galerie
$router->get('/photo/form', [PhotoController::class, 'form']); // Afficher le formulaire d'upload
$router->post('/photo/upload', [PhotoController::class, 'uploadPhoto']); // Traiter l'upload de la photo

// Galerie de photos
$router->get('/photo/gallery', [PhotoController::class, 'gallery']); // Afficher la galerie


// 🛎️ Notifications et alertes
$router->get('/notifications/{id}', [NotificationController::class, 'getNotifications']);
$router->post('/alerts/check', [AlertController::class, 'check']);

// Routes pour les messages
$router->get('/message/send', [MessageController::class, 'send']); // Formulaire d'envoi de message
$router->get('/message/received', [MessageController::class, 'received']); // Affichage des messages reçus

