<?php
require_once __DIR__ . '/../config/database.php';

class Photo {
    // Constantes de sécurité renforcées
    private const MAX_FILE_SIZE_MODEL = 5 * 1024 * 1024; // 5MB 
    private const ALLOWED_MIME_TYPES_MODEL = [
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'image/webp'
    ];
    private const ALLOWED_EXTENSIONS_MODEL = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Extensions dangereuses à bloquer absolument
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 
        'js', 'html', 'htm', 'sh', 'bat', 'exe', 'scr',
        'com', 'pif', 'vbs', 'ws', 'reg', 'msi'
    ];
    
    // Signatures de fichiers (magic numbers) pour double vérification
    private const FILE_SIGNATURES = [
        'jpeg' => ["\xFF\xD8\xFF"],
        'png'  => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'gif'  => ["\x47\x49\x46\x38\x37\x61", "\x47\x49\x46\x38\x39\x61"],
        'webp' => ["\x52\x49\x46\x46", "\x57\x45\x42\x50"] // RIFF...WEBP
    ];

    private static $db = null;

    private static function getDb() {
        if (self::$db === null) {
            try {
                $dbConnect = new DbConnect();
                self::$db = $dbConnect->getConnection();
            } catch (PDOException $e) {
                error_log("ERREUR BDD dans Photo Model (getDb): " . $e->getMessage());
                throw new Exception("Service temporairement indisponible.");
            }
        }
        return self::$db;
    }

    /**
     * Sauvegarde ultra-sécurisée du fichier uploadé
     */
    public static function saveToStorage(array $fileData, string $uploadPath) {
        // 1. Vérification des erreurs d'upload
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            self::handleUploadError($fileData['error']);
        }

        $tempName = $fileData['tmp_name'];
        $originalName = $fileData['name'];
        $fileSize = $fileData['size'];

        // 2. Validation de la taille
        if ($fileSize <= 0) {
            throw new Exception("Le fichier est vide ou corrompu.");
        }
        if ($fileSize > self::MAX_FILE_SIZE_MODEL) {
            throw new Exception("Fichier trop volumineux (max: " . (self::MAX_FILE_SIZE_MODEL / 1024 / 1024) . " Mo).");
        }

        // 3. Validation du type MIME réel
        $detectedMime = self::getFileMimeType($tempName);
        if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES_MODEL, true)) {
            throw new Exception("Type de fichier non autorisé (détecté: " . $detectedMime . ").");
        }

        // 4. Validation de l'extension
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS_MODEL, true)) {
            throw new Exception("Extension non autorisée: ." . $extension);
        }
        
        // 5. Vérification anti-extension dangereuse
        if (in_array($extension, self::FORBIDDEN_EXTENSIONS, true)) {
            throw new Exception("Extension interdite pour des raisons de sécurité.");
        }
        
        // 6. Vérification de la signature du fichier (magic numbers)
        if (!self::verifyFileSignature($tempName, $extension)) {
            throw new Exception("Signature de fichier invalide - fichier potentiellement corrompu ou falsifié.");
        }

        // 7. Validation image avec getimagesize
        $imageInfo = @getimagesize($tempName);
        if ($imageInfo === false) {
            throw new Exception("Fichier image invalide ou corrompu.");
        }

        // 8. Vérification des dimensions
        $maxWidth = 4000;
        $maxHeight = 4000;
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            throw new Exception("Image trop grande (max: {$maxWidth}x{$maxHeight}px).");
        }
        
        // 9. Vérification du nom de fichier original
        if (!self::isSafeFilename($originalName)) {
            error_log("Nom de fichier suspect: " . $originalName);
            // On continue car on va générer un nouveau nom
        }

        // 10. Création sécurisée du répertoire
        if (!self::ensureSecureDirectory($uploadPath)) {
            throw new Exception("Impossible de créer le répertoire de destination.");
        }

        // 11. Génération d'un nom de fichier ultra-sécurisé
        $newFilename = self::generateSecureFilename($extension);
        $targetPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFilename;

        // 12. Vérification finale - le fichier de destination n'existe pas déjà
        if (file_exists($targetPath)) {
            // Très improbable avec notre génération aléatoire, mais on vérifie
            $newFilename = self::generateSecureFilename($extension);
            $targetPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFilename;
        }

        // 13. Déplacement sécurisé du fichier
        if (!move_uploaded_file($tempName, $targetPath)) {
            throw new Exception("Erreur lors de la sauvegarde du fichier.");
        }
        
        // 14. Application des permissions sécurisées
        if (!chmod($targetPath, 0644)) {
            error_log("Impossible de définir les permissions pour: " . $targetPath);
        }
        
        // 15. Vérification finale que le fichier sauvegardé est toujours valide
        if (!self::verifyUploadedFile($targetPath)) {
            unlink($targetPath); // Supprimer le fichier suspect
            throw new Exception("Le fichier sauvegardé a échoué à la vérification finale.");
        }

        return 'uploads/' . $newFilename;
    }
    
    /**
     * Détection sécurisée du type MIME
     */
    private static function getFileMimeType($filePath) {
        // Méthode 1: finfo (recommandée)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($mimeType) return $mimeType;
        }
        
        // Méthode 2: mime_content_type (fallback)
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if ($mimeType) return $mimeType;
        }
        
        throw new Exception("Impossible de déterminer le type MIME du fichier.");
    }
    
    /**
     * Vérification de la signature du fichier (magic numbers)
     */
    private static function verifyFileSignature($filePath, $expectedExtension) {
        $handle = fopen($filePath, 'rb');
        if (!$handle) return false;
        
        $header = fread($handle, 12); // Lire les premiers 12 bytes
        fclose($handle);
        
        switch ($expectedExtension) {
            case 'jpg':
            case 'jpeg':
                return strpos($header, "\xFF\xD8\xFF") === 0;
                
            case 'png':
                return strpos($header, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") === 0;
                
            case 'gif':
                return strpos($header, "\x47\x49\x46\x38\x37\x61") === 0 || 
                       strpos($header, "\x47\x49\x46\x38\x39\x61") === 0;
                       
            case 'webp':
                return strpos($header, "RIFF") === 0 && strpos($header, "WEBP") === 8;
                
            default:
                return false;
        }
    }
    
    /**
     * Vérification d'un nom de fichier sécurisé
     */
    private static function isSafeFilename($filename) {
        // Vérifier qu'il n'y a pas de caractères dangereux
        if (preg_match('/[<>:"|?*\/\\\\]/', $filename)) {
            return false;
        }
        
        // Vérifier qu'il n'y a pas de séquences dangereuses
        $dangerous = ['..', './', '\\', '__', 'php', 'phar'];
        foreach ($dangerous as $pattern) {
            if (stripos($filename, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Création sécurisée du répertoire
     */
    private static function ensureSecureDirectory($path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                return false;
            }
            
            // Créer un fichier .htaccess pour bloquer l'exécution de scripts
            $htaccessPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
            $htaccessContent = "# Sécurité - Bloquer l'exécution de scripts\n";
            $htaccessContent .= "Options -ExecCGI\n";
            $htaccessContent .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
            $htaccessContent .= "Options -Indexes\n";
            
            file_put_contents($htaccessPath, $htaccessContent);
        }
        
        return is_writable($path);
    }
    
    /**
     * Génération d'un nom de fichier ultra-sécurisé
     */
    private static function generateSecureFilename($extension) {
        // Utiliser plusieurs sources d'entropie
        $random1 = bin2hex(random_bytes(16));
        $random2 = uniqid('', true);
        $timestamp = microtime(true);
        
        // Combiner et hacher
        $combined = $random1 . $random2 . $timestamp;
        $hash = hash('sha256', $combined);
        
        // Prendre les 32 premiers caractères + extension
        return substr($hash, 0, 32) . '.' . $extension;
    }
    
    /**
     * Vérification finale du fichier uploadé
     */
    private static function verifyUploadedFile($filePath) {
        // Vérifier que le fichier existe et est lisible
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        
        // Vérifier que c'est toujours une image valide
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }
        
        // Vérifier la taille du fichier
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize > self::MAX_FILE_SIZE_MODEL) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Gestion des erreurs d'upload
     */
    private static function handleUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => "Fichier trop volumineux (limite serveur).",
            UPLOAD_ERR_FORM_SIZE  => "Fichier trop volumineux (limite formulaire).",
            UPLOAD_ERR_PARTIAL    => "Fichier partiellement téléchargé.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier sélectionné.",
            UPLOAD_ERR_NO_TMP_DIR => "Erreur serveur: dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Erreur serveur: impossible d'écrire le fichier.",
            UPLOAD_ERR_EXTENSION  => "Upload bloqué par une extension PHP.",
        ];
        
        $message = $errors[$errorCode] ?? "Erreur d'upload inconnue (code: $errorCode).";
        throw new Exception($message);
    }

    // Vos autres méthodes existantes restent inchangées...
    public static function save($seniorId, $senderId, $url, $message) {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO photos (user_id, sender_id, url, message, created_at, is_viewed) VALUES (?, ?, ?, ?, NOW(), 0)");
        if ($stmt->execute([$seniorId, $senderId, $url, $message])) {
            return $db->lastInsertId();
        }
        error_log("ERREUR BDD Photo::save: " . print_r($stmt->errorInfo(), true));
        return false;
    }
    
    public static function getByUserId($userId) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT p.*, 
                   CASE 
                       WHEN p.is_viewed = 1 THEN 'Lu' 
                       WHEN n.is_read = 1 THEN 'Alerté' 
                       ELSE 'Non alerté' 
                   END as status 
            FROM photos p 
            LEFT JOIN notifications n ON p.id = n.related_id AND n.type = 'photo' AND n.user_id = p.user_id
            WHERE p.sender_id = ? OR p.user_id = ? 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete($photoId) {
        $db = self::getDb();
        $db->beginTransaction();
        try {
            // Récupérer le chemin du fichier
            $stmt = $db->prepare("SELECT url FROM photos WHERE id = ?");
            $stmt->execute([$photoId]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$photo) {
                $db->rollBack();
                return false;
            }
            
            // Supprimer le fichier physique de manière sécurisée
            $basePath = __DIR__ . '/../../public/';
            $filePath = $basePath . $photo['url'];
            
            if (file_exists($filePath)) {
                // Vérifier que le fichier est dans le bon répertoire (sécurité)
                $realPath = realpath($filePath);
                $allowedPath = realpath($basePath . 'uploads/');
                
                if ($realPath && $allowedPath && strpos($realPath, $allowedPath) === 0) {
                    if (!unlink($filePath)) {
                        error_log("Impossible de supprimer le fichier: " . $filePath);
                    }
                } else {
                    error_log("Tentative de suppression de fichier en dehors du répertoire autorisé: " . $filePath);
                }
            }
            
            // Supprimer les notifications associées
            $stmtNotifs = $db->prepare("DELETE FROM notifications WHERE related_id = ? AND type = 'photo'");
            $stmtNotifs->execute([$photoId]);
            
            // Supprimer l'entrée en base
            $stmtDelete = $db->prepare("DELETE FROM photos WHERE id = ?");
            $result = $stmtDelete->execute([$photoId]);
            
            if ($result) {
                $db->commit();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Exception lors de la suppression de la photo: " . $e->getMessage());
            return false;
        }
    }

    public static function getById($photoId) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM photos WHERE id = ?");
        $stmt->execute([$photoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll() {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM photos ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getLastNotViewed() {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM photos WHERE is_viewed = 0 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function markAsViewed($photoId) {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE photos SET is_viewed = 1 WHERE id = ?");
        return $stmt->execute([$photoId]);
    }
}