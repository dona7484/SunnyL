<?php
class NotificationModel {
    private $pdo;

    public function __construct() {
        // require_once __DIR__ . '/../config/database.php';
        $db = new DbConnect();
        $this->pdo = $db->getConnection();
    }

    // Ajouter une notification
    public function createNotification($userId, $type, $content) {
        $sql = "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, :type, :content)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':content' => $content
        ]);
    }

    // Récupérer toutes les notifications non lues d'un utilisateur
    public function getUnreadNotifications($userId) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marquer une notification comme lue
    public function markAsRead($notifId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $notifId]);
    }
}
?>
