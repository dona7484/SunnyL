<?php
/**
 * Serveur WebSocket SunnyLink
 * 
 * Ce serveur gère la communication en temps réel pour l'application SunnyLink,
 * notamment les notifications, les messages et les appels audio/vidéo.
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Notification.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SunnyLinkWebSocket implements MessageComponentInterface {
    protected $clients;           // Tous les clients connectés
    protected $users = [];        // Mapping des utilisateurs connectés (resourceId => userId)
    protected $connections = [];  // Mapping inverse (userId => [resourceId, resourceId, ...])
    protected $db;                // Connexion à la base de données
    protected $debug = true;      // Mode debug pour afficher les logs

    /**
     * Constructeur
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->initDatabase();
        $this->log("Serveur WebSocket SunnyLink démarré sur 0.0.0.0:8080");
    }

    /**
     * Initialise la connexion à la base de données
     */
    private function initDatabase() {
        try {
            $dbConnect = new DbConnect();
            $this->db = $dbConnect->getConnection();
            $this->log("Connexion à la base de données établie");
        } catch (Exception $e) {
            $this->log("Erreur de connexion à la base de données: " . $e->getMessage(), true);
            exit(1);
        }
    }

    /**
     * Gère une nouvelle connexion WebSocket
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->log("Nouvelle connexion! ({$conn->resourceId})");
    }

    /**
     * Gère la réception d'un message WebSocket
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!$data) {
                $this->log("Erreur: Message JSON invalide: " . json_last_error_msg(), true);
                return;
            }

            $this->log("Message reçu: " . json_encode($data));

            // Gestion de l'authentification
            if (isset($data['type']) && $data['type'] === 'auth') {
                $this->handleAuthentication($from, $data);
                return;
            }

            // Vérifier que l'utilisateur est authentifié
            if (!isset($this->users[$from->resourceId])) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Vous devez être authentifié']));
                $this->log("Erreur: Message reçu d'un client non authentifié (resourceId: {$from->resourceId})", true);
                return;
            }

            // Traitement des différents types de messages
            switch ($data['type']) {
                case 'message':
                    $this->handleTextMessage($from, $data);
                    break;
                case 'audio':
                    $this->handleAudioMessage($from, $data);
                    break;
                case 'ping':
                    $this->handlePing($from);
                    break;
                default:
                    $this->log("Type de message non géré: {$data['type']}");
                    break;
            }
        } catch (Exception $e) {
            $this->log("Erreur lors du traitement du message: " . $e->getMessage(), true);
            $from->send(json_encode(['type' => 'error', 'message' => 'Erreur interne']));
        }
    }

    /**
     * Gère la fermeture d'une connexion WebSocket
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        if (isset($this->users[$conn->resourceId])) {
            $userId = $this->users[$conn->resourceId];
            
            // Supprimer la connexion de la liste des connexions de l'utilisateur
            if (isset($this->connections[$userId])) {
                $index = array_search($conn->resourceId, $this->connections[$userId]);
                if ($index !== false) {
                    array_splice($this->connections[$userId], $index, 1);
                }
                
                // Si plus aucune connexion pour cet utilisateur, supprimer l'entrée
                if (empty($this->connections[$userId])) {
                    unset($this->connections[$userId]);
                }
            }
            
            unset($this->users[$conn->resourceId]);
            $this->log("Utilisateur $userId déconnecté (connexion {$conn->resourceId})");
        } else {
            $this->log("Connexion {$conn->resourceId} fermée (non authentifiée)");
        }
    }

    /**
     * Gère les erreurs de connexion WebSocket
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log("Erreur: {$e->getMessage()}", true);
        $conn->close();
    }

    /**
     * Gère l'authentification d'un client
     */
    private function handleAuthentication(ConnectionInterface $conn, array $data) {
        if (!isset($data['userId']) || !is_numeric($data['userId'])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'ID utilisateur invalide']));
            return;
        }

        $userId = (int)$data['userId'];
        $this->users[$conn->resourceId] = $userId;
        
        // Ajouter la connexion à la liste des connexions de l'utilisateur
        if (!isset($this->connections[$userId])) {
            $this->connections[$userId] = [];
        }
        $this->connections[$userId][] = $conn->resourceId;
        
        $this->log("Utilisateur {$userId} authentifié sur la connexion {$conn->resourceId}");
        $conn->send(json_encode([
            'type' => 'auth_success', 
            'status' => 'success', 
            'userId' => $userId
        ]));
        
        // Envoyer les notifications non lues après l'authentification
        $this->sendPendingNotifications($userId, $conn);
    }

    /**
     * Envoie les notifications non lues à un utilisateur qui vient de se connecter
     */
    private function sendPendingNotifications($userId, ConnectionInterface $conn) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($notifications) > 0) {
                $this->log("Envoi de " . count($notifications) . " notifications en attente à l'utilisateur $userId");
                
                foreach ($notifications as $notification) {
                    $conn->send(json_encode([
                        'type' => 'notification',
                        'notificationType' => $notification['type'],
                        'id' => $notification['id'],
                        'content' => $notification['content'],
                        'relatedId' => $notification['related_id'],
                        'timestamp' => $notification['created_at']
                    ]));
                    
                    // Petite pause entre chaque notification pour éviter l'engorgement
                    usleep(500000); // 500ms
                }
            }
        } catch (Exception $e) {
            $this->log("Erreur lors de l'envoi des notifications en attente: " . $e->getMessage(), true);
        }
    }

    /**
     * Gère les messages texte
     */
    private function handleTextMessage(ConnectionInterface $from, array $data) {
        $senderId = $this->users[$from->resourceId];
        
        if (!isset($data['receiverId']) || !isset($data['content'])) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Paramètres manquants (receiverId ou content)']));
            return;
        }
        
        $receiverId = $data['receiverId'];
        $content = $data['content'];
        
        // Enregistrer le message dans la base de données
        try {
            $stmt = $this->db->prepare("
                INSERT INTO messages 
                (sender_id, receiver_id, message, created_at, is_read) 
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$senderId, $receiverId, $content]);
            $messageId = $this->db->lastInsertId();
            
            $this->log("Message texte enregistré dans la base de données (ID: $messageId)");
            
            // Récupérer le nom de l'expéditeur
            $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$senderId]);
            $senderName = $stmt->fetchColumn();
            
            // Créer une notification
            $notifId = $this->createNotification(
                $receiverId,
                'message',
                "Nouveau message de " . $senderName,
                $messageId
            );
            
            // Vérifier si le destinataire est connecté
            $this->sendToUser($receiverId, [
                'type' => 'message',
                'senderId' => $senderId,
                'sender_name' => $senderName,
                'content' => $content,
                'message_id' => $messageId,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Confirmation à l'expéditeur
            $from->send(json_encode([
                'type' => 'message_sent',
                'messageId' => $messageId,
                'notifId' => $notifId,
                'receiverId' => $receiverId,
                'delivered' => isset($this->connections[$receiverId]),
                'status' => 'success'
            ]));
        } catch (Exception $e) {
            $this->log("Erreur lors de l'enregistrement du message: " . $e->getMessage(), true);
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Erreur lors de l\'enregistrement du message'
            ]));
        }
    }
    
    /**
     * Gère les messages audio
     */
    private function handleAudioMessage(ConnectionInterface $from, array $data) {
        $senderId = $this->users[$from->resourceId];
        
        if (!isset($data['receiverId']) || !isset($data['audioData'])) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Paramètres manquants (receiverId ou audioData)']));
            return;
        }
        
        $receiverId = $data['receiverId'];
        $audioData = $data['audioData'];
        
        // Enregistrer le message audio dans la base de données
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audio_messages 
                (sender_id, receiver_id, audio_data, created_at, is_read) 
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$senderId, $receiverId, $audioData]);
            $messageId = $this->db->lastInsertId();
            
            $this->log("Message audio enregistré dans la base de données (ID: $messageId)");
            
            // Récupérer le nom de l'expéditeur
            $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$senderId]);
            $senderName = $stmt->fetchColumn();
            
            // Créer une notification
            $notifId = $this->createNotification(
                $receiverId,
                'audio',
                "Nouveau message audio de " . $senderName,
                $messageId
            );
            
            // Vérifier si le destinataire est connecté
            $this->sendToUser($receiverId, [
                'type' => 'audio',
                'senderId' => $senderId,
                'sender_name' => $senderName,
                'audioData' => $audioData,
                'message_id' => $messageId,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Confirmation à l'expéditeur
            $from->send(json_encode([
                'type' => 'audio_sent',
                'messageId' => $messageId,
                'notifId' => $notifId,
                'receiverId' => $receiverId,
                'delivered' => isset($this->connections[$receiverId]),
                'status' => 'success'
            ]));
        } catch (Exception $e) {
            $this->log("Erreur lors de l'enregistrement du message audio: " . $e->getMessage(), true);
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Erreur lors de l\'enregistrement du message audio'
            ]));
        }
    }
    
    /**
     * Gère les pings pour maintenir la connexion active
     */
    private function handlePing(ConnectionInterface $from) {
        $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
    }
    
    /**
     * Crée une notification dans la base de données
     */
    private function createNotification($userId, $type, $content, $relatedId = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications 
                (user_id, type, content, related_id, created_at, is_read) 
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$userId, $type, $content, $relatedId]);
            $notifId = $this->db->lastInsertId();
            
            $this->log("Notification créée pour l'utilisateur $userId (ID: $notifId)");
            
            // Envoyer immédiatement la notification si l'utilisateur est connecté
            $this->sendNotification($userId, $notifId, $type, $content, $relatedId);
            
            return $notifId;
        } catch (Exception $e) {
            $this->log("Erreur lors de la création de la notification: " . $e->getMessage(), true);
            return false;
        }
    }
    
    /**
     * Envoie une notification à un utilisateur connecté
     */
    private function sendNotification($userId, $notifId, $type, $content, $relatedId = null) {
        // Message de notification
        $notification = [
            'type' => 'notification',
            'notificationType' => $type,
            'id' => $notifId,
            'content' => $content,
            'relatedId' => $relatedId,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Envoyer à l'utilisateur s'il est connecté
        return $this->sendToUser($userId, $notification);
    }
    
    /**
     * Envoie un message à un utilisateur spécifique
     */
    private function sendToUser($userId, $message) {
        if (!isset($this->connections[$userId]) || empty($this->connections[$userId])) {
            return false;
        }
        
        $sent = false;
        $encoded = json_encode($message);
        
        // Envoyer à toutes les connexions de cet utilisateur
        foreach ($this->connections[$userId] as $resourceId) {
            foreach ($this->clients as $client) {
                if ($client->resourceId === $resourceId) {
                    $client->send($encoded);
                    $sent = true;
                    break;
                }
            }
        }
        
        if ($sent) {
            $this->log("Message envoyé à l'utilisateur $userId");
        } else {
            $this->log("Impossible d'envoyer le message à l'utilisateur $userId (connexions incorrectes)");
        }
        
        return $sent;
    }
    
    /**
     * Envoie un message à tous les utilisateurs connectés sauf l'expéditeur
     */
    private function broadcast($message, $excludeUserId = null) {
        $encoded = json_encode($message);
        $count = 0;
        
        foreach ($this->clients as $client) {
            $resourceId = $client->resourceId;
            
            // Ne pas envoyer à l'expéditeur
            if (isset($this->users[$resourceId]) && $this->users[$resourceId] === $excludeUserId) {
                continue;
            }
            
            $client->send($encoded);
            $count++;
        }
        
        $this->log("Message diffusé à $count clients");
        return $count;
    }
    
    /**
     * Enregistre un message dans les logs
     */
    private function log($message, $isError = false) {
        if (!$this->debug && !$isError) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $type = $isError ? 'ERROR' : 'INFO';
        echo "[$timestamp] [$type] $message\n";
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