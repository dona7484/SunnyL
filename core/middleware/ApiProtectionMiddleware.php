<?php
require_once __DIR__ . '/AuthMiddleware.php'; // Chemin ajusté si nécessaire

class ApiProtectionMiddleware {
    /**
     * Middleware pour protéger une route API
     * * @param array $allowedRoles Rôles autorisés (vide = tous les utilisateurs authentifiés)
     * @return callable Fonction middleware qui retourne les données utilisateur ou gère la réponse d'erreur et exit.
     */
    public static function protect($allowedRoles = []) {
        return function() use ($allowedRoles) {
            // Authentifier l'utilisateur
            list($authenticated, $userData) = AuthMiddleware::authenticate();
            
            if (!$authenticated) {
                http_response_code(401);
                echo json_encode(['error' => 'Non authentifié', 'message' => 'Token manquant ou invalide.']);
                exit;
            }
            
            // Si des rôles spécifiques sont requis, les vérifier
            if (!empty($allowedRoles) && !AuthMiddleware::hasRole($userData, $allowedRoles)) {
                http_response_code(403);
                echo json_encode(['error' => 'Accès non autorisé', 'message' => 'Rôle insuffisant.']);
                exit;
            }
            
            // Rendre les données utilisateur disponibles pour le handler de la route
            return $userData;
        };
    }
}