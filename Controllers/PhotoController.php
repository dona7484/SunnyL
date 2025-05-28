<?php
require_once __DIR__ . '/../Controllers/NotificationController.php';

class PhotoController extends Controller {
    
    /**
     * Afficher le formulaire d'upload avec la liste des seniors liés
     */
    public function form() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? '';
        
        // Récupérer les seniors liés à cet utilisateur
        $linkedSeniors = [];
        
        if ($userRole !== 'senior') {
            try {
                require_once __DIR__ . '/../config/database.php';
                $dbConnect = new DbConnect();
                $db = $dbConnect->getConnection();
                
                // Requête pour récupérer les seniors liés à l'utilisateur connecté
                $stmt = $db->prepare("
                    SELECT u.id, u.name, u.email, u.role
                    FROM users u 
                    INNER JOIN relations r ON u.id = r.senior_id 
                    WHERE r.family_id = ? AND u.role = 'senior'
                    ORDER BY u.name ASC
                ");
                $stmt->execute([$userId]);
                $linkedSeniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                error_log("Erreur lors de la récupération des seniors liés : " . $e->getMessage());
                $_SESSION['error_message'] = "Erreur lors du chargement des destinataires.";
            }
        }
        
        // Passer les données à la vue
        $this->render('photo/upload', [
            'linkedSeniors' => $linkedSeniors,
            'userRole' => $userRole,
            'userId' => $userId
        ]);
    }
    
    public function uploadPhoto() {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Vérifier l'authentification
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Utilisateur non authentifié.");
            }
            
            $senderId = $_SESSION['user_id'];
            $senderRole = $_SESSION['role'] ?? '';
            
            // ID du senior destinataire depuis le formulaire
            $seniorId = $_POST['senior_id'] ?? null;
            if (!$seniorId) {
                throw new Exception("Veuillez sélectionner le senior destinataire.");
            }
            
            // SÉCURITÉ : Vérifier que l'utilisateur a le droit d'envoyer une photo à ce senior
            if (!$this->canSendPhotoToSenior($senderId, $seniorId, $senderRole)) {
                throw new Exception("Vous n'êtes pas autorisé à envoyer une photo à ce senior.");
            }
            
            // SÉCURITÉ FICHIERS : Validation complète du fichier
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $this->handleUploadError($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE);
            }
            
            $file = $_FILES['photo'];
            
            // Validation sécurisée du fichier
            $this->validateUploadedFile($file);
            
            $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
            
            // Limiter la longueur du message
            if (strlen($message) > 500) {
                throw new Exception("Le message ne peut pas dépasser 500 caractères.");
            }
            
            // Définir le dossier d'upload pour WAMP (chemin absolu)
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/sunnylink distant/SunnyLink/public/uploads/';
            
            // Alternative si le chemin ci-dessus ne marche pas
            if (!is_dir($uploadPath)) {
                $uploadPath = dirname(__DIR__) . '/public/uploads/';
            }
            
            // Normaliser les séparateurs pour Windows
            $uploadPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uploadPath);
            
            error_log("Chemin d'upload utilisé : " . $uploadPath);
            error_log("Le dossier existe : " . (is_dir($uploadPath) ? 'OUI' : 'NON'));
            
            // Vérifier que le répertoire existe et est accessible
            if (!is_dir($uploadPath)) {
                error_log("Tentative de création du répertoire : " . $uploadPath);
                if (!mkdir($uploadPath, 0777, true)) {
                    throw new Exception("Impossible de créer le répertoire d'upload : " . $uploadPath);
                }
            }
            
            if (!is_writable($uploadPath)) {
                error_log("Répertoire non accessible en écriture : " . $uploadPath);
                // Sur Windows/WAMP, essayer de corriger les permissions
                chmod($uploadPath, 0777);
                if (!is_writable($uploadPath)) {
                    throw new Exception("Le répertoire d'upload n'est pas accessible en écriture : " . $uploadPath);
                }
            }
            
            // Enregistrement sécurisé du fichier
            $uploadUrl = Photo::saveToStorage($file, $uploadPath);
            error_log("Photo enregistrée avec succès. Chemin: " . $uploadUrl);
            
            // Sauvegarde en base de données
            $photoId = Photo::save($seniorId, $senderId, $uploadUrl, $message);
            
            if (!$photoId) {
                throw new Exception("Erreur lors de l'enregistrement en base de données.");
            }
            
            // Envoi de la notification
            $notifController = new NotificationController();
            $notifId = $notifController->sendNotification(
                $seniorId, 
                'photo',
                'Nouvelle photo reçue' . ($message ? ' : ' . substr($message, 0, 50) : ''), 
                $photoId,
                false
            );
            
            $_SESSION['success_message'] = "Photo envoyée avec succès !";
            header('Location: index.php?controller=photo&action=gallery&id=' . $senderId);
            exit;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'upload de photo: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?controller=photo&action=form');
            exit;
        }
    }
    
    /**
     * Validation sécurisée du fichier uploadé
     */
    private function validateUploadedFile($file) {
        // Constantes de sécurité (correspondent à celles du modèle)
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // 1. Vérifier la taille
        if ($file['size'] <= 0) {
            throw new Exception("Le fichier est vide.");
        }
        
        if ($file['size'] > $maxFileSize) {
            throw new Exception("Le fichier est trop volumineux (maximum " . ($maxFileSize / 1024 / 1024) . " Mo).");
        }
        
        // 2. Vérifier le type MIME réel du fichier (pas celui déclaré par le navigateur)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($detectedMime, $allowedMimeTypes, true)) {
            throw new Exception("Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP.");
        }
        
        // 3. Vérifier l'extension
        $fileName = strtolower($file['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new Exception("Extension de fichier non autorisée.");
        }
        
        // 4. Vérification que c'est vraiment une image (double vérification)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception("Le fichier n'est pas une image valide.");
        }
        
        // 5. Vérifier les dimensions (optionnel - pour éviter les images trop grandes)
        $maxWidth = 4000;
        $maxHeight = 4000;
        
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            throw new Exception("Image trop grande. Dimensions maximales : {$maxWidth}x{$maxHeight} pixels.");
        }
        
        // 6. Vérifier le nom de fichier pour éviter les caractères dangereux
        if (preg_match('/[^a-zA-Z0-9._-]/', basename($fileName))) {
            // Le nom sera de toute façon régénéré, mais on peut le vérifier
            error_log("Nom de fichier suspect détecté : " . $fileName);
        }
        
        // 7. Vérifier que ce n'est pas un fichier exécutable déguisé
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'js', 'html', 'htm', 'sh', 'bat', 'exe'];
        $actualExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($actualExtension, $dangerousExtensions)) {
            throw new Exception("Type de fichier potentiellement dangereux.");
        }
        
        return true;
    }
    
    /**
     * Gestion des erreurs d'upload
     */
    private function handleUploadError($errorCode) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => "Le fichier dépasse la taille maximale autorisée par le serveur.",
            UPLOAD_ERR_FORM_SIZE  => "Le fichier dépasse la taille maximale spécifiée.",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier n'a été sélectionné.",
            UPLOAD_ERR_NO_TMP_DIR => "Erreur serveur : dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Erreur serveur : impossible d'écrire le fichier.",
            UPLOAD_ERR_EXTENSION  => "Upload bloqué par une extension PHP.",
        ];
        
        $message = $uploadErrors[$errorCode] ?? "Erreur inconnue lors de l'envoi (code: $errorCode).";
        throw new Exception($message);
    }
    
    /**
     * Vérifier si un utilisateur peut envoyer une photo à un senior
     */
    private function canSendPhotoToSenior($senderId, $seniorId, $senderRole) {
        try {
            // Si l'expéditeur est admin, il peut envoyer à n'importe qui
            if ($senderRole === 'admin') {
                return true;
            }
            
            // Si l'expéditeur est un senior, il ne peut pas envoyer de photos
            if ($senderRole === 'senior') {
                return false;
            }
            
            // Vérifier la relation family -> senior
            require_once __DIR__ . '/../config/database.php';
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM relations r
                INNER JOIN users u ON u.id = r.senior_id
                WHERE r.family_id = ? AND r.senior_id = ? AND u.role = 'senior'
            ");
            $stmt->execute([$senderId, $seniorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Erreur lors de la vérification des permissions : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une photo
     */
    public function delete() {
        header('Content-Type: application/json');
        
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $photoId = $data['photoId'] ?? null;
            
            if (!$photoId) {
                throw new Exception("ID de photo manquant");
            }
            
            // Vérifier les permissions
            $photo = Photo::getById($photoId);
            if (!$photo) {
                throw new Exception("Photo introuvable");
            }
            
            // Vérifier que l'utilisateur est le propriétaire ou le destinataire
            if ($photo['sender_id'] != $_SESSION['user_id'] && $photo['user_id'] != $_SESSION['user_id']) {
                throw new Exception("Vous n'avez pas l'autorisation de supprimer cette photo");
            }
            
            $result = Photo::delete($photoId);
            
            if ($result) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Photo supprimée avec succès"
                ]);
            } else {
                throw new Exception("Erreur lors de la suppression de la photo");
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer toutes les photos pour le diaporama
     */
    public function getAllForSlideshow() {
        header('Content-Type: application/json');
        
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => 'Utilisateur non connecté']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $role = $_SESSION['role'] ?? '';
            
            // Pour les seniors, récupérer les photos qui leur sont destinées
            if ($role === 'senior') {
                $photos = Photo::getByUserId($userId);
            } else {
                // Pour les membres de la famille, récupérer les photos qu'ils ont envoyées
                $photos = Photo::getByUserId($userId);
            }
            
            $slideshowPhotos = array_map(function($photo) {
                return [
                    'id' => $photo['id'],
                    'url' => $photo['url'],
                    'message' => $photo['message'] ?? '',
                    'created_at' => $photo['created_at']
                ];
            }, $photos);
            
            echo json_encode($slideshowPhotos);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des photos pour le diaporama: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Marquer une photo comme vue
     */
    public function markViewed() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $photoId = $data['photoId'] ?? null;
            
            if (!$photoId) {
                throw new Exception("Photo ID missing.");
            }
            
            error_log("Tentative de marquer la photo ID: $photoId comme vue");
            
            Photo::markAsViewed($photoId);
            
            // Mettre à jour la notification associée
            $notificationModel = new NotificationModel();
            $notifications = $notificationModel->getNotificationsByRelatedId($photoId, 'photo');
            
            foreach ($notifications as $notification) {
                $notificationModel->markAsRead($notification['id']);
                error_log("Notification ID: " . $notification['id'] . " marquée comme lue");
            }
            
            echo json_encode([
                "status" => "ok",
                "message" => "Photo marquée comme vue."
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors du marquage de la photo comme vue: " . $e->getMessage());
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Galerie photos
     */
    public function gallery() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_GET['id'] ?? ($_SESSION['user_id'] ?? null);
        if ($userId === null) {
            echo "Utilisateur non spécifié.";
            return;
        }
        
        $photos = Photo::getByUserId($userId);
        
        $this->render('photo/gallery', [
            'photos' => $photos,
            'userId' => $userId
        ]);
    }
}