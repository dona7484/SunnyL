<?php
require_once __DIR__ . '/../config/database.php';

class PasswordReset {
    /**
     * Crée un nouveau token de réinitialisation pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return string|bool Le token créé ou false en cas d'échec
     */
    public static function createToken($userId) {
        try {
            $db = (new DbConnect())->getConnection();
            
            // Vérifier si l'utilisateur existe
            $checkUser = $db->prepare("SELECT id FROM users WHERE id = ?");
            $checkUser->execute([$userId]);
            if (!$checkUser->fetch()) {
                return false;
            }
            
            // Nettoyer les anciens tokens pour cet utilisateur
            $cleanup = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?");
            $cleanup->execute([$userId]);
            
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            
            // Définir l'expiration (1 heure)
            $expires = date('Y-m-d H:i:s', time() + 3600);
            
            // Enregistrer le token
            $stmt = $db->prepare("INSERT INTO password_reset_tokens (user_id, token, expires) VALUES (?, ?, ?)");
            $result = $stmt->execute([$userId, $token, $expires]);
            
            if ($result) {
                return $token;
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de la création du token de réinitialisation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un token est valide et retourne l'ID de l'utilisateur associé
     * 
     * @param string $token Le token à vérifier
     * @return int|bool L'ID de l'utilisateur ou false si le token est invalide
     */
    public static function validateToken($token) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("
                SELECT user_id FROM password_reset_tokens 
                WHERE token = ? AND expires > NOW() AND used = 0
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['user_id'];
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de la validation du token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marque un token comme utilisé
     * 
     * @param string $token Le token à marquer comme utilisé
     * @return bool Succès ou échec
     */
    public static function markTokenAsUsed($token) {
        try {
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
            return $stmt->execute([$token]);
        } catch (Exception $e) {
            error_log("Erreur lors du marquage du token comme utilisé: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Génère un lien de réinitialisation avec le token
     * 
     * @param string $token Le token à inclure dans le lien
     * @return string L'URL complète de réinitialisation
     */
    public static function getResetLink($token) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $baseUrl .= str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        
        return $baseUrl . "index.php?controller=auth&action=resetPassword&token=" . urlencode($token);
    }
}
?>