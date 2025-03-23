<?php
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
    }
    public function get() {
        $this->getUserNotifications();
    }
    public function getLastUnreadNotification($userId) {
        $notifications = $this->notificationModel->getUnreadNotifications($userId);
        return $notifications[0] ?? null;
    }
    
    public function getUserNotifications() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        $notifications = $this->notificationModel->getUnreadNotifications($_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode($notifications);
    }

    public function sendNotification($userId, $type, $content) {
        return $this->notificationModel->createNotification($userId, $type, $content);
    }
    
    public function markNotificationAsRead() {
        if (isset($_POST['notif_id'])) {
            $this->notificationModel->markAsRead($_POST['notif_id']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'ID manquant']);
        }
    }
}
?>
