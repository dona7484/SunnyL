<?php
// Forcer le type MIME pour JavaScript
header('Content-Type: application/javascript');

// Le chemin complet vers websocket.js
$jsPath = __DIR__ . '/websocket.js';

if (file_exists($jsPath)) {
    echo file_get_contents($jsPath);
} else {
    echo "console.error('Fichier websocket.js introuvable');";
}