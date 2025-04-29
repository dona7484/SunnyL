<?php
require_once __DIR__ . '/../config/database.php';

class AudioMessage {
    // Enregistrer un message audio
    public static function save($senderId, $receiverId, $audioData) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("INSERT INTO audio_messages (sender_id, receiver_id, audio_data, created_at) VALUES (?, ?, ?, NOW())");
            $result = $stmt->execute([$senderId, $receiverId, $audioData]);
            
            if ($result) {
                return $db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du message audio: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer les messages audio reçus par un utilisateur
    public static function getReceivedMessages($userId) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("
                SELECT am.*, u.name as sender_name 
                FROM audio_messages am
                JOIN users u ON am.sender_id = u.id
                WHERE am.receiver_id = ? 
                ORDER BY am.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des messages audio: " . $e->getMessage());
            return [];
        }
    }
    public static function getSentMessages($userId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
    
        try {
            // Log pour le débogage
            error_log("Récupération des messages audio envoyés par l'utilisateur ID: $userId");
            
            // Préparer la requête pour récupérer les messages audio envoyés par l'utilisateur
            $sql = "SELECT a.*, u.name as receiver_name 
                    FROM audio_messages a 
                    JOIN users u ON a.receiver_id = u.id 
                    WHERE a.sender_id = :userId 
                    ORDER BY a.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
    
            // Récupérer les résultats
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Nombre de messages audio envoyés trouvés: " . count($messages));
            
            return $messages;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des messages audio envoyés : " . $e->getMessage());
            return [];
        }
    }
    
    // Marquer un message audio comme lu
    public static function markAsRead($messageId) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("UPDATE audio_messages SET is_read = 1 WHERE id = ?");
            return $stmt->execute([$messageId]);
        } catch (Exception $e) {
            error_log("Erreur lors du marquage du message audio comme lu: " . $e->getMessage());
            return false;
        }
    }
}
