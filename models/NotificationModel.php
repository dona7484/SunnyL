<?php
require_once __DIR__ . '/../config/database.php';

class NotificationModel {
    protected $connection;

    public function __construct() {
        // Initialiser la connexion à la base de données
        $dbConnect = new DbConnect();
        $this->connection = $dbConnect->getConnection();
    }
    // Modifications à apporter au modèle de notification (NotificationModel.php)


/**
 * Cette méthode permet d'obtenir l'expéditeur d'une notification
 * @param int $notifId ID de la notification
 * @return int|null ID de l'expéditeur ou null si non trouvé
 */
public function getSenderIdForNotification($notifId) {
    
        // Pour les notifications standard comme les messages ou les photos
        $sql = "SELECT n.sender_id FROM notifications n WHERE n.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notifId]);
        $senderId = $stmt->fetchColumn();
        
        if ($senderId) {
            return $senderId;
        }
    }
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
    }
    public function getAllNotifications($userId) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ajouter une notification
    // Méthode pour créer une notification avec event_id
    public function createNotification($userId, $type, $content, $eventId = null) {
        $sql = "INSERT INTO notifications (user_id, type, content, event_id) VALUES (:user_id, :type, :content, :event_id)";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':content' => $content,
            ':event_id' => $eventId // Utilisez event_id si disponible
        ]);
    }
    
    // public function getUnreadNotifications($userId) {
    //     $userRoleQuery = "SELECT role FROM users WHERE id = :user_id";
    //     $stmtRole = $this->connection->prepare($userRoleQuery);
    //     $stmtRole->execute([':user_id' => $userId]);
    //     $userRole = $stmtRole->fetchColumn();
        
    //     $sql = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0";
        
    //     if ($userRole === 'senior') {
    //         $sql .= " AND (type != 'read_confirmation' AND is_confirmation = 0)";
    //     }
        
    //     $sql .= " ORDER BY created_at DESC";
        
    //     $stmt = $this->connection->prepare($sql);
    //     $stmt->execute([':user_id' => $userId]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
    public function getUnreadNotifications($userId) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Marquer une notification comme lue
    public function markAsRead($notifId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $notifId]);
    }
    
    public function getNotificationsByRelatedId($relatedId, $type) {
        $sql = "SELECT * FROM notifications WHERE related_id = :related_id AND type = :type";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':related_id' => $relatedId,
            ':type' => $type
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ajouter une méthode pour obtenir la notification par ID (par exemple)
    public function getNotificationById($id) {
        $sql = "SELECT * FROM notifications WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode pour récupérer l'ID du membre de la famille d'une notification
    public function getFamilyMemberIdForNotification($notifId) {
        $sql = "SELECT user_id FROM notifications WHERE id = :notif_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':notif_id' => $notifId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_id']; // Ou tout autre ID lié à la famille
    }
}
?>
