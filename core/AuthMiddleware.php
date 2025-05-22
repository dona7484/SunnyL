<?php
require_once __DIR__ . '/../../core/JWTManager.php'; // Chemin ajusté si nécessaire

class AuthMiddleware {
    /**
     * Vérifie l'authentification via JWT
     * * @return array [est_authentifié, données_utilisateur]
     */
    public static function authenticate() {
        // Récupérer le token depuis l'en-tête Authorization
        $headers = apache_request_headers();
        // Pour compatibilité avec Nginx ou autres serveurs, vérifier aussi $_SERVER['HTTP_AUTHORIZATION']
        if (empty($headers['Authorization']) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
        }

        $token = null;
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
                $token = $matches[1];
            }
        }
        
        // Si pas de token, vérifier dans les paramètres ou cookies (moins sécurisé pour les API stateless)
        // Il est préférable de s'en tenir à l'en-tête Bearer pour les API.
        // if (!$token && isset($_GET['token'])) {
        //     $token = $_GET['token'];
        // }
        // 
        // if (!$token && isset($_COOKIE['access_token'])) {
        //     $token = $_COOKIE['access_token'];
        // }
        
        if (!$token) {
            return [false, null];
        }
        
        // Valider le token
        $userData = JWTManager::validateToken($token, 'access');
        
        if ($userData === false) { // S'assurer de la comparaison stricte si validateToken peut retourner autre chose que false
            return [false, null];
        }
        
        return [true, $userData];
    }
    
    /**
     * Vérifie si l'utilisateur a le rôle requis
     * * @param object $userData Données utilisateur du token
     * @param string|array $roles Rôle(s) requis
     * @return bool Autorisé ou non
     */
    public static function hasRole($userData, $roles) {
        if (!is_object($userData) || !isset($userData->role)) {
            return false;
        }
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return in_array($userData->role, $roles);
    }
}