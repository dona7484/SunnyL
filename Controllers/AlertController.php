<?php
/**
 * Contrôleur de gestion des alertes
 * 
 * Ce fichier contient la classe AlertController qui permet de vérifier
 * si des alertes ou notifications doivent être affichées à l'utilisateur.
 */

// Inclusion des dépendances nécessaires
require_once __DIR__ . '/../models/EventModel.php';  // Modèle pour gérer les événements
require_once __DIR__ . '/../models/Notification.php'; // Modèle pour gérer les notifications

/**
 * Classe AlertController
 * 
 * Gère les alertes et notifications à afficher aux utilisateurs
 * en temps réel à travers une API JSON.
 */
class AlertController {
    /**
     * Vérifie s'il y a des alertes ou notifications à afficher
     * 
     * Cette méthode est appelée régulièrement par le frontend via AJAX
     * pour déterminer si une notification doit être affichée à l'utilisateur.
     * Elle vérifie à la fois les alertes d'événements et les autres types de notifications.
     * 
     * @return void Envoie une réponse JSON indiquant s'il y a une alerte à afficher
     */
    public function check() {
        // Définit l'en-tête de la réponse comme étant du JSON
        header('Content-Type: application/json');

        // Démarrage de la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupération de l'ID de l'utilisateur depuis la session
        $userId = $_SESSION['user_id'];
        
        // Date et heure actuelles pour comparer avec les alertes programmées
        $now = date('Y-m-d H:i:s');

        // Instanciation du modèle d'événements
        $model = new EventModel();
        
        // Vérification des alertes d'événements programmées pour maintenant
        $alert = $model->getAlertForTime($userId, $now);

        // Si une alerte d'événement est trouvée
        if ($alert) {
            // Marquer l'alerte comme déclenchée pour éviter de l'afficher à nouveau
            $model->markAlertAsTriggered($alert['id']);
            
            // Retourner les informations sur l'alerte au format JSON
            echo json_encode([
                'should_alert' => true,   // Indique qu'une alerte doit être affichée
                'type' => 'event',        // Type d'alerte : événement
                'message' => $alert['notification_message'], // Message à afficher
                'id' => $alert['id']      // ID de l'événement pour référence
            ]);
            return; // Termine la fonction
        }

        // 🔔 Vérification d'autres types de notifications (photo, message) si aucun événement
        $notifs = Notification::getUnseenByUser($userId);
        
        // Si des notifications non vues sont trouvées
        if (!empty($notifs)) {
            // Prendre la première notification de la liste
            $notif = $notifs[0];
            
            // Marquer cette notification comme vue pour éviter de l'afficher à nouveau
            Notification::markAsSeen($notif['id']);
            
            // Retourner les informations sur la notification au format JSON
            echo json_encode([
                'should_alert' => true,    // Indique qu'une alerte doit être affichée
                'type' => $notif['type'],  // Type de notification (photo, message, etc.)
                'message' => $notif['content'], // Contenu de la notification
                'id' => $notif['id']       // ID de la notification pour référence
            ]);
            return; // Termine la fonction
        }

        // Si aucune alerte ou notification n'est trouvée, retourner false
        echo json_encode(['should_alert' => false]);
    }
}