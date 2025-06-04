<?php
require_once __DIR__ . '/../config/database.php';

class SpotifyController extends Controller {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct() {
        $this->clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $this->clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
        $this->redirectUri = $_ENV['SPOTIFY_REDIRECT_URI'];
    }
    private $redirectUri = 'https://vps-6ce6c779.vps.ovh.net/SunnyLink/public/index.php?controller=spotify&action=callback';
    
    public function player() {
        // Vérifier si l'utilisateur a un token Spotify valide
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['spotify_token']) || empty($_SESSION['spotify_token'])) {
            // Rediriger vers l'authentification
            $this->authorize();
            return;
        }
        
        // Vérifier si le token est expiré
        if (isset($_SESSION['spotify_token_expires']) && $_SESSION['spotify_token_expires'] < time()) {
            // Rafraîchir le token
            $this->refreshToken();
        }
        
        // Afficher l'interface du lecteur
        $this->render('spotify/player', [
            'token' => $_SESSION['spotify_token']
        ]);
    }
    
    public function authorize() {
        // Générer un état aléatoire pour la sécurité
        $state = bin2hex(random_bytes(16));
        $_SESSION['spotify_state'] = $state;
        
        // Paramètres d'authentification
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => 'user-read-private user-read-email streaming user-library-read user-read-playback-state user-modify-playback-state',
            'show_dialog' => 'true'
        ];
        
        // Rediriger vers l'URL d'autorisation Spotify
        $url = 'https://accounts.spotify.com/authorize?' . http_build_query($params);
        header('Location: ' . $url);
        exit;
    }
    
    public function callback() {
        if (!isset($_GET['code'])) {
            // Erreur d'authentification
            $this->render('error/generic', ['message' => 'Erreur d\'authentification Spotify']);
            return;
        }
        
        // Vérifier l'état pour la sécurité
        if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['spotify_state']) {
            $this->render('error/generic', ['message' => 'État invalide']);
            return;
        }
        
        // Échanger le code contre un token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ]));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        if (isset($response['access_token'])) {
            $_SESSION['spotify_token'] = $response['access_token'];
            $_SESSION['spotify_refresh_token'] = $response['refresh_token'];
            $_SESSION['spotify_token_expires'] = time() + $response['expires_in'];
            
            // Rediriger vers le lecteur
            header('Location: index.php?controller=spotify&action=player');
            exit;
        } else {
            $this->render('error/generic', ['message' => 'Erreur lors de l\'obtention du token Spotify']);
        }
    }
    
    private function refreshToken() {
        if (!isset($_SESSION['spotify_refresh_token'])) {
            $this->authorize();
            return;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $_SESSION['spotify_refresh_token'],
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ]));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        if (isset($response['access_token'])) {
            $_SESSION['spotify_token'] = $response['access_token'];
            $_SESSION['spotify_token_expires'] = time() + $response['expires_in'];
            
            if (isset($response['refresh_token'])) {
                $_SESSION['spotify_refresh_token'] = $response['refresh_token'];
            }
        } else {
            // En cas d'erreur, réautoriser
            $this->authorize();
        }
    }
    
    public function playlists() {
        // Récupérer les playlists de l'utilisateur
        if (!isset($_SESSION['spotify_token'])) {
            $this->authorize();
            return;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.spotify.com/v1/me/playlists');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_SESSION['spotify_token']
        ]);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $playlists = json_decode($result, true);
        
        $this->render('spotify/playlists', [
            'playlists' => $playlists['items'] ?? []
        ]);
    }
}
