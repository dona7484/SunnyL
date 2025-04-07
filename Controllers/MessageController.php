<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class MessageController extends Controller {
    
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupérer les données du formulaire
                $senderId = $_SESSION['user_id'];
                $receiverId = $_POST['receiver_id'];
                $messageText = $_POST['message']; // IMPORTANT: Utilisez 'message' au lieu de 'content'
                
                // Enregistrer le message dans la base de données
                $result = Message::save($senderId, $receiverId, $messageText);
                
                if ($result) {
                    // Envoyer une notification
                    $notifModel = new NotificationModel();
                    $notifModel->createNotification(
                        $receiverId, 
                        'message', 
                        "Nouveau message de " . ($_SESSION['name'] ?? 'un contact')
                    );
                    
                    echo json_encode(['status' => 'success', 'message' => 'Message envoyé avec succès']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement du message']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            $this->render('message/send');
        }
    }
    
    public function markAsRead() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $messageId = $data['message_id'] ?? null;

            if (!$messageId || !is_numeric($messageId)) {
                throw new Exception("ID de message invalide");
            }

            $db = (new DbConnect())->getConnection();
            
            // Récupérer l'expéditeur
            $stmt = $db->prepare("SELECT sender_id FROM messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $senderId = $stmt->fetchColumn();

            if (!$senderId) {
                throw new Exception("Message introuvable");
            }

            // Marquer comme lu
            $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$messageId]);

            // Envoyer notification de lecture
            (new NotificationModel())->createNotification(
                $senderId,
                'message_read',
                "Votre message a été lu"
            );

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } finally {
            $db = null; // Fermer la connexion
        }
    }

    public function received() {
        $this->checkAuthentication();
        
        $messages = Message::getReceivedMessages($_SESSION['user_id']);
        $this->render('message/received', [
            'messages' => $messages,
            'current_user' => $_SESSION['user_id']
        ]);
    }

    private function sendToWebSocketServer($data) {
        try {
            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($data));
        } catch (Exception $e) {
            error_log("Erreur WebSocket: " . $e->getMessage());
        }
    }

    private function checkAuthentication() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }
}
