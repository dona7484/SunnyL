<?php
require_once __DIR__ . '/../config/database.php';

class Activity {
    /**
     * Récupère les activités récentes pour un utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @param int $limit Nombre maximum d'activités à récupérer
     * @return array Liste des activités récentes
     */
    public static function getRecentActivities($userId, $limit = 5) {
        try {
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            
            // Récupérer les différents types d'activités et les fusionner
            
            // 1. Messages récents
            $messageQuery = "
                SELECT 
                    'message' as type,
                    CASE 
                        WHEN m.sender_id = :user_id THEN CONCAT('Message envoyé à ', u.name)
                        ELSE CONCAT('Message reçu de ', u.name)
                    END as content,
                    m.created_at
                FROM messages m
                JOIN users u ON (m.sender_id = u.id AND m.receiver_id = :user_id) OR (m.receiver_id = u.id AND m.sender_id = :user_id)
                WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                ORDER BY m.created_at DESC
                LIMIT :limit
            ";
            
            // 2. Photos récentes
            $photoQuery = "
                SELECT 
                    'photo' as type,
                    CASE 
                        WHEN p.sender_id = :user_id THEN CONCAT('Photo envoyée à ', u.name)
                        ELSE CONCAT('Photo reçue de ', u.name) 
                    END as content,
                    p.created_at
                FROM photos p
                JOIN users u ON (p.sender_id = u.id AND p.user_id = :user_id) OR (p.user_id = u.id AND p.sender_id = :user_id)
                WHERE p.sender_id = :user_id OR p.user_id = :user_id
                ORDER BY p.created_at DESC
                LIMIT :limit
            ";
            
            // 3. Événements récents
            $eventQuery = "
                SELECT 
                    'event' as type,
                    CONCAT('Événement créé: ', e.title) as content,
                    e.date as created_at
                FROM events e
                WHERE e.user_id = :user_id
                ORDER BY e.date DESC
                LIMIT :limit
            ";
            
            // 4. Notifications récentes
            $notifQuery = "
                SELECT 
                    type,
                    content,
                    created_at
                FROM notifications 
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit
            ";
            
            // Exécuter chaque requête
            $stmt1 = $db->prepare($messageQuery);
            $stmt1->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt1->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt1->execute();
            $messages = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt2 = $db->prepare($photoQuery);
            $stmt2->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt2->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt2->execute();
            $photos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt3 = $db->prepare($eventQuery);
            $stmt3->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt3->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt3->execute();
            $events = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt4 = $db->prepare($notifQuery);
            $stmt4->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt4->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt4->execute();
            $notifications = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            
            // Fusionner tous les résultats
            $allActivities = array_merge($messages, $photos, $events, $notifications);
            
            // Trier par date la plus récente
            usort($allActivities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Limiter le nombre de résultats
            return array_slice($allActivities, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Erreur dans Activity::getRecentActivities: " . $e->getMessage());
            return [];
        }
    }
}