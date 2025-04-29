<?php
// Fichier : ws_status.php
// Utilisé pour vérifier le statut du serveur WebSocket

$host = 'localhost';
$port = 8080;

echo "<h1>État du serveur WebSocket SunnyLink</h1>";

// Vérification du processus
$output = [];
exec("ps aux | grep websocket-server.php | grep -v grep", $output);

if (count($output) > 0) {
    echo "<p style='color:green;'>✅ Processus WebSocket trouvé : </p>";
    echo "<pre>";
    print_r($output);
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ Aucun processus WebSocket en cours d'exécution.</p>";
}

// Vérification du port
$connection = @fsockopen($host, $port, $errno, $errstr, 1);
if (is_resource($connection)) {
    echo "<p style='color:green;'>✅ Le port $port est ouvert et à l'écoute.</p>";
    fclose($connection);
} else {
    echo "<p style='color:red;'>❌ Le port $port n'est pas accessible. Erreur : $errstr ($errno)</p>";
}

// Vérification du service systemd (si configuré)
$serviceOutput = [];
exec("systemctl is-active websocket.service", $serviceOutput);
if (isset($serviceOutput[0]) && $serviceOutput[0] === 'active') {
    echo "<p style='color:green;'>✅ Le service systemd 'websocket.service' est actif.</p>";
} else {
    echo "<p style='color:orange;'>⚠️ Le service systemd 'websocket.service' n'est pas actif ou n'est pas configuré.</p>";
}

// Afficher le contenu du fichier de log
echo "<h2>Dernières lignes du fichier de log :</h2>";
if (file_exists(__DIR__ . '/websocket.log')) {
    $logContent = file_get_contents(__DIR__ . '/websocket.log', false, null, -4096); // Lire les derniers 4ko
    echo "<pre style='background-color:#f5f5f5; padding:10px; max-height:300px; overflow:auto;'>";
    echo htmlspecialchars($logContent);
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>⚠️ Fichier de log introuvable.</p>";
}

// Actions possibles
echo "<h2>Actions :</h2>";
echo "<ul>";
echo "<li><a href='?action=restart' style='color:blue;'>Redémarrer le serveur WebSocket</a></li>";
echo "<li><a href='?action=log' style='color:blue;'>Afficher tout le fichier de log</a></li>";
echo "</ul>";

// Traitement des actions
if (isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'restart':
            // Arrêter le processus existant
            exec("pkill -f websocket-server.php");
            // Démarrer un nouveau processus
            exec("php " . __DIR__ . "/websocket-server.php > " . __DIR__ . "/websocket.log 2>&1 &");
            echo "<p style='color:green;'>Le serveur WebSocket a été redémarré. <a href=''>Rafraîchir</a> pour voir le nouvel état.</p>";
            break;
        case 'log':
            if (file_exists(__DIR__ . '/websocket.log')) {
                $logContent = file_get_contents(__DIR__ . '/websocket.log');
                echo "<h3>Contenu complet du fichier de log :</h3>";
                echo "<pre style='background-color:#f5f5f5; padding:10px; max-height:500px; overflow:auto;'>";
                echo htmlspecialchars($logContent);
                echo "</pre>";
            }
            break;
    }
}