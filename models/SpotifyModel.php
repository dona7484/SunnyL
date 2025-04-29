<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/spotify.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class SpotifyModel {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct() {
        $this->db = (new DbConnect())->getConnection();
        $this->clientId = SPOTIFY_CLIENT_ID;
        $this->clientSecret = SPOTIFY_CLIENT_SECRET;
        $this->redirectUri = SPOTIFY_REDIRECT_URI;
    }

    public function getAuthUrl($state) {
        return 'https://accounts.spotify.com/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => 'user-read-playback-state user-modify-playback-state user-read-private'
        ]);
    }

    public function handleAuthCallback($code) {
        $tokens = $this->getTokens($code);
        
        if(isset($tokens['access_token'])) {
            return $tokens; // Ne plus stocker directement ici
        }
        
        throw new Exception('Échec de l\'authentification');
    }
    
    
    
    private function getTokens($code) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://accounts.spotify.com/api/token',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ])
        ]);
        
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    private function storeTokens($userId, $tokens) {
        $stmt = $this->db->prepare("REPLACE INTO spotify_tokens 
            (user_id, access_token, refresh_token, expires_at) 
            VALUES (?, ?, ?, ?)");
        
        $stmt->execute([
            $userId,
            $tokens['access_token'],
            $tokens['refresh_token'],
            date('Y-m-d H:i:s', time() + $tokens['expires_in'])
        ]);
    }

    public function getPlaybackState() {
        return $this->apiRequest('GET', 'me/player');
    }

    private function apiRequest($method, $endpoint, $params = []) {
        $ch = curl_init();
        
        if ($ch === false) {
            throw new Exception('Échec de l\'initialisation cURL');
        }
    
        $options = [
            CURLOPT_URL => 'https://api.spotify.com/v1/' . $endpoint,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->getValidToken(),
                'Content-Type: application/json'
            ],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true // Active le rapport d'erreurs HTTP
        ];
    
        if(!empty($params)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        }
    
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch); // Fermeture explicite importante
    
        if ($error) {
            throw new Exception("Erreur cURL ($httpCode): $error");
        }
    
        return json_decode($response, true);
    }
    
    protected function getValidToken() {
        // Vérifier si le token existe et n'est pas expiré
        if (isset($_SESSION['spotify_token']) && 
            isset($_SESSION['spotify_token_expires']) && 
            $_SESSION['spotify_token_expires'] > time()) {
            return $_SESSION['spotify_token'];
        }
        
        // Si un refresh_token existe, essayer de rafraîchir le token
        if (isset($_SESSION['spotify_refresh_token'])) {
            try {
                $this->refreshAccessToken($_SESSION['spotify_refresh_token']);
                return $_SESSION['spotify_token'];
            } catch (Exception $e) {
                // Journaliser l'erreur mais continuer
                error_log("Erreur lors du rafraîchissement du token: " . $e->getMessage());
            }
        }
        
        // Rediriger vers l'authentification si aucun token valide n'est disponible
        // Au lieu de lancer une exception, stockez un message d'erreur dans la session
        $_SESSION['spotify_error'] = "Veuillez vous reconnecter via le bouton \"Musique\"";
        return null;
    }
    
    
    private function refreshToken() {
        $refreshToken = $_SESSION['spotify_refresh_token'] ?? null;
        if (!$refreshToken) {
            throw new Exception('Refresh token manquant');
        }
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Échec du rafraîchissement du token (HTTP $httpCode)");
        }
        
        $tokenData = json_decode($response, true);
        $_SESSION['spotify_access_token'] = $tokenData['access_token'];
        $_SESSION['spotify_token_expiry'] = time() + $tokenData['expires_in'];
    }
    
    private function makeTokenRequest($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ]);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode !== 200) {
            throw new Exception("Erreur lors de la requête de token (HTTP $httpCode)");
        }
    
        return json_decode($response, true);
    }
    
    private function updateSessionWithNewTokens($tokenData) {
        $_SESSION['spotify_access_token'] = $tokenData['access_token'];
        $_SESSION['spotify_token_expiry'] = time() + $tokenData['expires_in'];
        if (isset($tokenData['refresh_token'])) {
            $_SESSION['spotify_refresh_token'] = $tokenData['refresh_token'];
        }
    }
    
    public function controlPlayback($action, $value = null) {
        switch($action) {
            case 'play':
                return $this->apiRequest('PUT', 'me/player/play');
            case 'pause':
                return $this->apiRequest('PUT', 'me/player/pause');
            case 'next':
                return $this->apiRequest('POST', 'me/player/next');
            case 'previous':
                return $this->apiRequest('POST', 'me/player/previous');
            case 'volume':
                if ($value === null || !is_numeric($value)) {
                    throw new Exception('Valeur de volume invalide');
                }
                return $this->apiRequest('PUT', 'me/player/volume', ['volume_percent' => intval($value)]);
            default:
                throw new Exception('Action non supportée');
        }
    }
    
private function refreshAccessToken($refreshToken) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://accounts.spotify.com/api/token',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("Échec du rafraîchissement du token (HTTP $httpCode)");
    }
    
    return json_decode($response, true);
}
}
?>
