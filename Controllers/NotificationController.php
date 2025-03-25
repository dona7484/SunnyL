<?php
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
    }

    // Méthode pour récupérer toutes les notifications non lues de l'utilisateur
    public function getUserNotifications() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Utilisateur non connecté. Veuillez vous connecter pour voir vos notifications.']);
            exit;
        }
    
        // Récupérer les notifications non lues via le modèle
        $notifications = $this->notificationModel->getUnreadNotifications($_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode($notifications);
    }
    
    // Méthode pour récupérer la dernière notification non lue
    public function getLastUnreadNotification($userId) {
        $notifications = $this->notificationModel->getUnreadNotifications($userId);
    
        if (empty($notifications)) {
            return null;
        }
    
        return $notifications[0];
    }
    
    // Méthode pour envoyer une notification
    public function sendNotification($userId, $type, $content, $eventId = null) {
        $this->notificationModel->createNotification($userId, $type, $content, $eventId);
    }

    public function markNotificationAsRead() {
        try {
            if (isset($_POST['notif_id'])) {
                $notifId = $_POST['notif_id'];
    
                // Marquer la notification comme lue dans la base de données
                $this->notificationModel->markAsRead($notifId);
    
                // Récupérer l'ID de l'événement depuis la notification
                $notification = $this->notificationModel->getNotificationById($notifId);
                $eventId = $notification['event_id'];  // Assurez-vous que la notification contient un event_id
    
                if ($eventId !== null) {
                    // Mettre à jour le statut de l'événement en "alerté"
                    $eventModel = new EventModel();
                    $eventModel->markAlertAsTriggered($eventId);  // Marquer l'événement comme alerté
                }
    
                // Répondre avec un message de succès
                echo json_encode(['success' => true, 'message' => 'Notification marquée comme lue.']);
            } else {
                echo json_encode(['error' => 'ID de notification manquant.']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur lors de la mise à jour de la notification : ' . $e->getMessage()]);
        }
    }
    
    // Méthode pour récupérer l'ID du membre de la famille à partir de la notification
    private function getFamilyMemberIdForNotification($notifId) {
        // Logique pour récupérer l'ID du membre de la famille
        // Par exemple, en récupérant le `user_id` de la notification
        $notification = $this->notificationModel->getNotificationById($notifId);
        return $notification['user_id'];  // Remplacer par la logique qui correspond à ta table
    }
}
?>
