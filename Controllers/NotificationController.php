<?php
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        // Instanciation du modèle de notification
        $this->notificationModel = new NotificationModel();
    }

    // Méthode pour créer une notification
    public function create() {
        try {
            // Vérification des paramètres POST
            if (!isset($_POST['userId']) || !isset($_POST['message'])) {
                throw new Exception("Les paramètres userId et message sont requis.");
            }

            // Récupération des paramètres
            $userId = $_POST['userId'];
            $message = $_POST['message'];

            // Log pour vérifier les valeurs
            error_log("Création de notification: userId = $userId, message = $message");

            // Créer la notification via le modèle
            $this->notificationModel->createNotification($userId, 'alert', $message);

            // Répondre en JSON
            echo json_encode(['success' => true, 'message' => 'Notification envoyée.']);
        } catch (Exception $e) {
            // Log de l'erreur
            error_log("Erreur lors de la création de la notification: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Méthode pour marquer la notification comme lue
    public function markNotificationAsRead() {
        try {
            // Récupérer les données JSON
            $jsonData = json_decode(file_get_contents('php://input'), true);
            
            // Chercher l'ID dans le JSON ou dans POST
            $notifId = null;
            if ($jsonData && isset($jsonData['notif_id'])) {
                $notifId = $jsonData['notif_id'];
            } elseif (isset($_POST['notif_id'])) {
                $notifId = $_POST['notif_id'];
            }
            
            if ($notifId) {
                $notifModel = new NotificationModel();
                $result = $notifModel->markAsRead($notifId);
                
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Mise à jour échouée']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID de notification manquant']);
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour de la notification: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    // Méthode pour récupérer toutes les notifications non lues de l'utilisateur
    public function getUserNotifications() {
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            // Assurez-vous que $_SESSION['user_id'] est défini
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("L'utilisateur n'est pas connecté.");
            }
    
            // Récupération des notifications
            $currentNotifications = $this->notificationModel->getUnreadNotifications($_SESSION['user_id']);
            
            // Définir l'en-tête JSON
            header('Content-Type: application/json');
            
            // Retourner les notifications en JSON
            echo json_encode($currentNotifications);
        } catch (Exception $e) {
            error_log("Erreur dans getUserNotifications : " . $e->getMessage());
            echo json_encode(['error' => 'Une erreur est survenue dans la récupération des notifications']);
        }
    }
    
    // Méthode pour récupérer la dernière notification non lue
    public function getLastUnreadNotification($userId) {
        try {
            $notifications = $this->notificationModel->getUnreadNotifications($userId);

            if (empty($notifications)) {
                return null;
            }

            return $notifications[0];  // Retourner la première notification non lue
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur lors de la récupération de la notification : ' . $e->getMessage()]);
        }
    }

    // Méthode pour envoyer une notification
    public function sendNotification($userId, $type, $content, $eventId = null) {
        try {
            $this->notificationModel->createNotification($userId, $type, $content, $eventId);
            error_log("Notification envoyée : " . $content);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de la notification : " . $e->getMessage());
            echo json_encode(['error' => 'Erreur lors de l\'envoi de la notification : ' . $e->getMessage()]);
        }
    }
    
    // Méthode privée pour récupérer l'ID du membre de la famille pour une notification donnée
    private function getFamilyMemberIdForNotification($notifId) {
        try {
            // Logique pour récupérer l'ID du membre de la famille
            $notification = $this->notificationModel->getNotificationById($notifId);
            return $notification['user_id'];  // Assurez-vous que la table contient cette information
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur lors de la récupération du membre de la famille : ' . $e->getMessage()]);
        }
    }
}
