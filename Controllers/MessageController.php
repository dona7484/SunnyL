<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/NotificationController.php';
require_once __DIR__ . '/../models/AudioMessage.php';
require_once __DIR__ . '/../models/User.php';

class MessageController extends Controller {
    
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Détecter si c'est une requête AJAX (JSON) ou un formulaire classique
            $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            try {
                // Récupérer les données de la requête
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    // Pour les requêtes AJAX/JSON
                    $data = json_decode(file_get_contents('php://input'), true);
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $data['receiver_id'] ?? null;
                    $messageText = $data['message'] ?? null;
                } else {
                    // Pour les formulaires classiques
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $_POST['receiver_id'] ?? null;
                    $messageText = $_POST['message'] ?? null;
                }
                
                if (!$senderId) {
                    throw new Exception("Utilisateur non authentifié");
                }
                
                if (!$receiverId || !$messageText) {
                    throw new Exception("Données de message incomplètes");
                }
                
                // Log pour le débogage
                error_log("Tentative d'envoi de message - De: $senderId, À: $receiverId, Message: $messageText");
                
                $result = Message::save($senderId, $receiverId, $messageText);
                
                if ($result) {
                    // Récupérer le nom de l'expéditeur
                    $userModel = new User();
                    $sender = $userModel->getById($senderId);
                    $senderName = $sender ? $sender['name'] : 'Utilisateur inconnu';
                    
                    // Créer une notification pour le destinataire
                    $notificationController = new NotificationController();
                    $notifId = $notificationController->sendNotification(
                        $receiverId,
                        'message',
                        'Nouveau message de ' . $senderName,
                        $result // ID du message comme relatedId
                    );
                    
                    error_log("Message envoyé avec succès - ID notification: $notifId");
                    
                    // Retourner une réponse selon le type de requête
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        // Réponse JSON pour AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Message envoyé avec succès',
                            'message_id' => $result,
                            'notification_id' => $notifId
                        ]);
                    } else {
                        // Redirection pour soumission de formulaire classique
                        $_SESSION['message_sent'] = true;
                        $_SESSION['success_message'] = 'Message envoyé avec succès';
                        header('Location: index.php?controller=message&action=sent');
                        exit;
                    }
                } else {
                    error_log("Échec de l'envoi du message");
                    
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'envoi du message']);
                    } else {
                        $_SESSION['error_message'] = 'Échec de l\'envoi du message';
                        header('Location: index.php?controller=message&action=send');
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi du message: " . $e->getMessage());
                
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['error_message'] = $e->getMessage();
                    header('Location: index.php?controller=message&action=send');
                    exit;
                }
            }
        } else {
            // Affichage du formulaire d'envoi de message
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?controller=auth&action=login");
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['role'] ?? 'famille';
            
            // Connexion à la base de données
            $db = new DbConnect();
            $pdo = $db->getConnection();
            
            if ($userRole === 'famille' || $userRole === 'familymember') {
                // Si l'utilisateur est un membre de la famille, récupérer les seniors associés
                $stmt = $pdo->prepare("SELECT u.id as user_id, u.name FROM users u 
                            JOIN relations r ON u.id = r.senior_id 
                            WHERE r.family_id = :family_id");
                $stmt->execute([':family_id' => $userId]);
                $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Seniors trouvés pour family member $userId: " . count($seniors));
            } else if ($userRole === 'senior') {
                // Si l'utilisateur est un senior, récupérer les membres de la famille associés
                $stmt = $pdo->prepare("SELECT u.id as user_id, u.name FROM users u 
                            JOIN relations r ON u.id = r.family_id 
                            WHERE r.senior_id = :senior_id");
                $stmt->execute([':senior_id' => $userId]);
                $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Family members trouvés pour senior $userId: " . count($seniors));
            } else {
                $seniors = [];
            }
            
            // Rendre la vue avec les destinataires
            $this->render('message/send', [
                'seniors' => $seniors,
                'userRole' => $userRole,
                'success_message' => $_SESSION['success_message'] ?? null,
                'error_message' => $_SESSION['error_message'] ?? null
            ]);
            
            // Nettoyer les messages de session après les avoir affichés
            unset($_SESSION['success_message']);
            unset($_SESSION['error_message']);
        }
    }
    
    public function sent() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        // Log pour le débogage
        error_log("Récupération des messages envoyés par l'utilisateur ID: " . $_SESSION['user_id']);
        
        // Récupérer les messages texte envoyés
        $messages = Message::getSentMessages($_SESSION['user_id']);
        
        // Récupérer les messages audio envoyés (si applicable)
        $audioMessages = [];
        if (class_exists('AudioMessage')) {
            $audioMessages = AudioMessage::getSentMessages($_SESSION['user_id']);
        }
        
        // Rendre la vue avec les messages
        $this->render('message/sent', [
            'messages' => $messages,
            'audioMessages' => $audioMessages,
            'current_user' => $_SESSION['user_id'],
            'success_message' => $_SESSION['success_message'] ?? null
        ]);
        
        // Nettoyer les messages de session après les avoir affichés
        unset($_SESSION['success_message']);
    }
    
    public function sendAudio() {
        // Détecter si c'est une requête AJAX (JSON)
        $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Toujours définir l'en-tête Content-Type pour les requêtes AJAX
        if ($isAjaxRequest || $this->isJsonRequest()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_SESSION['user_id'])) {
                    throw new Exception("Utilisateur non authentifié");
                }
                
                // Récupérer les données selon le type de requête
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    // Récupérer les données JSON
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    if ($data === null) {
                        throw new Exception("Données JSON invalides");
                    }
                    
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $data['receiver_id'] ?? null;
                    $audioData = $data['audio_data'] ?? null;
                } else {
                    // Formulaire multipart/form-data
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $_POST['receiver_id'] ?? null;
                    $audioData = $_POST['audio_data'] ?? null;
                }
                
                if (!$receiverId || !$audioData) {
                    throw new Exception("Données audio incomplètes");
                }
                
                // Log pour le débogage
                error_log("Tentative d'envoi de message audio - De: $senderId, À: $receiverId");
                
                // Vérifier si la classe AudioMessage existe, sinon utiliser Message
                if (class_exists('AudioMessage')) {
                    $messageId = AudioMessage::save($senderId, $receiverId, $audioData);
                } else {
                    $messageId = Message::saveAudio($senderId, $receiverId, $audioData);
                }
                
                if ($messageId) {
                    // Récupérer le nom de l'expéditeur
                    $userModel = new User();
                    $sender = $userModel->getById($senderId);
                    $senderName = $sender ? $sender['name'] : 'Utilisateur';
                    
                    $notificationController = new NotificationController();
                    $notifId = $notificationController->sendNotification(
                        $receiverId,
                        'audio',
                        'Nouveau message audio de ' . $senderName,
                        $messageId
                    );
                    
                    error_log("Message audio envoyé avec succès - ID notification: $notifId");
                    
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Message audio envoyé avec succès', 
                            'message_id' => $messageId,
                            'notification_id' => $notifId
                        ]);
                    } else {
                        $_SESSION['success_message'] = 'Message audio envoyé avec succès';
                        header('Location: index.php?controller=message&action=sent');
                        exit;
                    }
                } else {
                    error_log("Échec de l'envoi du message audio");
                    
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement du message audio']);
                    } else {
                        $_SESSION['error_message'] = 'Échec de l\'enregistrement du message audio';
                        header('Location: index.php?controller=message&action=send');
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi du message audio: " . $e->getMessage());
                
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['error_message'] = $e->getMessage();
                    header('Location: index.php?controller=message&action=send');
                    exit;
                }
            }
        } else {
            if ($isAjaxRequest || $this->isJsonRequest()) {
                echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
            } else {
                $_SESSION['error_message'] = 'Méthode non autorisée';
                header('Location: index.php?controller=message&action=send');
                exit;
            }
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
            $notifController = new NotificationController();
            $notifId = $notifController->sendNotification(
                $senderId,
                'read_confirmation',
                "Votre message a été lu",
                $messageId,
                true
            );

            echo json_encode([
                'success' => true, 
                'message' => 'Message marqué comme lu',
                'notification_id' => $notifId
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function received() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        // Log pour le débogage
        error_log("Récupération des messages pour l'utilisateur ID: " . $_SESSION['user_id']);
        
        // Récupérer les messages texte
        $messages = Message::getReceivedMessages($_SESSION['user_id']);
        
        // Récupérer les messages audio (si applicable)
        $audioMessages = [];
        if (class_exists('AudioMessage')) {
            $audioMessages = AudioMessage::getReceivedMessages($_SESSION['user_id']);
        }
        
        // Log pour le débogage
        error_log("Nombre de messages texte trouvés: " . count($messages));
        error_log("Nombre de messages audio trouvés: " . (isset($audioMessages) ? count($audioMessages) : 0));
        
        // Rendre la vue avec les messages
        $this->render('message/received', [
            'messages' => $messages,
            'audioMessages' => $audioMessages,
            'current_user' => $_SESSION['user_id']
        ]);
    }
    
    // Méthode utilitaire pour vérifier si la requête est en JSON
    private function isJsonRequest() {
        return (isset($_SERVER['CONTENT_TYPE']) && 
                (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false));
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