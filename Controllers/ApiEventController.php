<?php
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../Entities/Event.php'; // Nécessaire si EventModel::create attend un objet Event

class ApiEventController {
    /**
     * Récupère les événements pour l'utilisateur authentifié
     */
    public function getEvents($userData) { // $userData est l'objet décodé du token
        $eventModel = new EventModel();
        $events = $eventModel->getUpcomingEventsForUser($userData->user_id); // Utiliser user_id de $userData

        // Réponse JSON (déjà gérée par le Router si Content-Type est défini globalement)
        // header('Content-Type: application/json'); // Peut être défini globalement dans api.php
        echo json_encode([
            'success' => true,
            'events' => $events
        ]);
    }
    
    /**
     * Crée un nouvel événement
     */
    public function createEvent($userData) { // $userData est l'objet décodé du token
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données JSON invalides']);
            return;
        }

        if (!isset($data['title']) || !isset($data['date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Titre et date requis']);
            return;
        }
        
        $event = new Event(); // [cite: 57, 207]
        $event->setTitle($data['title']); // [cite: 58, 208]
        $event->setDescription($data['description'] ?? ''); // [cite: 58, 208]
        $event->setDate($data['date']); // [cite: 58, 208]
        $event->setLieu($data['lieu'] ?? ''); // [cite: 62, 210]
        $event->setUserId($userData->user_id); // Utiliser user_id de $userData // [cite: 62]
        
        // Gérer alert_time (assurez-vous que le format est correct pour la DB)
        $alertTime = null;
        if (isset($data['alert_time']) && !empty($data['alert_time'])) {
            // Supposons que alert_time est un timestamp ou une date Y-m-d H:i:s
            // Si c'est un délai comme '1h', '30m', il faut le calculer par rapport à $data['date']
            $eventTimestamp = strtotime($data['date']);
            if ($eventTimestamp !== false) {
                $delayInSeconds = match($data['alert_time']) {
                    '1h' => 3600,
                    '30m' => 1800,
                    '15m' => 900,
                    default => 0
                };
                if ($delayInSeconds > 0) {
                    $alertTimestamp = $eventTimestamp - $delayInSeconds;
                    $alertTime = date('Y-m-d H:i:s', $alertTimestamp);
                }
            }
        }
        $event->setAlertTime($alertTime); // [cite: 62, 210]
        
        $event->setNotificationMessage($data['notification_message'] ?? 'Nouvel événement'); // [cite: 62, 210]
        $event->setRecurrence($data['recurrence'] ?? 'none'); // [cite: 62, 210]
        
        $eventModel = new EventModel(); // [cite: 57]
        $eventId = $eventModel->create($event); // [cite: 62, 227, 228]
        
        // header('Content-Type: application/json'); // Peut être défini globalement dans api.php
        if ($eventId) {
            // Envoyer des notifications aux seniors liés si c'est un membre de la famille qui crée
            if ($userData->role === 'famille' || $userData->role === 'familymember') { // [cite: 39]
                require_once __DIR__ . '/../models/SeniorModel.php'; // [cite: 57]
                require_once __DIR__ . '/NotificationController.php'; // [cite: 57]
                $seniorModel = new SeniorModel(); // [cite: 59]
                $seniors = $seniorModel->getSeniorsForFamilyMember($userData->user_id); // [cite: 59, 298]
                
                if (!empty($seniors)) {
                    $notifController = new NotificationController(); // [cite: 59]
                    foreach ($seniors as $senior) {
                        $notifController->sendNotification( // [cite: 63]
                            $senior['user_id'], // [cite: 63]
                            'event', // [cite: 63]
                            'Nouvel événement : ' . $event->getTitle(), // [cite: 64]
                            $eventId, // [cite: 64]
                            false // [cite: 64]
                        );
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'event_id' => $eventId,
                'message' => 'Événement créé avec succès'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la création de l\'événement'
            ]);
        }
    }
}