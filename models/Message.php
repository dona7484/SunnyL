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
    
            // Vérification si l'insertion a réussi
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du message : " . $e->getMessage());
            return false;
        }
    }
    
    
    // Méthode pour récupérer les messages reçus par un utilisateur
    public static function getReceivedMessages($userId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();

        try {
            // Préparer la requête pour récupérer les messages reçus par l'utilisateur
            $sql = "SELECT * FROM messages WHERE receiver_id = :userId ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['userId' => $userId]);

            // Récupérer les résultats
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }
}
?>
