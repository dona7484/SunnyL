<?php
/**
 * Contrôleur d'authentification
 * 
 * Gère toutes les fonctionnalités liées à l'authentification des utilisateurs :
 * connexion, déconnexion, inscription et récupération de mot de passe.
 */

// Inclure les dépendances nécessaires
require_once __DIR__ . '/../core/JWTManager.php';

require_once __DIR__ . '/../config/database.php';  // Configuration de la base de données
require_once __DIR__ . '/../models/User.php';      // Modèle utilisateur
require_once __DIR__ . '/Controller.php';

// Modifier la déclaration de classe pour hériter de Controller
class AuthController extends Controller {

public function login() {
    // Si pas de soumission de formulaire, afficher simplement le formulaire
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $this->render('auth/login', []);
        return;
    }
    
    // À partir d'ici, on traite la soumission du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];
    $interface = $_POST['interface'] ?? 'default';

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
        // Initialisation des variables de session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['name'] = $user->name;
        $_SESSION['role'] = $user->role;
        session_regenerate_id(true);

        // Si interface tablette et rôle famille
        if ($interface === 'tablet' && $user->role === 'famille') {
            $seniorId = $this->getSeniorForFamilyMember($user->id);
            if ($seniorId) {
                $_SESSION['user_id'] = $seniorId;
                $_SESSION['role'] = 'senior';
            }
        }
        
        // Générer les tokens JWT
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'];
        $tokens = JWTManager::generateTokens($userId, $userRole);
        
        // Stocker le refresh token dans un cookie HttpOnly
        setcookie('refresh_token', $tokens['refresh_token'], [
            'expires' => time() + 604800, // 7 jours
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
        
        // Option: stocker aussi l'access token pour les APIs du site
        setcookie('access_token', $tokens['access_token'], [
            'expires' => time() + 3600, // 1 heure
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
        
        // Redirection selon le rôle
        if ($_SESSION['role'] === 'senior') {
            header('Location: index.php?controller=home&action=dashboard');
        } else {
            header('Location: index.php?controller=home&action=family_dashboard');
        }
        exit;
    } else {
        // Échec de connexion
        $erreur = "Identifiants incorrects.";
        $this->render('auth/login', ['erreur' => $erreur]);
    }
}
    public function getToken() {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié']);
        return;
    }
    
    $tokens = JWTManager::generateTokens($_SESSION['user_id'], $_SESSION['role']);
    
    // Stocker le refresh token dans un cookie HttpOnly
    setcookie('refresh_token', $tokens['refresh_token'], [
        'expires' => time() + 604800,
        'path' => '/',
        'httponly' => true,
        'secure' => true,
        'samesite' => 'Strict'
    ]);
    
    // Retourner uniquement l'access token
    echo json_encode([
        'access_token' => $tokens['access_token'],
        'expires_in' => $tokens['expires_in']
    ]);
}

/**
 * Endpoint pour rafraîchir un token expiré
 */
public function refreshToken() {
    header('Content-Type: application/json');
    
    $refreshToken = $_COOKIE['refresh_token'] ?? null;
    
    if (!$refreshToken) {
        http_response_code(401);
        echo json_encode(['error' => 'Refresh token manquant']);
        return;
    }
    
    $result = JWTManager::refreshAccessToken($refreshToken);
    
    if (!$result) {
        http_response_code(401);
        echo json_encode(['error' => 'Refresh token invalide']);
        return;
    }
    
    echo json_encode($result);
}

    /**
     * Récupère l'ID du senior associé à un membre de famille
     * 
     * Cette méthode permet de trouver le senior lié à un membre de famille
     * pour la fonctionnalité d'interface tablette
     * 
     * @param int $familyMemberId ID du membre de famille
     * @return int|null ID du senior ou null si non trouvé
     */
    public function getSeniorForFamilyMember($familyMemberId) {
        $query = $this->connection->prepare("SELECT senior_id FROM family_members WHERE family_member_id = :id");
        $query->execute(['id' => $familyMemberId]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['senior_id'] : null;
    }

    /**
     * Méthode d'affichage des vues
     * 
     * @param string $view Chemin de la vue à afficher
     * @param array $data Données à passer à la vue
     */
    public function render($view, $data = [])
    {
        extract($data);  // Extrait les données pour qu'elles soient disponibles dans la vue
        include "../views/{$view}.php";  // Inclusion du fichier de vue
    }

    /**
     * Méthode de déconnexion
     * 
     * Détruit la session et redirige vers la page de connexion
     */
    public function logout() {
    // Si un refresh token est présent, le révoquer
    if (isset($_COOKIE['refresh_token'])) {
        JWTManager::revokeRefreshToken($_COOKIE['refresh_token']);
        
        // Supprimer le cookie
        setcookie('refresh_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    // Supprimer la session (code existant)
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    
    // Redirection
    header('Location: index.php?controller=auth&action=login');
    exit();
}
    /**
     * Méthode d'inscription
     * 
     * Gère l'inscription d'un nouvel utilisateur
     */
    public function register() {
        // Si le formulaire est soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération des données du formulaire
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // IMPORTANT: Changé de 'familymember' à 'famille' pour correspondre à la base de données
            $role = $_POST['role'] ?? 'famille'; 
            
            // Validation des données
            $errors = [];
            if (empty($name)) {
                $errors[] = "Le nom est requis";
            }
            if (empty($email)) {
                $errors[] = "L'email est requis";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis";
            } elseif (strlen($password) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            }
            
            // Vérification si l'email existe déjà
            $user = User::getByEmail($email);
            if ($user) {
                $errors[] = "Cet email est déjà utilisé";
            }
            
            // Si pas d'erreurs, inscrire l'utilisateur
            if (empty($errors)) {
                $result = User::register($name, $email, $password, $role);
                
                if ($result) {
                    // Redirection vers la page de connexion avec un message de succès
                    $_SESSION['success_message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    header('Location: index.php?controller=auth&action=login');
                    exit;
                } else {
                    $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
            
            // S'il y a des erreurs, afficher à nouveau le formulaire avec les erreurs
            $this->render('auth/register', ['errors' => $errors, 'name' => $name, 'email' => $email]);
        } else {
            // Afficher le formulaire d'inscription
            $this->render('auth/register', []);
        }
    }
/**
 * Affiche le formulaire pour demander la réinitialisation du mot de passe
 */
public function forgotPasswordForm() {
    $this->render('auth/forgot_password', [
        'csrf_token' => $this->generateCSRFToken()
    ]);
}

/**
 * Traite la demande de réinitialisation du mot de passe
 */
public function forgotPassword() {
    // Vérifier que la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->forgotPasswordForm();
        return;
    }
    
    // Vérifier le token CSRF
    $this->requireValidCSRFToken($_POST['csrf_token'] ?? '', 'index.php?controller=auth&action=forgotPasswordForm');
    
    // Récupérer l'email
    $email = $_POST['email'] ?? '';
    if (empty($email)) {
        $this->render('auth/forgot_password', [
            'error' => "L'adresse email est requise.",
            'email' => $email,
            'csrf_token' => $this->generateCSRFToken()
        ]);
        return;
    }
    
    // Rechercher l'utilisateur
    $user = User::getByEmail($email);
    if (!$user) {
        // Pour des raisons de sécurité, ne pas indiquer si l'email existe
        // Mais afficher un message de succès même si l'email n'existe pas
        $this->render('auth/forgot_password_success', [
            'message' => "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation."
        ]);
        return;
    }
    
    // Vérifier si l'utilisateur est bloqué pour trop de tentatives
    require_once __DIR__ . '/../models/LoginAttempt.php';
    if (LoginAttempt::isLocked($user['id'])) {
        // Pour des raisons de sécurité, afficher le même message mais ne rien faire
        $this->render('auth/forgot_password_success', [
            'message' => "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation."
        ]);
        return;
    }
    
    // Créer un token de réinitialisation
    require_once __DIR__ . '/../models/PasswordReset.php';
    $token = PasswordReset::createToken($user['id']);
    
    if (!$token) {
        $this->render('auth/forgot_password', [
            'error' => "Une erreur s'est produite lors de la création du lien de réinitialisation.",
            'email' => $email,
            'csrf_token' => $this->generateCSRFToken()
        ]);
        return;
    }
    
    // Générer le lien de réinitialisation
    $resetLink = PasswordReset::getResetLink($token);
    
    // Construire l'email
    $to = $email;
    $subject = "Réinitialisation de votre mot de passe SunnyLink";
    $headers = "From: noreply@sunnylink.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    
    $message = "
    <html>
    <head>
        <title>Réinitialisation de votre mot de passe</title>
    </head>
    <body>
        <h2>Bonjour " . htmlspecialchars($user['name']) . ",</h2>
        <p>Vous avez demandé la réinitialisation de votre mot de passe sur SunnyLink.</p>
        <p>Pour créer un nouveau mot de passe, veuillez cliquer sur le lien ci-dessous :</p>
        <p><a href=\"" . $resetLink . "\">" . $resetLink . "</a></p>
        <p>Ce lien est valable pendant 1 heure.</p>
        <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
        <p>Cordialement,<br>L'équipe SunnyLink</p>
    </body>
    </html>
    ";
    
    // Envoyer l'email (en environnement de production)
    $emailSent = false;
    if (mail($to, $subject, $message, $headers)) {
        $emailSent = true;
    } else {
        // En développement, afficher le lien directement
        $_SESSION['reset_link'] = $resetLink;
    }
    
    // Afficher la page de succès
    $this->render('auth/forgot_password_success', [
        'message' => "Un lien de réinitialisation a été envoyé à votre adresse email.",
        'email' => $email,
        'development_link' => $_SESSION['reset_link'] ?? null // Uniquement pour le développement
    ]);
}

/**
 * Affiche le formulaire de réinitialisation de mot de passe avec le token
 */
public function resetPassword() {
    // Récupérer le token
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        $_SESSION['error_message'] = "Le lien de réinitialisation est invalide.";
        header('Location: index.php?controller=auth&action=forgotPasswordForm');
        exit;
    }
    
    // Vérifier que le token est valide
    require_once __DIR__ . '/../models/PasswordReset.php';
    $userId = PasswordReset::validateToken($token);
    
    if (!$userId) {
        $_SESSION['error_message'] = "Le lien de réinitialisation est invalide ou a expiré.";
        header('Location: index.php?controller=auth&action=forgotPasswordForm');
        exit;
    }
    
    // Afficher le formulaire de réinitialisation
    $this->render('auth/reset_password', [
        'token' => $token,
        'csrf_token' => $this->generateCSRFToken()
    ]);
}

/**
 * Traite la réinitialisation du mot de passe
 */
public function processResetPassword() {
    // Vérifier que la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?controller=auth&action=forgotPasswordForm');
        exit;
    }
    
    // Vérifier le token CSRF
    $this->requireValidCSRFToken($_POST['csrf_token'] ?? '', 'index.php?controller=auth&action=forgotPasswordForm');
    
    // Récupérer les données
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Vérifier que le token est valide
    require_once __DIR__ . '/../models/PasswordReset.php';
    $userId = PasswordReset::validateToken($token);
    
    if (!$userId) {
        $_SESSION['error_message'] = "Le lien de réinitialisation est invalide ou a expiré.";
        header('Location: index.php?controller=auth&action=forgotPasswordForm');
        exit;
    }
    
    // Vérifier que les mots de passe correspondent
    if ($newPassword !== $confirmPassword) {
        $this->render('auth/reset_password', [
            'token' => $token,
            'error' => "Les mots de passe ne correspondent pas.",
            'csrf_token' => $this->generateCSRFToken()
        ]);
        return;
    }
    
    // Vérifier la complexité du mot de passe
    if (!$this->validatePasswordStrength($newPassword)) {
        $this->render('auth/reset_password', [
            'token' => $token,
            'error' => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.",
            'csrf_token' => $this->generateCSRFToken()
        ]);
        return;
    }
    
    // Récupérer les données de l'utilisateur
    $user = User::getById($userId);
    if (!$user) {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la réinitialisation du mot de passe.";
        header('Location: index.php?controller=auth&action=forgotPasswordForm');
        exit;
    }
    
    // Mettre à jour le mot de passe
    $result = User::updateProfile($userId, $user['name'], $user['email'], $newPassword, $user['avatar'] ?? null);
    
    if (!$result) {
        $this->render('auth/reset_password', [
            'token' => $token,
            'error' => "Une erreur s'est produite lors de la mise à jour du mot de passe.",
            'csrf_token' => $this->generateCSRFToken()
        ]);
        return;
    }
    
    // Marquer le token comme utilisé
    PasswordReset::markTokenAsUsed($token);
    
    // Afficher un message de succès
    $_SESSION['success_message'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
    header('Location: index.php?controller=auth&action=login');
    exit;
}

/**
 * Valide la complexité du mot de passe
 * 
 * @param string $password Mot de passe à valider
 * @return bool True si le mot de passe est suffisamment fort
 */
private function validatePasswordStrength($password) {
    // Longueur minimale de 8 caractères
    if (strlen($password) < 8) {
        return false;
    }
    
    // Au moins une lettre majuscule
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Au moins une lettre minuscule
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    return true;
}
}
?>