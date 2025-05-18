<?php
// Forcer le type MIME pour JavaScript
header('Content-Type: application/javascript');

// Le chemin complet vers main.js
$jsPath = __DIR__ . '/main.js';

if (file_exists($jsPath)) {
    echo file_get_contents($jsPath);
} else {
    echo "console.error('Fichier main.js introuvable');";
}