<?php
// Forcer le type MIME pour JavaScript
header('Content-Type: application/javascript');

// Le chemin complet vers global-notifications.js
$jsPath = __DIR__ . '/global-notifications.js';

if (file_exists($jsPath)) {
    echo file_get_contents($jsPath);
} else {
    echo "console.error('Fichier global-notifications.js introuvable');";
}