<?php
require_once __DIR__ . '/../config/database.php';

class Message {

    // Méthode pour sauvegarder un message dans la base de données
    public static function save($senderId, $receiverId, $messageText) {
        try {
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            
            // Construction du message
            $message = htmlspecialchars($messageText);
            
            // Préparation de la requête SQL
            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            
            // Exécution de la requête avec les paramètres
            $stmt->execute([$senderId, $receiverId, $message]);
    
            // Retourner l'ID du message créé
            if ($stmt->rowCount() > 0) {
                return $db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du message : " . $e->getMessage());
            return false;
        }
    }
// Méthode pour récupérer les messages envoyés par un utilisateur
public static function getSentMessages($userId) {
    $dbConnect = new DbConnect();
    $db = $dbConnect->getConnection();

    try {
        // Log pour le débogage
        error_log("Récupération des messages envoyés par l'utilisateur ID: $userId");
        
        // Préparer la requête pour récupérer les messages envoyés par l'utilisateur
        $sql = "SELECT m.*, u.name as receiver_name, m.is_read 
                FROM messages m 
                JOIN users u ON m.receiver_id = u.id 
                WHERE m.sender_id = :userId 
                ORDER BY m.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['userId' => $userId]);

        // Récupérer les résultats
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Nombre de messages envoyés trouvés: " . count($messages));
        
        return $messages;
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des messages envoyés : " . $e->getMessage());
        return [];
    }
}

    public static function saveAudio($senderId, $receiverId, $audioData) {
        try {
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            
            // Utiliser la date et l'heure actuelles
            $currentDateTime = date('Y-m-d H:i:s');
            
            $stmt = $db->prepare("INSERT INTO audio_messages (sender_id, receiver_id, audio_data, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$senderId, $receiverId, $audioData, $currentDateTime]);
    
            return $db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du message audio : " . $e->getMessage());
            return false;
        }
    }
    public static function delete($id) {
        $db = (new DbConnect())->getConnection();
        $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
    }
    public static function getById($id) {
        $db = (new DbConnect())->getConnection();
        $stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    // Méthode pour récupérer les messages reçus par un utilisateur
    public static function getReceivedMessages($userId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
    
        try {
            // Log pour le débogage
            error_log("Récupération des messages pour l'utilisateur ID: $userId");
            
            // Préparer la requête pour récupérer les messages reçus par l'utilisateur
            $sql = "SELECT m.*, u.name as sender_name 
                    FROM messages m 
                    JOIN users u ON m.sender_id = u.id 
                    WHERE m.receiver_id = :userId 
                    ORDER BY m.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
    
            // Récupérer les résultats
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Nombre de messages trouvés: " . count($messages));
            
            return $messages;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des messages : " . $e->getMessage());
            return [];
        }
    }
}    
?>
