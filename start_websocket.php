<?php
// Démarrer le serveur WebSocket en arrière-plan
echo "Démarrage du serveur WebSocket SunnyLink...\n";
exec('php websocket-server.php > websocket.log 2>&1 &'); // Notez le tiret au lieu de l'underscore
echo "Serveur WebSocket démarré. Consultez websocket.log pour les détails.\n";
