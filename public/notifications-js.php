<?php
/**
 * Service Worker JS Proxy
 * 
 * Ce fichier sert le service worker avec le bon type MIME
 * pour éviter les problèmes de Content-Type
 */

// Définir le type MIME pour JavaScript
header('Content-Type: application/javascript');
header('Service-Worker-Allowed: /');

// Chemin vers le fichier service worker
$swFilePath = __DIR__ . '/service-worker.js';

// Vérifier si le fichier existe
if (file_exists($swFilePath)) {
    // Lire et retourner le contenu du fichier
    echo file_get_contents($swFilePath);
} else {
    // Si le fichier n'existe pas, retourner un service worker minimal
    echo "
    // Service Worker minimal (fallback)
    console.log('Service Worker SunnyLink chargé (version fallback)');
    
    self.addEventListener('install', event => {
      console.log('Service Worker installé');
      self.skipWaiting();
    });
    
    self.addEventListener('activate', event => {
      console.log('Service Worker activé');
      return self.clients.claim();
    });
    
    self.addEventListener('fetch', event => {
      event.respondWith(fetch(event.request));
    });
    ";
}
?>