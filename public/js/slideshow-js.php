<?php
// Forcer le type MIME pour JavaScript
header('Content-Type: application/javascript');

// Le chemin complet vers slideshow.js
$jsPath = __DIR__ . '/slideshow.js';

if (file_exists($jsPath)) {
    echo file_get_contents($jsPath);
} else {
    echo "console.error('Fichier slideshow.js introuvable');";
}