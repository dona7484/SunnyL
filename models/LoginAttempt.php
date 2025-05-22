<?php
require_once __DIR__ . '/../config/database.php';

class LoginAttempt {
    /**
     * Enregistre une tentative de connexion
     * 
     * @param int $userId ID de l'utilisateur
     * @param string|null $ipAddress Adresse IP (facultatif)
     * @return bool Succès ou échec
     */
    public static function recordAttempt($userId, $ipAddress = null) {
        try {
            if ($ipAddress === null) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            }
            
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("INSERT INTO login_attempts (user_id, ip_address) VALUES (?, ?)");
            return $stmt->execute([$userId, $ipAddress]);
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement de la tentative de connexion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Compte les tentatives récentes pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $minutes Période en minutes
     * @return int Nombre de tentatives récentes
     */
    public static function getRecentAttempts($userId, $minutes = 30) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM login_attempts 
                WHERE user_id = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt->execute([$userId, $minutes]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors du comptage des tentatives de connexion: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Vérifie si un utilisateur est temporairement bloqué
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $maxAttempts Nombre maximum de tentatives autorisées
     * @param int $lockoutTime Période de blocage en minutes
     * @return bool True si l'utilisateur est bloqué
     */
    public static function isLocked($userId, $maxAttempts = 5, $lockoutTime = 30) {
        return self::getRecentAttempts($userId, $lockoutTime) >= $maxAttempts;
    }
    
    /**
     * Compte les tentatives récentes pour une adresse IP
     * 
     * @param string|null $ipAddress Adresse IP
     * @param int $minutes Période en minutes
     * @return int Nombre de tentatives récentes
     */
    public static function getRecentIPAttempts($ipAddress = null, $minutes = 30) {
        try {
            if ($ipAddress === null) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            }
            
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM login_attempts 
                WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt->execute([$ipAddress, $minutes]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors du comptage des tentatives IP: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Vérifie si une adresse IP est temporairement bloquée
     * 
     * @param string|null $ipAddress Adresse IP
     * @param int $maxAttempts Nombre maximum de tentatives autorisées
     * @param int $lockoutTime Période de blocage en minutes
     * @return bool True si l'adresse IP est bloquée
     */
    public static function isIPLocked($ipAddress = null, $maxAttempts = 10, $lockoutTime = 30) {
        return self::getRecentIPAttempts($ipAddress, $lockoutTime) >= $maxAttempts;
    }
    
    /**
     * Nettoie les anciennes tentatives
     * 
     * @param int $days Nombre de jours à conserver
     * @return bool Succès ou échec
     */
    public static function cleanupOldAttempts($days = 7) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? DAY)");
            return $stmt->execute([$days]);
        } catch (Exception $e) {
            error_log("Erreur lors du nettoyage des tentatives: " . $e->getMessage());
            return false;
        }
    }
}
?>