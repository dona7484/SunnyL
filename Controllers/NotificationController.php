<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Controller.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationController extends Controller {
    private $vapidPublicKey = 'BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI';
    private $vapidPrivateKey = 'L8IGRAqN9gHQDL9ewkV3_IsmtMLxSU9ZHWeyyHpUHwU';

    protected $notificationModel;

    public function __construct() {
        // Instanciation du modèle de notification
        $this->notificationModel = new NotificationModel();
    }
    
    public function index() {
        // Pour les seniors, afficher les notifications non lues
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'senior') {
            $notifications = Notification::getUnreadByUserId($_SESSION['user_id']);
            
            // Définir les variables pour la vue
            $GLOBALS['notifications'] = $notifications;
            
            // Inclure directement la vue
            include __DIR__ . '/../views/notification/index.php';
        } else {
            // Redirection pour les autres utilisateurs
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }
    
    public function subscribe() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['user_id'];
            $subscription = $data['subscription'];
            
            // Enregistrer l'abonnement dans la base de données
            $result = Notification::saveSubscription($userId, json_encode($subscription));
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Échec de l\'enregistrement de l\'abonnement']);
            }
        }
    }
    public function missed() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    
        $notifications = Notification::getUnreadByUserId($_SESSION['user_id']);
        $this->render('notification/missed', [
            'notifications' => $notifications
        ]);
    }
    // Méthode pour créer une notification
    public function create() {
        try {
            // Vérification des paramètres POST
            if (!isset($_POST['userId']) || !isset($_POST['message'])) {
                throw new Exception("Les paramètres userId et message sont requis.");
            }

            // Récupération des paramètres
            $userId = $_POST['userId'];
            $message = $_POST['message'];

            // Log pour vérifier les valeurs
            error_log("Création de notification: userId = $userId, message = $message");

            // Créer la notification via le modèle
            $notifId = Notification::create($userId, 'alert', $message);

            // Envoyer une notification push si possible
            $this->sendPush($userId, 'alert', 'Nouvelle alerte', $message, 'index.php?controller=notification&action=view');

            // Répondre en JSON
            echo json_encode(['success' => true, 'message' => 'Notification envoyée.']);
        } catch (Exception $e) {
            // Log de l'erreur
            error_log("Erreur lors de la création de la notification: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
 // Méthode pour envoyer une notification
public function sendNotification($userId, $type, $content, $relatedId = null, $isConfirmation = false) {
    try {
        // Log pour le débogage
        error_log("Tentative d'envoi de notification - Type: $type, UserId: $userId, Content: $content, RelatedId: $relatedId");
        
        // Pour les messages audio, forcer le type à 'audio'
        if ($type === 'audio' || strpos(strtolower($content), 'audio') !== false) {
            $type = 'audio';
        }
        
        // Si c'est un message, récupérer le contenu réel du message
        if ($type === 'message' && $relatedId) {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("SELECT message FROM messages WHERE id = ?");
            $stmt->execute([$relatedId]);
            $messageContent = $stmt->fetchColumn();
            
            if ($messageContent) {
                // Inclure le début du message dans la notification
                $truncatedMessage = substr($messageContent, 0, 50) . (strlen($messageContent) > 50 ? '...' : '');
                $content = $content . ': ' . $truncatedMessage;
            }
        }
        
        // De même pour les messages audio, ajouter une description
        if ($type === 'audio' && $relatedId) {
            // Pour les messages audio, on ne peut pas récupérer le texte
            // Mais on peut améliorer le contenu de la notification
            $content = $content . '. Appuyez pour écouter le message audio.';
        }
        
        $notifId = Notification::create($userId, $type, $content, $relatedId, $isConfirmation);
        
        if ($notifId) {
            error_log("Notification créée avec succès - ID: $notifId, Type: $type, UserId: $userId");
            
            // URL de redirection
            $url = 'index.php?controller=home&action=dashboard'; // URL par défaut
            if ($type === 'message' || $type === 'audio') {
                $url = 'index.php?controller=message&action=received';
            } elseif ($type === 'photo') {
                $url = 'index.php?controller=photo&action=gallery';
            } elseif ($type === 'event') {
                $url = 'index.php?controller=event&action=index';
            } elseif ($type === 'video_call') {
                // Amélioration de la redirection pour les appels vidéo
                $url = 'index.php?controller=call&action=receive&from=' . $_SESSION['user_id'] . '&room=' . $relatedId;
                
                // Envoyer une notification WebSocket en plus de la notification standard
                $this->sendWebSocketNotification($userId, 'video_call', $content, $relatedId);
            }
            
            // Envoyer une notification push
            $pushResult = $this->sendPush($userId, $type, 'Nouvelle notification', $content, $url);
            error_log("Résultat de l'envoi push: " . ($pushResult ? "Succès" : "Échec"));
            
            return $notifId;
        } else {
            error_log("Échec de la création de notification - Type: $type, UserId: $userId");
            return false;
        }
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de la notification : " . $e->getMessage());
        return false;
    }
}
    
    private function sendWebSocketNotification($userId, $type, $content, $relatedId = null) {
        try {
            // Préparer les données à envoyer
            $notification = [
                'type' => 'notification',
                'receiverId' => $userId,
                'content' => [
                    'type' => $type,
                    'message' => $content,
                    'relatedId' => $relatedId,
                    'senderId' => $_SESSION['user_id'] ?? null,
                    'senderName' => $_SESSION['name'] ?? 'Utilisateur',
                    'timestamp' => time()
                ],
                'notifType' => $type
            ];
            
            // Tentative d'envoi via cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://' . $_SERVER['SERVER_NAME'] . ':8080');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour le développement seulement
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Pour le développement seulement
            
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                error_log("Erreur cURL lors de l'envoi de notification WebSocket: " . $error);
                return false;
            }
            
            error_log("Notification WebSocket envoyée avec succès à l'utilisateur $userId");
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de notification WebSocket: " . $e->getMessage());
            return false;
        }
    }
    // Méthode pour récupérer toutes les notifications non lues de l'utilisateur
    // Dans la méthode getUserNotifications de NotificationController.php
public function getUserNotifications() {
    header('Content-Type: application/json');
    
    try {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => "L'utilisateur n'est pas connecté."]);
            exit;
        }

        $notifications = Notification::getUnreadByUserId($_SESSION['user_id']);
        echo json_encode($notifications);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Une erreur est survenue: ' . $e->getMessage()]);
    }
    exit;
}
    
    // Méthode pour récupérer la dernière notification non lue
    public function getLastUnreadNotification($userId) {
        try {
            $notifications = Notification::getUnreadByUserId($userId);

            if (empty($notifications)) {
                return null;
            }

            return $notifications[0];  // Retourner la première notification non lue
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur lors de la récupération de la notification : ' . $e->getMessage()]);
        }
    }

public function markNotificationAsRead() {
    // Définir l'en-tête Content-Type AVANT toute sortie
    header('Content-Type: application/json');
    
    try {
        // Récupérer les données JSON
        $jsonData = json_decode(file_get_contents('php://input'), true);
        
        // Chercher l'ID dans le JSON ou dans POST
        $notifId = null;
        if ($jsonData && isset($jsonData['notif_id'])) {
            $notifId = $jsonData['notif_id'];
        } elseif (isset($_POST['notif_id'])) {
            $notifId = $_POST['notif_id'];
        } elseif (isset($_GET['id'])) {
            $notifId = $_GET['id'];
        }
        
        if ($notifId) {
            // Utiliser NotificationModel pour récupérer la notification
            $notificationModel = new NotificationModel();
            $notification = $notificationModel->getNotificationById($notifId);
            $type = $notification['type'] ?? '';
            $relatedId = $notification['related_id'] ?? null;
            
            // Log de débogage - AVANT
            error_log("Marquage de notification comme lue - ID: $notifId, Type: $type");
            
            // Marquer la notification comme lue
            $result = $notificationModel->markAsRead($notifId);
            
            // Test supplémentaire en utilisant directement la classe Notification
            if (class_exists('Notification') && method_exists('Notification', 'markAsRead')) {
                $notifResult = Notification::markAsRead($notifId);
                error_log("Résultat Notification::markAsRead: " . ($notifResult ? "Succès" : "Échec"));
            }
            
            // Vérifier que la notification a bien été marquée comme lue
            $checkNotif = $notificationModel->getNotificationById($notifId);
            if ($checkNotif && isset($checkNotif['is_read'])) {
                error_log("Après marquage, is_read = " . $checkNotif['is_read']);
            }
            
            // Autres traitements... (photo, event, etc.)
            // ...
            
            echo json_encode(['success' => true, 'type' => $type]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID de notification manquant']);
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour de la notification: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
    // Méthode pour envoyer une notification push
    public function sendPush($userId, $type, $title, $body, $url = null) {
        $subscription = Notification::getSubscriptionByUserId($userId);
        if (!$subscription) return false;
        
        // Décoder la chaîne JSON en tableau associatif PHP
        $subscriptionArray = json_decode($subscription, true);
        
        // Vérifier que le décodage a fonctionné
        if (!is_array($subscriptionArray)) {
            error_log("Erreur: La subscription n'est pas un JSON valide pour l'utilisateur $userId");
            return false;
        }
        
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:dona7484@gmail.com',
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ]);
        
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'url' => $url
        ]);
        
        // Passer le tableau décodé à Subscription::create()
        $webPush->sendOneNotification(Subscription::create($subscriptionArray), $payload);
    
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) return false;
        }
        
        return true;
    }
    
    
    // Méthode privée pour récupérer l'ID du membre de la famille pour une notification donnée
    private function getFamilyMemberIdForNotification($notifId) {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->getFamilyMemberIdForNotification($notifId);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération du membre de la famille : ' . $e->getMessage());
            return false;
        }
    }
}