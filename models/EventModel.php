<?php
require_once __DIR__ . '/../Entities/Event.php';
require_once __DIR__ . '/../config/database.php';

// Dans models/EventModel.php
class EventModel extends DbConnect {
    protected $request;
    public function findAll() {
        $sql = "SELECT * FROM events";
        $stmt = $this->connection->query($sql); // $this->connection est maintenant disponible
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Event');
    }
    
    public function __set($name, $value) {
        // Mapper les propriétés de la base de données aux propriétés de la classe
        if ($name === 'user_id') {
            $this->userId = $value;
        } else {
            $this->$name = $value;
        }
    }
    
    public function find($id) {
        // Vérifier si l'ID est valide
        if (!is_numeric($id)) {
            return false;
        }
    
        // Exécution de la requête SQL pour trouver l'événement
        $this->request = $this->connection->prepare("SELECT * FROM events WHERE id = :id");
        $this->request->bindParam(":id", $id, PDO::PARAM_INT);
        $this->request->execute();
    
        // Retourner l'événement ou false si non trouvé
        $event = $this->request->fetchObject('Event');
        return $event ?: false;  // Retourne l'objet Event ou false si non trouvé
    }
    
    // Méthode de création d'un événement
        public function create(Event $event) {
            $this->request = $this->connection->prepare("INSERT INTO events (title, description, date, lieu, user_id, alert_time, notification_message, recurrence) VALUES (:title, :description, :date, :lieu, :user_id, :alert_time, :notification_message, :recurrence)");
            $this->request->bindValue(":title", $event->getTitle());
            $this->request->bindValue(":description", $event->getDescription());
            $this->request->bindValue(":date", $event->getDate());
            $this->request->bindValue(":lieu", $event->getLieu());
            $this->request->bindValue(":user_id", $event->getUserId());
            $this->request->bindValue(":alert_time", $event->getAlertTime());
            $this->request->bindValue(":notification_message", $event->getNotificationMessage());
            $this->request->bindValue(":recurrence", $event->getRecurrence());
            $this->executeTryCatch();
            
            // Récupérer l'ID de l'événement créé
            $eventId = $this->connection->lastInsertId();
            
            // Définir l'ID dans l'objet Event
            $event->setId($eventId);
            
            return $eventId;
        }
        
        public function markAsRead($eventId) {
            $sql = "UPDATE events SET is_read = 1 WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $eventId]);
        }
        
    // Méthode pour récupérer tous les événements associés à un utilisateur (organisateur ou participant)
    public function findEventsForUser($userId) {
        // Requête pour récupérer les événements où l'utilisateur est l'organisateur ou un participant
        $this->request = $this->connection->prepare("
            SELECT * FROM events 
            WHERE user_id = :user_id 
            OR id IN (
                SELECT event_id FROM participants WHERE user_id = :user_id
            )
        ");
        $this->request->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $this->request->execute();
        
        // Retourner tous les événements associés à l'utilisateur
        return $this->request->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
 * Récupère les événements à venir pour un utilisateur spécifique
 * @param int $userId ID de l'utilisateur
 * @return array Liste des événements à venir
 */
public function getUpcomingEventsForUser($userId) {
    $sql = "SELECT id, title, description, date, lieu 
            FROM events 
            WHERE user_id = :user_id AND date >= NOW() 
            ORDER BY date ASC 
            LIMIT 5";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les événements à venir pour plusieurs utilisateurs
 * @param array $userIds Liste des IDs d'utilisateurs
 * @return array Liste des événements à venir
 */
public function getUpcomingEventsForUsers($userIds) {
    if (empty($userIds)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    
    $sql = "SELECT id, title, description, date, lieu 
            FROM events 
            WHERE user_id IN ($placeholders) AND date >= NOW() 
            ORDER BY date ASC 
            LIMIT 5";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->execute($userIds);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    public function update($id, Event $event) {
        $this->request = $this->connection->prepare("UPDATE events SET title = :title, description = :description, date = :date, lieu = :lieu WHERE id = :id");
        $this->request->bindValue(":id", $id);
        $this->request->bindValue(":title", $event->getTitle());
        $this->request->bindValue(":description", $event->getDescription());
        $this->request->bindValue(":date", $event->getDate());
        $this->request->bindValue(":lieu", $event->getLieu());
        $this->executeTryCatch();
    }
    

    public function createRecurringEvent(Event $event) {
        $recurrence = $event->getRecurrence();  // daily, weekly, monthly
        $currentDate = $event->getDate();  // Date de départ de l'événement
    
        $interval = null;
        switch ($recurrence) {
            case 'daily':
                $interval = '1 DAY';
                break;
            case 'weekly':
                $interval = '1 WEEK';
                break;
            case 'monthly':
                $interval = '1 MONTH';
                break;
            default:
                $interval = '0';  // Pas de récurrence
        }
    
        // Création d'un nouvel événement à chaque intervalle
        if ($interval !== '0') {
            // Créer un événement pour la récurrence
            for ($i = 0; $i < 10; $i++) {  // Créer 10 occurrences (tu peux ajuster le nombre)
                $nextDate = date('Y-m-d H:i:s', strtotime($currentDate . " + $interval"));
                $event->setDate($nextDate);
    
                $this->create($event);  // Crée l'événement récurrent
                $currentDate = $nextDate;  // Met à jour la date pour le prochain événement
            }
        } else {
            $this->create($event);  // Crée l'événement une seule fois si pas de récurrence
        }
    }
    
    public function delete($id) {
        $this->request = $this->connection->prepare("DELETE FROM events WHERE id = :id");
        $this->request->bindParam(":id", $id);
        $this->executeTryCatch();
    }

    private function executeTryCatch() {
        try {
            $this->request->execute();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
        $this->request->closeCursor();
    }

    public function getAlertForTime($userId, $now) {
        $sql = "SELECT * FROM events 
                WHERE user_id = :user_id 
                AND alert_time <= :now 
                AND is_triggered = 0
                ORDER BY alert_time ASC
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':now' => $now
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function markAlertAsTriggered($eventId) {
        // Récupérer l'événement par son ID
        $event = $this->find($eventId);
    
        // Vérifier si l'événement existe (si $event est un objet Event et non false)
        if ($event !== false) {
            $event->setIsTriggered(1);  // Marquer l'événement comme "alerté"
    
            // Mettre à jour dans la base de données
            $sql = "UPDATE events SET is_triggered = :is_triggered WHERE id = :event_id";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':is_triggered' => $event->getIsTriggered(),
                ':event_id' => $eventId
            ]);
        } else {
            // Si l'événement n'a pas été trouvé, gérer l'erreur
            throw new Exception("L'événement avec l'ID $eventId n'a pas été trouvé.");
        }
    }
    
    public function getUpcomingAlerts($userId) {
        $sql = "SELECT * FROM events WHERE user_id = :user_id AND alert_time <= NOW() AND is_triggered = 0 ORDER BY alert_time ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotificationsByEventId($eventId) {
        $sql = "SELECT * FROM notifications WHERE event_id = :event_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);
    
        // Retourne les notifications sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function markEventAsRead($eventId) {
        $sql = "UPDATE events SET is_read = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $eventId]);
    }
    
    public function getParticipantsByEventId($eventId) {
        // Exemple de requête pour récupérer les participants d'un événement
        $sql = "SELECT participant_name FROM participants WHERE event_id = :event_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);

        // Retourne les participants sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
