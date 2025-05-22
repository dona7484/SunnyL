<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php'; // [cite: 268]

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException; // Important pour capturer les tokens expirés

class JWTManager {
    // Clé secrète pour signer les tokens (à stocker idéalement dans une variable d'environnement)
    private static $secretKey = 'sunnylink_jwt_secret_key_2023'; // TODO: Move to environment variable
    private static $algorithm = 'HS256';

    // Durées de validité
    private static $accessTokenExpiry = 3600; // 1 heure
    private static $refreshTokenExpiry = 604800; // 7 jours

    /**
     * Génère un access token et un refresh token pour un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $role Rôle de l'utilisateur (senior, famille)
     * @return array Tableau contenant les tokens et leur durée de validité
     */
    public static function generateTokens($userId, $role) {
        $issuedAt = time();

        // Access Token payload
        $accessTokenPayload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + self::$accessTokenExpiry,
            'user_id' => $userId,
            'role' => $role,
            'type' => 'access'
        ];

        // Refresh Token payload
        $tokenId = bin2hex(random_bytes(32)); // Identifiant unique
        $refreshTokenPayload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + self::$refreshTokenExpiry,
            'user_id' => $userId,
            'type' => 'refresh',
            'jti' => $tokenId // JWT ID unique
        ];

        // Générer les tokens
        $accessToken = JWT::encode($accessTokenPayload, self::$secretKey, self::$algorithm);
        $refreshToken = JWT::encode($refreshTokenPayload, self::$secretKey, self::$algorithm);

        // Stocker le refresh token en base
        self::storeRefreshToken($tokenId, $userId, $issuedAt + self::$refreshTokenExpiry);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => self::$accessTokenExpiry
        ];
    }

    /**
     * Valide un token JWT
     *
     * @param string $token Token JWT à valider
     * @param string $type Type de token ('access' ou 'refresh')
     * @return object|false Données décodées du token ou false si invalide
     */
    public static function validateToken($token, $type = 'access') {
        if (empty($token)) {
            error_log('JWT validation error: Token is empty.');
            return false;
        }
        try {
            // La clé doit être encapsulée dans un objet Key
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));

            // Vérifier si le décodage a produit un objet
            if (!is_object($decoded)) {
                error_log('JWT validation error: Decoding did not return an object.');
                return false;
            }

            // Vérifier le type de token
            if (!isset($decoded->type) || $decoded->type !== $type) {
                error_log('JWT validation error: Token type mismatch or type not set. Expected: ' . $type . ', Got: ' . ($decoded->type ?? 'N/A'));
                return false;
            }

            // Pour les refresh tokens, vérifier la validité en base
            if ($type === 'refresh') {
                if (!isset($decoded->jti) || !isset($decoded->user_id)) {
                    error_log('JWT (refresh) validation error: jti or user_id missing in decoded token.');
                    return false;
                }
                if (!self::isRefreshTokenValid($decoded->jti, $decoded->user_id)) {
                    error_log('JWT (refresh) validation error: Token not valid in DB (token_id: ' . $decoded->jti . ', user_id: ' . $decoded->user_id . ').');
                    return false;
                }
            }
            
            return $decoded; // Retourne l'objet décodé

        } catch (ExpiredException $e) {
            error_log('JWT validation error: Token has expired. Message: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('JWT validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Stocke un refresh token dans la base de données
     *
     * @param string $tokenId Identifiant unique du token
     * @param int $userId ID de l'utilisateur
     * @param int $expiry Timestamp d'expiration
     * @return bool Succès de l'opération
     */
    private static function storeRefreshToken($tokenId, $userId, $expiry) {
        try {
            $db = (new DbConnect())->getConnection(); 
            // Révoquer les anciens tokens pour cet utilisateur avant d'en insérer un nouveau
            $cleanupStmt = $db->prepare("UPDATE refresh_tokens SET is_revoked = 1 WHERE user_id = ?");
            $cleanupStmt->execute([$userId]);

            $stmt = $db->prepare("INSERT INTO refresh_tokens (token_id, user_id, expires_at, is_revoked) VALUES (?, ?, ?, 0)");
            return $stmt->execute([$tokenId, $userId, date('Y-m-d H:i:s', $expiry)]);
        } catch (\Exception $e) {
            error_log('Error storing refresh token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un refresh token est valide et non révoqué en base
     *
     * @param string $tokenId Identifiant du token
     * @param int $userId ID de l'utilisateur
     * @return bool Token valide ou non
     */
    private static function isRefreshTokenValid($tokenId, $userId) {
        try {
            $db = (new DbConnect())->getConnection(); // [cite: 268]
            $stmt = $db->prepare("SELECT 1 FROM refresh_tokens WHERE token_id = ? AND user_id = ? AND expires_at > NOW() AND is_revoked = 0");
            $stmt->execute([$tokenId, $userId]);
            return $stmt->fetchColumn() !== false; // fetchColumn retourne false si aucune ligne, ou la valeur de la colonne.
        } catch (\Exception $e) {
            error_log('Error checking refresh token validity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rafraîchit un access token à partir d'un refresh token
     *
     * @param string $refreshToken Refresh token
     * @return array|false Nouveau access token ou false si invalide
     */
    public static function refreshAccessToken($refreshToken) {
        $decoded = self::validateToken($refreshToken, 'refresh');

        if (!$decoded) { // $decoded sera un objet ou false
            error_log('Refresh token validation failed or token is invalid.');
            return false;
        }

        // À ce point, $decoded doit être un objet. La vérification suivante est une double sécurité.
        if (!is_object($decoded) || !isset($decoded->user_id)) {
            error_log('Invalid decoded refresh token structure: ' . print_r($decoded, true));
            return false;
        }
            
        try {
            $db = (new DbConnect())->getConnection(); // [cite: 268]
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$decoded->user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log('User not found for refresh token. User ID: ' . $decoded->user_id);
                return false;
            }
            
            // Générer un nouveau access token
            $issuedAt = time();
            $accessTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + self::$accessTokenExpiry,
                'user_id' => $decoded->user_id,
                'role' => $user['role'],
                'type' => 'access'
            ];
            
            $newAccessToken = JWT::encode($accessTokenPayload, self::$secretKey, self::$algorithm);
            
            return [
                'access_token' => $newAccessToken,
                'expires_in' => self::$accessTokenExpiry
            ];
        } catch (\Exception $e) {
            error_log('Error refreshing access token: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Révoque un refresh token (par exemple lors du logout)
     *
     * @param string $refreshToken Refresh token à révoquer
     * @return bool Succès de l'opération
     */
    public static function revokeRefreshToken($refreshToken) {
        try {
            // D'abord, valider la structure du token et s'il n'est pas expiré (sans vérifier la DB pour la validité, car on veut le révoquer)
            $decoded = JWT::decode($refreshToken, new Key(self::$secretKey, self::$algorithm));

            if (!is_object($decoded) || !isset($decoded->type) || $decoded->type !== 'refresh' || !isset($decoded->jti)) {
                error_log('Cannot revoke token: Invalid token structure or type.');
                return false; // Pas un refresh token valide ou jti manquant
            }
            
            $db = (new DbConnect())->getConnection();
            $stmt = $db->prepare("UPDATE refresh_tokens SET is_revoked = 1 WHERE token_id = ?");
            $success = $stmt->execute([$decoded->jti]);
            if ($success) {
                error_log("Refresh token revoked successfully: " . $decoded->jti);
            } else {
                error_log("Failed to revoke refresh token in DB: " . $decoded->jti);
            }
            return $success;

        } catch (ExpiredException $e) {
            // Le token est déjà expiré, on peut considérer la révocation comme "réussie" car il n'est plus utilisable.
            error_log('Attempted to revoke an already expired refresh token: ' . $e->getMessage());
            // On pourrait vouloir supprimer les tokens expirés de la DB ici, mais la révocation est le but principal.
            // Pour simplifier, on retourne true car le token n'est plus valide.
            return true; 
        } catch (\Exception $e) {
            error_log('Error revoking refresh token: ' . $e->getMessage());
            return false;
        }
    }
}