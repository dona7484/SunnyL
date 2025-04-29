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
 * Vérifie si une notification de type appel vidéo est en attente pour un utilisateur
 * @param int $userId ID de l'utilisateur
 * @return bool
 */
public function hasActiveVideoCall($userId) {
    try {
        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE user_id = ? 
                AND type = 'video_call' 
                AND is_read = 0 
                AND created_at > NOW() - INTERVAL 2 MINUTE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification des appels vidéo actifs: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère la dernière notification d'appel vidéo pour un utilisateur
 * @param int $userId ID de l'utilisateur
 * @return array|null La notification ou null si aucune n'est trouvée
 */
public function getLatestVideoCall($userId) {
    try {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                AND type = 'video_call' 
                AND is_read = 0 
                AND created_at > NOW() - INTERVAL 2 MINUTE
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $notification ?: null;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du dernier appel vidéo: " . $e->getMessage());
        return null;
    }
}

/**
 * Cette méthode permet d'obtenir l'expéditeur d'une notification
 * @param int $notifId ID de la notification
 * @return int|null ID de l'expéditeur ou null si non trouvé
 */
public function getSenderIdForNotification($notifId) {
    try {
        // Pour les notifications standard comme les messages ou les photos
        $sql = "SELECT n.sender_id FROM notifications n WHERE n.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notifId]);
        $senderId = $stmt->fetchColumn();
        
        if ($senderId) {
            return $senderId;
        }
        
        // Pour les notifications d'appel vidéo (où sender_id pourrait être stocké différemment)
        $sql = "SELECT n.content FROM notifications n WHERE n.id = ? AND n.type = 'video_call'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notifId]);
        $content = $stmt->fetchColumn();
        
        if ($content && preg_match('/de (\w+)/', $content, $matches)) {
            // Obtenir l'ID utilisateur à partir du nom
            $sql = "SELECT user_id FROM users WHERE name = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$matches[1]]);
            return $stmt->fetchColumn();
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de l'expéditeur: " . $e->getMessage());
        return null;
    }
}
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
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
