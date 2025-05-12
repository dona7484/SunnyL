<?php
/**
 * Contrôleur d'authentification
 * 
 * Gère toutes les fonctionnalités liées à l'authentification des utilisateurs :
 * connexion, déconnexion, inscription et récupération de mot de passe.
 */

// Inclure les dépendances nécessaires
require_once __DIR__ . '/../config/database.php';  // Configuration de la base de données
require_once __DIR__ . '/../models/User.php';      // Modèle utilisateur

/**
 * Classe AuthController
 * 
 * Gère l'authentification et les opérations liées aux comptes utilisateurs
 */
class AuthController {
    /**
     * Méthode de connexion
     * 
     * Gère à la fois l'affichage du formulaire de connexion et le traitement de la soumission
     */
    public function login() {
        // Vérifier si le formulaire a été soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération des données du formulaire
            $email = $_POST['email'];
            $password = $_POST['password'];
            $interface = $_POST['interface'] ?? 'default'; // Interface choisie (par défaut 'default')
    
            // Connexion à la base de données
            $db = new DbConnect();
            $connection = $db->getConnection();
            
            // Préparation et exécution de la requête pour trouver l'utilisateur
            $query = $connection->prepare("SELECT * FROM users WHERE email = :email");
            $query->execute(['email' => $email]);
            $query->setFetchMode(PDO::FETCH_OBJ);  // Résultats sous forme d'objets
            $user = $query->fetch();
            
            // Vérification des identifiants
            if ($user && password_verify($password, $user->password)) {
                // Initialisation des variables de session
                $_SESSION['user_id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['role'] = $user->role; // Stockage du rôle utilisateur
                session_regenerate_id(true);     // Sécurité : régénère l'ID de session pour prévenir la fixation de session
            
                // Redirection en fonction du rôle
                if ($_SESSION['role'] === 'senior') {
                    header('Location: index.php?controller=home&action=dashboard');
                } else {
                    header('Location: index.php?controller=home&action=family_dashboard');
                }
                exit;
            }
            
                // Si l'interface est 'tablet' et que le rôle est famille, on redirige vers un tableau de bord senior
                // (Fonctionnalité spéciale pour tablette : un membre de famille peut voir l'interface senior)
                if ($interface === 'tablet' && $user->role === 'famille') {
                    $seniorId = $this->getSeniorForFamilyMember($user->id);
                    if ($seniorId) {
                        $_SESSION['user_id'] = $seniorId;
                        $_SESSION['role'] = 'senior';
                    }
                }
    
                // Rediriger vers le tableau de bord approprié en fonction du rôle
                if ($_SESSION['role'] === 'senior') {
                    header('Location: index.php?controller=home&action=dashboard');
                } else {
                    header('Location: index.php?controller=home&action=family_dashboard');
                }
                exit;
            } else {
                // Connexion échouée : préparation du message d'erreur
                $erreur = "Identifiants incorrects.";
                $this->render('auth/login', ['erreur' => $erreur]);
            }
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
    public function logout()
    {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Suppression des variables de session
        session_unset();
        
        // Destruction de la session
        session_destroy();
        
        // Suppression du cookie de session
        setcookie(session_name(), '', time() - 3600, '/');
        
        // Redirection vers la page de connexion
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
     * Méthode de récupération de mot de passe
     * 
     * Permet à l'utilisateur de réinitialiser son mot de passe s'il l'a oublié
     */
    public function forgotPassword() {
        // Si le formulaire est soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération de l'email du formulaire
            $email = $_POST['email'] ?? '';
            
            // Validation simple
            $errors = [];
            if (empty($email)) {
                $errors[] = "L'email est requis";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            
            // Vérification si l'utilisateur existe
            $user = User::getByEmail($email);
            if (!$user) {
                $errors[] = "Aucun compte n'est associé à cet email";
            }
            
            // Si pas d'erreurs, procéder à la réinitialisation
            if (empty($errors)) {
                // Génération d'un mot de passe temporaire aléatoire
                $tempPassword = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                
                // Mise à jour du mot de passe utilisateur
                $userId = $user['id'];
                $userName = $user['name'];
                
                // Hachage du nouveau mot de passe
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Mise à jour du mot de passe dans la base de données
                $db = new DbConnect();
                $connection = $db->getConnection();
                $stmt = $connection->prepare("UPDATE users SET password = :password WHERE id = :id");
                $result = $stmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $userId
                ]);
                
                if ($result) {
                    // Normalement, on enverrait un email avec le mot de passe temporaire
                    // Mais pour cet exemple, on l'affiche simplement à l'utilisateur
                    
                    // Message de succès
                    $_SESSION['temp_password'] = $tempPassword;
                    $_SESSION['success_message'] = "Un mot de passe temporaire a été généré. Veuillez vérifier ci-dessous.";
                    $this->render('auth/reset_password_success', [
                        'email' => $email,
                        'tempPassword' => $tempPassword
                    ]);
                    return;
                } else {
                    $errors[] = "Erreur lors de la réinitialisation du mot de passe. Veuillez réessayer.";
                }
            }
            
            // S'il y a des erreurs, afficher à nouveau le formulaire avec les erreurs
            $this->render('auth/forgot_password', ['errors' => $errors, 'email' => $email]);
        } else {
            // Afficher le formulaire de récupération de mot de passe
            $this->render('auth/forgot_password', []);
        }
    }
}
?>