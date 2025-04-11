<?php
require_once __DIR__ . '/../Controllers/NotificationController.php';

class PhotoController extends Controller {
    public function uploadPhoto() {
        try {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
    
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // ID du family member (expéditeur)
            $senderId = $_SESSION['user_id'] ?? null;
            if (!$senderId) {
                throw new Exception("Utilisateur non authentifié.");
            }
    
            // ID du senior destinataire depuis le formulaire
            $seniorId = $_POST['senior_id'] ?? null;
            if (!$seniorId) {
                throw new Exception("Veuillez sélectionner le senior destinataire.");
            }
    
            // Validation du fichier et du message
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Erreur lors de l'envoi du fichier.");
            }
            $file = $_FILES['photo'];
            $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
    
            // Enregistrement du fichier dans le dossier uploads
            $uploadUrl = Photo::saveToStorage($file);
    
// Sauvegarde des informations en base de données avec l'ID du senior et celui du family member
$photoId = Photo::save($seniorId, $senderId, $uploadUrl, $message);

// Envoi d'une notification au senior avec tous les paramètres requis
$notifController = new NotificationController();
$notifId = $notifController->sendNotification(
    $seniorId, 
    'photo',
    'Nouvelle photo reçue : ' . $message, 
    $photoId,
    false // Ce paramètre indique que ce n'est pas une notification de confirmation
);

if ($notifId) {
    // La notification a été créée avec succès
    error_log("Notification de photo envoyée - Type: photo, ID: " . $notifId . ", Message: " . $message);
} else {
    // Il y a eu un problème lors de l'envoi de la notification
    error_log("Erreur lors de l'envoi de la notification de photo");
}
    
                echo json_encode([
                    "status" => "ok",
                    "message" => "Photo envoyée avec succès.",
                    "photoId" => $photoId,
                    "notifId" => $notifId // ID de la notification créée
                ]);
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
        try {
            // Ici, vous pouvez adapter la méthode pour récupérer uniquement les photos à afficher dans le slideshow.
            // Par exemple, on récupère toutes les photos :
            $photos = Photo::getAll();
            echo json_encode($photos);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer la dernière photo non vue (pour affichage en modal)
     */
    public function getLastPhoto() {
        try {
            // On suppose que la méthode getLastNotViewed() existe dans le modèle Photo.
            $photo = Photo::getLastNotViewed();
            echo json_encode($photo);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Marquer une photo comme vue
     */
    public function markViewed() {
        try {
            // Récupération des données POST en JSON
            $data = json_decode(file_get_contents('php://input'), true);
            $photoId = $data['photoId'] ?? null;
            if (!$photoId) {
                throw new Exception("Photo ID missing.");
            }
            // On suppose que la méthode markAsViewed($photoId) existe dans le modèle Photo.
            Photo::markAsViewed($photoId);
            echo json_encode([
                "status" => "ok",
                "message" => "Photo marquée comme vue."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer les photos d'un utilisateur (pour la galerie)
     */
    public function getPhotos($id) {
        try {
            $photos = Photo::getByUserId($id);
            echo json_encode($photos);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function form() {
        // Inclusion du formulaire d'upload (vérifiez le chemin)
        require_once __DIR__ . '/../views/photo/upload.php';
    }

    public function gallery() {
        // Prioriser l'ID passé en GET, sinon utiliser celui de la session
        $userId = $_GET['id'] ?? ($_SESSION['user_id'] ?? null);
        if ($userId === null) {
            echo "Utilisateur non spécifié.";
            return;
        }
        // Récupérer les photos de l'utilisateur
        $photos = Photo::getByUserId($userId);
        $GLOBALS['userId'] = $userId;
        require_once __DIR__ . '/../views/photo/gallery.php';
    }    

}
