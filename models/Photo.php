<?php
class Photo {
    // Enregistre une nouvelle photo dans la base
    public static function save($seniorId, $senderId, $url, $message) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("INSERT INTO photos (user_id, sender_id, url, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$seniorId, $senderId, $url, $message]);
        
        // Retourner l'ID de la photo nouvellement créée
        return $db->lastInsertId();
    }
    
    
    // Récupère toutes les photos d'un utilisateur en les classant par date décroissante
    public static function getByUserId($userId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Sauvegarde le fichier uploadé dans le dossier uploads/
    public static function saveToStorage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("❌ Échec de l'envoi du fichier.");
        }
    
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        $filename = uniqid() . '-' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
    
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("❌ Erreur lors du déplacement du fichier.");
        }
        return $targetPath;
    }
    
    // Récupère toutes les photos (par exemple pour le diaporama)
    public static function getAll() {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT * FROM photos ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupère la dernière photo non vue (pour l'affichage en modal)
    public static function getLastNotViewed() {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        // Utilisation de la colonne is_viewed conformément à votre table
        $stmt = $db->prepare("SELECT * FROM photos WHERE is_viewed = 0 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Marque une photo comme vue en mettant à jour is_viewed
    public static function markAsViewed($photoId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("UPDATE photos SET is_viewed = 1 WHERE id = ?");
        $stmt->execute([$photoId]);
    }
}
?>
