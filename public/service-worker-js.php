<?php
// Forcer le type MIME pour JavaScript
header('Content-Type: application/javascript');

// Le chemin complet vers service-worker.js (adapté à votre structure)
$serviceWorkerPath = dirname(__DIR__) . '/service-worker.js';

if (file_exists($serviceWorkerPath)) {
    // Le fichier existe, nous le servons
    echo file_get_contents($serviceWorkerPath);
} else {
    // Le fichier n'existe pas, on log l'erreur et on sert un SW minimal
    error_log("Service worker non trouvé à: " . $serviceWorkerPath);
    
    echo "// Service Worker minimal pour SunnyLink (fallback)
self.addEventListener('install', event => {
  self.skipWaiting();
  console.log('Service Worker installé (fallback)');
});

self.addEventListener('activate', event => {
  console.log('Service Worker activé (fallback)');
});

self.addEventListener('fetch', event => {
  event.respondWith(fetch(event.request));
});";
}