<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Notification.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SunnyLinkWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $users = [];
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $dbConnect = new DbConnect();
        $this->db = $dbConnect->getConnection();
        echo "Serveur WebSocket SunnyLink démarré sur ws://0.0.0.0:8080\n";
    }

    // Implémentation de la méthode onOpen requise par l'interface
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            // Log pour le débogage
            echo "Message reçu: " . print_r($data, true) . "\n";
            
            if (!$data) {
                echo "Erreur: Message JSON invalide\n";
                return;
            }

            // Authentification de l'utilisateur
            if (isset($data['type']) && $data['type'] === 'auth') {
                $this->users[$from->resourceId] = $data['userId'];
                echo "Utilisateur {$data['userId']} identifié sur la connexion {$from->resourceId}\n";
                $from->send(json_encode(['type' => 'auth', 'status' => 'success', 'userId' => $data['userId']]));
                return;
            }

            // Vérifier que l'expéditeur est authentifié avant de traiter d'autres types de messages
            if (!isset($this->users[$from->resourceId])) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Vous devez être authentifié']));
                echo "Erreur: Utilisateur non authentifié pour la connexion {$from->resourceId}\n";
                return;
            }

            // Traitement des messages texte
            if (isset($data['type']) && $data['type'] === 'message') {
                $senderId = $this->users[$from->resourceId];
                $receiverId = $data['receiverId'];
                $content = $data['content'];
                
                // Enregistrer le message dans la base de données
                $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
                $stmt->execute([$senderId, $receiverId, $content]);
                $messageId = $this->db->lastInsertId();
                
                echo "Message texte enregistré dans la base de données\n";
                
                // Récupérer le nom de l'expéditeur
                $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->execute([$senderId]);
                $senderName = $stmt->fetchColumn();
                
                // Créer une notification
                $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, content, related_id, created_at, is_read) VALUES (?, 'message', ?, ?, NOW(), 0)");
                $stmt->execute([$receiverId, "Nouveau message de " . $senderName, $messageId]);
                $notifId = $this->db->lastInsertId();
                
                echo "Notification créée pour l'utilisateur $receiverId\n";
                
                // Vérifier si le destinataire est connecté
                $receiverConnected = false;
                foreach ($this->users as $resourceId => $userId) {
                    if ($userId == $receiverId) {
                        foreach ($this->clients as $client) {
                            if ($client->resourceId == $resourceId) {
                                $client->send(json_encode([
                                    'type' => 'message',
                                    'senderId' => $senderId,
                                    'sender_name' => $senderName,
                                    'content' => $content,
                                    'message_id' => $messageId,
                                    'timestamp' => date('Y-m-d H:i:s')
                                ]));
                                $receiverConnected = true;
                                echo "Message envoyé en temps réel à l'utilisateur $receiverId\n";
                                break;
                            }
                        }
                    }
                }
                
                // Confirmer l'envoi à l'expéditeur
                $from->send(json_encode([
                    'type' => 'message_sent',
                    'messageId' => $messageId,
                    'notifId' => $notifId,
                    'receiverId' => $receiverId,
                    'delivered' => $receiverConnected,
                    'status' => 'success'
                ]));
                
                echo "Message texte envoyé avec succès de l'utilisateur {$senderId} pour {$receiverId}\n";
            }
            
            // Traitement des messages audio
            if (isset($data['type']) && $data['type'] === 'audio') {
                $senderId = $this->users[$from->resourceId];
                $receiverId = $data['receiverId'];
                $audioData = $data['audioData'];
                
                // Enregistrer le message audio dans la base de données
                $stmt = $this->db->prepare("INSERT INTO audio_messages (sender_id, receiver_id, audio_data, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
                $stmt->execute([$senderId, $receiverId, $audioData]);
                $messageId = $this->db->lastInsertId();
                
                echo "Message audio enregistré dans la base de données\n";
                
                // Récupérer le nom de l'expéditeur
                $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->execute([$senderId]);
                $senderName = $stmt->fetchColumn();
                
                // Créer une notification
                $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, content, related_id, created_at, is_read) VALUES (?, 'audio', ?, ?, NOW(), 0)");
                $stmt->execute([$receiverId, "Nouveau message audio de " . $senderName, $messageId]);
                $notifId = $this->db->lastInsertId();
                
                echo "Notification créée pour l'utilisateur $receiverId\n";
                
                // Vérifier si le destinataire est connecté
                $receiverConnected = false;
                foreach ($this->users as $resourceId => $userId) {
                    if ($userId == $receiverId) {
                        foreach ($this->clients as $client) {
                            if ($client->resourceId == $resourceId) {
                                $client->send(json_encode([
                                    'type' => 'audio',
                                    'senderId' => $senderId,
                                    'sender_name' => $senderName,
                                    'audioData' => $audioData,
                                    'message_id' => $messageId,
                                    'timestamp' => date('Y-m-d H:i:s')
                                ]));
                                $receiverConnected = true;
                                echo "Message audio envoyé en temps réel à l'utilisateur $receiverId\n";
                                break;
                            }
                        }
                    }
                }
                
                // Confirmer l'envoi à l'expéditeur
                $from->send(json_encode([
                    'type' => 'audio_sent',
                    'messageId' => $messageId,
                    'notifId' => $notifId,
                    'receiverId' => $receiverId,
                    'delivered' => $receiverConnected,
                    'status' => 'success'
                ]));
                
                echo "Message audio envoyé avec succès de l'utilisateur {$senderId} pour {$receiverId}\n";
            }
            
            // Ping/Pong pour garder la connexion active
            if (isset($data['type']) && $data['type'] === 'ping') {
                $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
            }
            
        } catch (\Exception $e) {
            echo "Erreur: {$e->getMessage()}\n";
            $from->send(json_encode(['type' => 'error', 'message' => 'Erreur lors du traitement du message']));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if (isset($this->users[$conn->resourceId])) {
            $userId = $this->users[$conn->resourceId];
            unset($this->users[$conn->resourceId]);
            echo "Utilisateur $userId déconnecté (connexion {$conn->resourceId})\n";
        } else {
            echo "Connexion {$conn->resourceId} fermée\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erreur: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Lancer le serveur WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SunnyLinkWebSocket()
        )
    ),
    8080,
    '0.0.0.0'  // Écouter sur toutes les interfaces
);

echo "Serveur WebSocket démarré sur 0.0.0.0:8080\n";
$server->run();