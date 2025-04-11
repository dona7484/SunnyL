<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Controller.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationController extends Controller {
    private $vapidPublicKey = 'BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI';
    private $vapidPrivateKey = 'L8IGRAqN9gHQDL9ewkV3_IsmtMLxSU9ZHWeyyHpUHwU';

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
        // Pour les messages audio, forcer le type à 'audio'
        if (strpos(strtolower($content), 'audio') !== false) {
            $type = 'audio';
        }
        
        $notifId = Notification::create($userId, $type, $content, $relatedId, $isConfirmation);
        error_log("Notification créée - Type: $type, UserId: $userId, Content: $content, NotifId: $notifId");
        
        // URL de redirection
        $url = 'index.php?controller=home&action=dashboard'; // URL par défaut
        if ($type === 'message' || $type === 'audio') {
            $url = 'index.php?controller=message&action=received';
        } elseif ($type === 'photo') {
            $url = 'index.php?controller=photo&action=gallery';
        } elseif ($type === 'event') {
            $url = 'index.php?controller=event&action=index';
        }
        
        // Envoyer une notification push
        $this->sendPush($userId, $type, 'Nouvelle notification', $content, $url);
        return $notifId;
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de la notification : " . $e->getMessage());
        return false;
    }
}


    // Méthode pour récupérer toutes les notifications non lues de l'utilisateur
    public function getUserNotifications() {
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            // Assurez-vous que $_SESSION['user_id'] est défini
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("L'utilisateur n'est pas connecté.");
            }
    
            // Récupération des notifications
            $currentNotifications = Notification::getUnreadByUserId($_SESSION['user_id']);
            
            // Définir l'en-tête JSON
            header('Content-Type: application/json');
            
            // Retourner les notifications en JSON
            echo json_encode($currentNotifications);
        } catch (Exception $e) {
            error_log("Erreur dans getUserNotifications : " . $e->getMessage());
            echo json_encode(['error' => 'Une erreur est survenue dans la récupération des notifications']);
        }
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
                
                $result = $notificationModel->markAsRead($notifId);
                
                if ($result) {
                    // Récupérer l'ID du membre de la famille (expéditeur)
                    $senderId = $notificationModel->getFamilyMemberIdForNotification($notifId);
                    
                    if ($senderId) {
                        // Vérifier que l'expéditeur est bien un family member
                        $userModel = new User();
                        $sender = $userModel->getById($senderId);
                        
                        if ($sender && $sender['role'] === 'familymember') {
                            // Envoyer une notification au family member
                            $this->sendNotification(
                                $senderId,
                                'read_confirmation',
                                'Votre message a été lu par le senior',
                                $notification['related_id'] ?? null,
                                true
                            );
                        }
                    }
                    
                    // Inclure le type dans la réponse pour le débogage
                    echo json_encode(['success' => true, 'type' => $type]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Mise à jour échouée']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID de notification manquant']);
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour de la notification: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    // Méthode pour envoyer une notification push
    public function sendPush($userId, $type, $title, $body, $url = null) {
        $subscription = Notification::getSubscriptionByUserId($userId);
        if (!$subscription) return false;
    
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
        
        $webPush->sendOneNotification(Subscription::create(json_decode($subscription, true)), $payload);
    
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

