<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/NotificationController.php';
require_once __DIR__ . '/../models/AudioMessage.php';

class MessageController extends Controller {
    
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupérer les données JSON
                $data = json_decode(file_get_contents('php://input'), true);
                
                if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                    // Essayer avec $_POST si ce n'est pas du JSON valide
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $_POST['receiver_id'] ?? null;
                    $messageText = $_POST['message'] ?? null;
                } else {
                    // Utiliser les données JSON
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $data['receiver_id'] ?? null;
                    $messageText = $data['message'] ?? null;
                }
                
                if (!$receiverId || !$messageText) {
                    throw new Exception("Données de message incomplètes");
                }
                
                $result = Message::save($senderId, $receiverId, $messageText);
                
                if ($result) {
                    // Créer une notification
                    $notificationController = new NotificationController();
                    $notificationController->sendNotification(
                        $receiverId,
                        'message',
                        'Nouveau message de ' . $_SESSION['name'],
                        $senderId
                    );
                    
                    echo json_encode(['status' => 'success', 'message' => 'Message envoyé avec succès']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'envoi du message']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            // Code pour afficher le formulaire d'envoi de message
            $familyMemberId = $_SESSION['user_id'];
            $seniorModel = new SeniorModel();
            $seniors = $seniorModel->getSeniorsForFamilyMember($familyMemberId);
            
            $this->render('message/send', [
                'seniors' => $seniors
            ]);
        }
    }
    

    public function sendAudio() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupérer les données JSON
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Récupérer les données du formulaire
                $senderId = $_SESSION['user_id'];
                $receiverId = $data['receiver_id'];
                $audioData = $data['audio_data'];
                
                // Enregistrer le message audio dans la base de données
                $messageId = Message::saveAudio($senderId, $receiverId, $audioData);
                
                if ($messageId) {
                    $notificationController = new NotificationController();
                    $notificationController->sendNotification(
                        $receiverId,
                        'audio', // Assurez-vous que c'est bien 'audio' et non 'message'
                        'Nouveau message audio de ' . $_SESSION['name'],
                        $messageId
                    );
                    
                    echo json_encode(['status' => 'success', 'message' => 'Message audio envoyé avec succès']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement du message audio']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
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
        
        // Récupérer les messages texte
        $messages = Message::getReceivedMessages($_SESSION['user_id']);
        
        // Récupérer les messages audio
        $audioMessages = AudioMessage::getReceivedMessages($_SESSION['user_id']);
        
        $this->render('message/received', [
            'messages' => $messages,
            'audioMessages' => $audioMessages,
            'current_user' => $_SESSION['user_id']
        ]);
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
