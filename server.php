<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

require __DIR__ . '/config/database.php';

class SunnyLinkMessageServer implements MessageComponentInterface {
    protected $clients;
    protected $users = [];
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "Serveur de messages SunnyLink démarré!\n";
        $this->initDatabase();
    }

    private function initDatabase() {
        try {
            $dbConnect = new DbConnect();
            $this->db = $dbConnect->getConnection();
            echo "Connexion à la base de données établie\n";
        } catch (Exception $e) {
            echo "Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
            exit;
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            // Vérifier le JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON invalide: " . json_last_error_msg());
            }

            // Identification de l'utilisateur
            if (isset($data['type']) && $data['type'] === 'identify') {
                $this->users[$from->resourceId] = $data['userId'];
                echo "Utilisateur {$data['userId']} identifié sur la connexion {$from->resourceId}\n";
                return;
            }

            // Traitement des messages texte
            if (isset($data['type']) && $data['type'] === 'message') {
                $this->saveTextMessageToDB($data);
                echo "Message texte reçu de l'utilisateur {$data['sender']} pour {$data['receiver']}\n";
            }
            
            // Traitement des messages audio
            if (isset($data['type']) && $data['type'] === 'audio') {
                $this->saveAudioMessageToDB($data);
                echo "Message audio reçu de l'utilisateur {$data['sender']} pour {$data['recipient']}\n";
            }

            // Transmission du message au destinataire
            foreach ($this->clients as $client) {
                // Pour les messages texte
                if (isset($data['type']) && $data['type'] === 'message' && 
                    isset($this->users[$client->resourceId]) && 
                    $this->users[$client->resourceId] == $data['receiver']) {
                    $client->send(json_encode($data));
                    echo "Message texte transmis au destinataire {$data['receiver']}\n";
                }
                
                // Pour les messages audio
                if (isset($data['type']) && $data['type'] === 'audio' && 
                    $client !== $from && 
                    isset($data['recipient']) && 
                    isset($this->users[$client->resourceId]) && 
                    $this->users[$client->resourceId] == $data['recipient']) {
                    $client->send(json_encode([
                        'type' => 'audio',
                        'sender' => $data['sender'],
                        'sender_name' => $data['sender_name'] ?? 'Utilisateur',
                        'audioData' => $data['audioData'],
                        'timestamp' => time()
                    ]));
                    echo "Message audio transmis au destinataire {$data['recipient']}\n";
                }
            }
        } catch (Exception $e) {
            echo "Erreur lors du traitement du message: " . $e->getMessage() . "\n";
        }
    }

    private function saveTextMessageToDB($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $data['sender'] ?? 0,
                $data['receiver'] ?? 0,
                $data['content'] ?? ''
            ]);
            
            // Créer une notification pour le destinataire
            $this->createNotification(
                $data['receiver'], 
                'message', 
                'Nouveau message de ' . ($data['sender_name'] ?? 'Utilisateur'), 
                $this->db->lastInsertId()
            );
            
            echo "Message texte enregistré dans la base de données\n";
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            echo "Erreur lors de l'enregistrement du message texte: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function saveAudioMessageToDB($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO audio_messages (sender_id, receiver_id, audio_data, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $data['sender'] ?? 0,
                $data['recipient'] ?? 0,
                $data['audioData'] ?? ''
            ]);
            
            // Créer une notification pour le destinataire
            $this->createNotification(
                $data['recipient'], 
                'audio', 
                'Nouveau message audio de ' . ($data['sender_name'] ?? 'Utilisateur'), 
                $this->db->lastInsertId()
            );
            
            echo "Message audio enregistré dans la base de données\n";
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            echo "Erreur lors de l'enregistrement du message audio: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function createNotification($userId, $type, $content, $relatedId = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, content, related_id, created_at, is_read) VALUES (?, ?, ?, ?, NOW(), 0)");
            $stmt->execute([$userId, $type, $content, $relatedId]);
            echo "Notification créée pour l'utilisateur $userId\n";
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            echo "Erreur lors de la création de la notification: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if (isset($this->users[$conn->resourceId])) {
            echo "Utilisateur {$this->users[$conn->resourceId]} déconnecté\n";
            unset($this->users[$conn->resourceId]);
        }
        echo "Connexion {$conn->resourceId} fermée\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Une erreur est survenue: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Création du serveur WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SunnyLinkMessageServer()
        )
    ),
    8080
);

echo "Serveur WebSocket SunnyLink démarré sur ws://localhost:8080\n";
$server->run();
