<?php
// Models/Notification.php

class Notification {
    public static function create($data) {
        $db = DbConnect::getConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, content) VALUES (?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['type'], $data['content']]);
    }

    public static function getUnseenByUser($userId) {
        $db = DbConnect::getConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markAsSeen($id) {
        $db = DbConnect::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}