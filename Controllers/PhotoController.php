<?php
require_once __DIR__ . '/../Controllers/NotificationController.php';

class PhotoController extends Controller {
    public function uploadPhoto() {
        try {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            // Log pour le débogage
            error_log("Début de l'upload de photo");
            
            // Vérifier les données du formulaire
            error_log("Données du formulaire: " . print_r($_POST, true));
            error_log("Fichiers: " . print_r($_FILES, true));
            
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
            
            // Log pour le débogage
            error_log("Photo enregistrée avec succès. Chemin: " . $uploadUrl);
    
            // Sauvegarde des informations en base de données
            $photoId = Photo::save($seniorId, $senderId, $uploadUrl, $message);
    
            // Envoi d'une notification au senior
            $notifController = new NotificationController();
            $notifId = $notifController->sendNotification(
                $seniorId, 
                'photo',
                'Nouvelle photo reçue : ' . $message, 
                $photoId,
                false
            );
    
            // Rediriger vers la galerie photo au lieu de renvoyer un JSON
            header('Location: index.php?controller=photo&action=gallery&id=' . $senderId);
            exit;
        } catch (Exception $e) {
            error_log("Erreur lors de l'upload de photo: " . $e->getMessage());
            // En cas d'erreur, afficher un message d'erreur
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?controller=photo&action=form');
            exit;
        }
    }
    /**
 * Supprimer une photo
 */
public function delete() {
    header('Content-Type: application/json');
    
    try {
        // Récupération des données POST en JSON
        $data = json_decode(file_get_contents('php://input'), true);
        $photoId = $data['photoId'] ?? null;
        
        if (!$photoId) {
            throw new Exception("ID de photo manquant");
        }
        
        // Vérifier que l'utilisateur a le droit de supprimer cette photo
        $photo = Photo::getById($photoId);
        
        if (!$photo) {
            throw new Exception("Photo introuvable");
        }
        
        // Vérifier que l'utilisateur est le propriétaire ou le destinataire de la photo
        if ($photo['sender_id'] != $_SESSION['user_id'] && $photo['user_id'] != $_SESSION['user_id']) {
            throw new Exception("Vous n'avez pas l'autorisation de supprimer cette photo");
        }
        
        // Supprimer la photo
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
        header('Content-Type: application/json');
        
        try {
            // Récupération des données POST en JSON
            $data = json_decode(file_get_contents('php://input'), true);
            $photoId = $data['photoId'] ?? null;
            
            if (!$photoId) {
                throw new Exception("Photo ID missing.");
            }
            
            // Log pour le débogage
            error_log("Tentative de marquer la photo ID: $photoId comme vue");
            
            // Marquer la photo comme vue
            Photo::markAsViewed($photoId);
            
            // Mettre à jour également la notification associée
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
        
        // Récupérer les photos de l'utilisateur avec leur statut
        $photos = Photo::getByUserId($userId);
        
        // Passer les données à la vue
        $this->render('photo/gallery', [
            'photos' => $photos,
            'userId' => $userId
        ]);
    }
}
