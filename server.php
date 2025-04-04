<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// Déclaration de la classe ChatServer uniquement si elle n'est pas déjà définie
if (!class_exists('ChatServer')) {
    class ChatServer implements \Ratchet\MessageComponentInterface {
        protected $clients;
        
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            echo "WebSocket Server démarré...\n";
        }
        
        public function onOpen(\Ratchet\ConnectionInterface $conn) {
            $this->clients->attach($conn);
            echo "Nouvelle connexion: {$conn->resourceId}\n";
        }
        
        public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
            $data = json_decode($msg, true);
            if (!$data) {
                echo "Message invalide reçu.\n";
                return;
            }
            
            // Sauvegarder le message dans la base
            // (Si vous utilisez déjà l'autoloader, vous pouvez retirer le require_once)
            require_once __DIR__ . '/models/Message.php';
            try {
                Message::save($data['sender'], $data['receiver'], $data['message'], null);
                echo "Message sauvegardé en base.\n";
            } catch (Exception $e) {
                echo "Erreur lors de la sauvegarde : " . $e->getMessage() . "\n";
            }
            
            // Diffuser le message aux clients connectés
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
            echo "Message diffusé: " . $msg . "\n";
        }
        
        
        
        public function onClose(\Ratchet\ConnectionInterface $conn) {
            $this->clients->detach($conn);
            echo "Connexion fermée: {$conn->resourceId}\n";
        }
        
        public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
            echo "Erreur: {$e->getMessage()}\n";
            $conn->close();
        }
    }
}

// Démarrage du serveur sur le port 8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();
