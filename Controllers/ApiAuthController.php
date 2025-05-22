<?php
require_once __DIR__ . '/../core/JWTManager.php';
require_once __DIR__ . '/../models/User.php';

class ApiAuthController {
    /**
     * Authentification via email/mot de passe
     */
   public function login() {
        // Définir le header Content-Type
        header('Content-Type: application/json');
        
        // Récupérer les données JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Valider les données
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email et mot de passe requis']);
            return;
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // Connexion à la base de données
        $db = new DbConnect();
        $connection = $db->getConnection();
        
        // Préparation et exécution de la requête pour trouver l'utilisateur
        $query = $connection->prepare("SELECT * FROM users WHERE email = :email");
        $query->execute(['email' => $email]);
        $query->setFetchMode(PDO::FETCH_OBJ);
        $user = $query->fetch();
        
        // Vérification des identifiants
        if ($user && password_verify($password, $user->password)) {
            // Générer les tokens JWT
            $tokens = JWTManager::generateTokens($user->id, $user->role);
            
            // Répondre avec les tokens
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in']
            ]);
        } else {
            // Échec de connexion
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants incorrects']);
        }
    }
    /**
     * Rafraîchissement du token d'accès
     */
    public function refresh() {
        // Récupérer les données JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Valider les données
        if (!isset($data['refresh_token'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Refresh token requis']);
            return;
        }
        
        $refreshToken = $data['refresh_token'];
        
        // Rafraîchir le token
        $newTokens = JWTManager::refreshAccessToken($refreshToken);
        
        if (!$newTokens) {
            http_response_code(401);
            echo json_encode(['error' => 'Refresh token invalide ou expiré']);
            return;
        }
        
        // Stocker le nouveau token en cookie sécurisé (optionnel)
        setcookie('access_token', $newTokens['access_token'], [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Répondre avec le nouveau token
        echo json_encode([
            'success' => true,
            'access_token' => $newTokens['access_token'],
            'expires_in' => $newTokens['expires_in']
        ]);
    }
    
    /**
     * Déconnexion (révocation du refresh token)
     */
    public function logout() {
        // Récupérer les données JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Valider les données
        if (!isset($data['refresh_token'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Refresh token requis']);
            return;
        }
        
        $refreshToken = $data['refresh_token'];
        
        // Révoquer le token
        $revoked = JWTManager::revokeRefreshToken($refreshToken);
        
        // Supprimer le cookie d'accès
        setcookie('access_token', '', time() - 3600, '/');
        
        // Répondre avec succès même si le token était déjà révoqué ou invalide
        echo json_encode(['success' => true]);
    }
}