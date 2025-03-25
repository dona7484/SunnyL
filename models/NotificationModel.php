<?php
require_once __DIR__ . '/../config/database.php';
class NotificationModel {
    private $pdo;

    public function __construct() {
        // On s'assure que la connexion est bien initialisée ici
        require_once __DIR__ . '/../config/database.php'; // Assurez-vous que votre connexion est bien importée.
        $db = new DbConnect();
        $this->pdo = $db->getConnection(); // Assurez-vous que cette méthode retourne une instance valide de PDO
    }

    // Ajouter une notification
 // Méthode pour créer une notification avec event_id
public function createNotification($userId, $type, $content, $eventId = null) {
    $sql = "INSERT INTO notifications (user_id, type, content, event_id) VALUES (:user_id, :type, :content, :event_id)";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        ':user_id' => $userId,
        ':type' => $type,
        ':content' => $content,
        ':event_id' => $eventId // Utilisez event_id si disponible
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

    // Ajouter une méthode pour obtenir la notification par ID (par exemple)
    public function getNotificationById($id) {
        $sql = "SELECT * FROM notifications WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode pour récupérer l'ID du membre de la famille d'une notification
    public function getFamilyMemberIdForNotification($notifId) {
        $sql = "SELECT user_id FROM notifications WHERE id = :notif_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':notif_id' => $notifId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_id']; // Ou tout autre ID lié à la famille
    }
}
?>
