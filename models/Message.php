<?php
require_once __DIR__ . '/../config/database.php';

class Message {

    // Méthode pour sauvegarder un message dans la base de données
    public static function save($senderId, $receiverId, $messageText) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        
        try {
            // Préparer la requête pour insérer un message
            $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);

            // Exécuter la requête
            $stmt->execute([$senderId, $receiverId, $messageText]);

            // Vérifier si l'insertion a réussi
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                throw new Exception("Erreur lors de l'enregistrement du message.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
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
