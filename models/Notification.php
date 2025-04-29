<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private static $db;

    private static function getDb() {
        if (!self::$db) {
            $dbConnect = new DbConnect();
            self::$db = $dbConnect->getConnection();
        }
        return self::$db;
    }

    // Créer une nouvelle notification
    public static function create($userId, $type, $content, $relatedId = null, $isConfirmation = false) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, content, related_id, is_confirmation) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $type, $content, $relatedId, $isConfirmation ? 1 : 0]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erreur lors de la création de la notification: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les notifications non lues d'un utilisateur
    public static function getUnreadByUserId($userId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
            return [];
        }
    }

    // Récupérer les notifications non vues d'un utilisateur
    public static function getUnseenByUser($userId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_seen = 0 ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des notifications non vues: " . $e->getMessage());
            return [];
        }
    }

    // Marquer une notification comme vue
    public static function markAsSeen($notifId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("UPDATE notifications SET is_seen = 1 WHERE id = ?");
            return $stmt->execute([$notifId]);
        } catch (Exception $e) {
            error_log("Erreur lors du marquage de la notification comme vue: " . $e->getMessage());
            return false;
        }
    }

    // Marquer une notification comme lue
    public static function markAsRead($notifId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            return $stmt->execute([$notifId]);
        } catch (Exception $e) {
            error_log("Erreur lors du marquage de la notification comme lue: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer une notification par son ID
    public static function getById($notifId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE id = ?");
            $stmt->execute([$notifId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de la notification: " . $e->getMessage());
            return null;
        }
    }

    // Enregistrer un abonnement aux notifications push
    public static function saveSubscription($userId, $subscription) {
        try {
            $db = self::getDb();
            // Vérifier si un abonnement existe déjà
            $stmt = $db->prepare("SELECT id FROM push_subscriptions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() > 0) {
                // Mettre à jour l'abonnement existant
                $stmt = $db->prepare("UPDATE push_subscriptions SET subscription = ? WHERE user_id = ?");
                return $stmt->execute([$subscription, $userId]);
            } else {
                // Créer un nouvel abonnement
                $stmt = $db->prepare("INSERT INTO push_subscriptions (user_id, subscription) VALUES (?, ?)");
                return $stmt->execute([$userId, $subscription]);
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement de l'abonnement: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer l'abonnement d'un utilisateur
    public static function getSubscriptionByUserId($userId) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("SELECT subscription FROM push_subscriptions WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de l'abonnement: " . $e->getMessage());
            return null;
        }
    }
    
}
?>
