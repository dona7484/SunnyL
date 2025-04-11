<?php
// Models/Notification.php
require_once __DIR__ . '/../config/database.php';

class Notification {
    public static function create($userId, $type, $content, $relatedId = 0, $isConfirmation = false) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, content, related_id, is_confirmation, created_at, is_read) VALUES (?, ?, ?, ?, ?, NOW(), 0)");
            $result = $stmt->execute([$userId, $type, $content, $relatedId, $isConfirmation ? 1 : 0]);
            
            if ($result) {
                return $db->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de la création de notification: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getUnseenByUser($userId) {
        $db = (new DbConnect())->getConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Alias pour maintenir la compatibilité avec le nouveau code
    public static function getUnreadByUserId($userId) {
        return self::getUnseenByUser($userId);
    }

    public static function getById($id) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de la notification: " . $e->getMessage());
            return false;
        }
    }
    
    public static function markAsSeen($id) {
        $db = (new DbConnect())->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Alias pour maintenir la compatibilité avec le nouveau code
    public static function markAsRead($id) {
        return self::markAsSeen($id);
    }
    
    public static function getSubscriptionByUserId($userId) {
        $db = (new DbConnect())->getConnection();
        $stmt = $db->prepare("SELECT subscription FROM push_subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return json_decode($result['subscription'], true);
        }
        
        return null;
    }
    
    public static function saveSubscription($userId, $subscription) {
        $db = (new DbConnect())->getConnection();
        $subscriptionJson = json_encode($subscription);
        
        $stmt = $db->prepare("INSERT INTO push_subscriptions (user_id, subscription, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $subscriptionJson]);
    }
}
