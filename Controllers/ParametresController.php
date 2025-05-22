<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SeniorModel.php';
require_once __DIR__ . '/Controller.php';

class ParametresController extends Controller
{
    // Affichage de la page paramètres
    public function index() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $user = User::getById($userId);
    
    // Variables spécifiques au rôle
    $linkedParents = [];
    
    // Si c'est un membre de la famille, récupérer les seniors liés
    if ($_SESSION['role'] === 'famille' || $_SESSION['role'] === 'familymember') {
        $seniorModel = new SeniorModel();
        $linkedParents = $seniorModel->getSeniorsForFamilyMember($userId);
    }
    
    // Rendre la vue appropriée selon le rôle
    if ($_SESSION['role'] === 'senior') {
        $this->render('parametres/index', [
            'user' => $user
        ]);
    } else {
        $this->render('parametres/index', [
            'user' => $user,
            'linkedParents' => $linkedParents
        ]);
    }
}

 public function updateProfile() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Vérifier si l'avatar est présent dans la requête
    $hasAvatar = isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK;
    $avatarPath = null;
    
    if ($hasAvatar) {
        // Créer le dossier des images s'il n'existe pas
        $uploadDir = __DIR__ . '/../public/images/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                error_log("Impossible de créer le répertoire pour les avatars: $uploadDir");
                $_SESSION['error_message'] = "Erreur : Impossible de créer le répertoire pour les avatars.";
                header('Location: index.php?controller=parametres&action=index');
                exit;
            }
        }
        
        // Vérifier les permissions du dossier
        if (!is_writable($uploadDir)) {
            error_log("Le répertoire des avatars n'est pas accessible en écriture: $uploadDir");
            $_SESSION['error_message'] = "Erreur : Le dossier des avatars n'est pas accessible en écriture.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Générer un nom de fichier unique
        $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Vérifier l'extension
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['error_message'] = "Erreur : Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Vérifier la taille (max 5 Mo)
        if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error_message'] = "Erreur : L'image est trop volumineuse (max 5 Mo).";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Nom de fichier unique avec timestamp
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $uploadFilePath = $uploadDir . $filename;
        
        // Déplacer le fichier temporaire
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFilePath)) {
            $avatarPath = $filename;
            error_log("Avatar téléchargé avec succès: $uploadFilePath");
        } else {
            error_log("Échec du téléchargement de l'avatar. Code d'erreur: " . $_FILES['avatar']['error']);
            $_SESSION['error_message'] = "Erreur lors du téléchargement de la photo.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
    }
    
    // Mise à jour du profil
    if ($avatarPath !== null) {
        // Si une nouvelle image a été téléchargée
        $result = User::updateProfile($userId, $name, $email, null, $avatarPath);
    } else {
        // Sinon, mettre à jour uniquement le nom et l'email
        $result = User::updateProfile($userId, $name, $email);
    }
    
    if ($result) {
        $_SESSION['success_message'] = "Profil mis à jour avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du profil.";
    }
    
    header('Location: index.php?controller=parametres&action=index');
    exit;
}

  /**
 * Gère la mise à jour du mot de passe de l'utilisateur
 */
public function updatePassword() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    
    // Vérifier le token CSRF
    $this->requireValidCSRFToken($_POST['csrf_token'] ?? '', 'index.php?controller=parametres&action=index');
    
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Vérifier que tous les champs sont remplis
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Récupérer les données de l'utilisateur
    $user = User::getById($userId);
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Vérifier que le mot de passe actuel est correct
    if (!password_verify($currentPassword, $user['password'])) {
        // Enregistrer la tentative échouée
        require_once __DIR__ . '/../models/LoginAttempt.php';
        LoginAttempt::recordAttempt($userId);
        
        $_SESSION['error_message'] = "Le mot de passe actuel est incorrect.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Vérifier que les nouveaux mots de passe correspondent
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "Les nouveaux mots de passe ne correspondent pas.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Vérifier que le nouveau mot de passe est différent de l'ancien
    if (password_verify($newPassword, $user['password'])) {
        $_SESSION['error_message'] = "Le nouveau mot de passe doit être différent de l'ancien.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Vérifier la complexité du mot de passe
    if (!$this->validatePasswordStrength($newPassword)) {
        $_SESSION['error_message'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    // Mettre à jour le mot de passe
    $result = User::updateProfile($userId, $user['name'], $user['email'], $newPassword, $user['avatar'] ?? null);
    
    if ($result) {
        // Régénérer l'ID de session pour plus de sécurité
        session_regenerate_id(true);
        
        $_SESSION['success_message'] = "Votre mot de passe a été modifié avec succès.";
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la modification du mot de passe.";
    }
    
    header('Location: index.php?controller=parametres&action=index');
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

    // Ajout d'un parent (redirige vers la page d'ajout)
    public function addParent()
    {
        // Ici tu peux rediriger vers un formulaire ou gérer l'ajout direct
        header('Location: index.php?controller=relation&action=create');
        exit;
    }

    // Suppression d'un parent lié
    public function removeParent()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        $familyId = $_SESSION['user_id'];
        $parentId = $_POST['parent_id'] ?? null;
        if ($parentId) {
            $db = new DbConnect();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("DELETE FROM relations WHERE family_id = ? AND senior_id = ?");
            $stmt->execute([$familyId, $parentId]);
            $_SESSION['success_message'] = "Parent supprimé.";
        }
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    public function senior()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?controller=auth&action=login");
        exit;
    }
    $user = User::getById($_SESSION['user_id']);
    $this->render('parametres/senior', ['user' => $user]);
}

}
?>
