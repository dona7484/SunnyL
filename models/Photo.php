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
   // Méthode pour récupérer toutes les photos d'un utilisateur avec leur statut
   public static function getByUserId($userId) {
    $dbConnect = new DbConnect();
    $db = $dbConnect->getConnection();
    $stmt = $db->prepare("
        SELECT p.*, 
               CASE 
                   WHEN p.is_viewed = 1 THEN 'Lu' 
                   WHEN n.is_read = 1 THEN 'Alerté' 
                   ELSE 'Non alerté' 
               END as status 
        FROM photos p 
        LEFT JOIN notifications n ON p.id = n.related_id AND n.type = 'photo' 
        WHERE p.sender_id = ? OR p.user_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Méthode pour vérifier si une photo a été lue
    public static function isRead($photoId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT is_viewed FROM photos WHERE id = ?");
        $stmt->execute([$photoId]);
        return (bool)$stmt->fetchColumn();
    }
    // Sauvegarde le fichier uploadé dans le dossier uploads/
    public static function saveToStorage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("❌ Échec de l'envoi du fichier: code " . $file['error']);
        }
    
        // Utiliser un chemin absolu direct
        $uploadDir = '/var/www/html/SunnyLink/public/uploads/';
        
        // Alternative avec chemin relatif plus clair
        // $uploadDir = __DIR__ . '/../public/uploads/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("❌ Impossible de créer le répertoire d'upload.");
            }
            chmod($uploadDir, 0777);
        }
    
        $filename = uniqid() . '-' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        $webPath = 'uploads/' . $filename;
    
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $error = error_get_last();
            throw new Exception("❌ Erreur lors du déplacement du fichier: " . ($error['message'] ?? 'Raison inconnue'));
        }
        
        return $webPath;
    }
    
    // Méthode pour vérifier si une notification a été envoyée pour cette photo
    public static function isAlerted($photoId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE related_id = ? AND type = 'photo' AND is_read = 1
        ");
        $stmt->execute([$photoId]);
        return (bool)$stmt->fetchColumn();
    }
    /**
 * Supprime une photo par son ID
 */
public static function delete($photoId) {
    try {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        
        // Récupérer le chemin du fichier avant de le supprimer de la base de données
        $stmt = $db->prepare("SELECT url FROM photos WHERE id = ?");
        $stmt->execute([$photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$photo) {
            return false;
        }
        
        // Supprimer le fichier physique
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/SunnyLink/' . $photo['url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Supprimer les notifications associées
        $stmt = $db->prepare("DELETE FROM notifications WHERE related_id = ? AND type = 'photo'");
        $stmt->execute([$photoId]);
        
        // Supprimer l'entrée dans la base de données
        $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
        $result = $stmt->execute([$photoId]);
        
        return $result;
    } catch (Exception $e) {
        error_log("Erreur lors de la suppression de la photo: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère une photo par son ID
 */
public static function getById($photoId) {
    $dbConnect = new DbConnect();
    $db = $dbConnect->getConnection();
    $stmt = $db->prepare("SELECT * FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
