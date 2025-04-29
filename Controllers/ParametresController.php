<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Relation.php';

class ParametresController extends Controller {
    
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'];
        
        // Récupérer les informations de l'utilisateur
        $user = User::getById($userId);
        
        // Récupérer les parents âgés liés (si l'utilisateur est un membre de la famille)
        $parentsLies = [];
        if ($userRole === 'familymember') {
            $relationModel = new RelationModel();
            $parentsLies = $relationModel->getSeniorsForFamilyMember($userId);
        }
        
        // Récupérer les préférences de notification
        $notifPrefs = $this->getNotificationPreferences($userId);
        
        // Rendre la vue
        $this->render('parametres/index', [
            'user' => $user,
            'parentsLies' => $parentsLies,
            'notifPrefs' => $notifPrefs
        ]);
    }
    
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $photoUrl = null;
        
        // Traitement de l'upload de photo de profil
        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '-' . basename($_FILES['photo_profil']['name']);
            $uploadFile = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $uploadFile)) {
                $photoUrl = $uploadFile;
            }
        }
        
        // Mise à jour du profil utilisateur
        User::updateProfile($userId, $_SESSION['name'], null, null, $photoUrl);
        
        // Redirection vers la page des paramètres
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    public function updateNotifications() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les préférences de notification
        $emailEnabled = isset($_POST['email_notif']) ? 1 : 0;
        $smsEnabled = isset($_POST['sms_notif']) ? 1 : 0;
        $pushEnabled = isset($_POST['push_notif']) ? 1 : 0;
        
        // Mettre à jour les préférences
        $this->updateNotificationPreferences($userId, $emailEnabled, $smsEnabled, $pushEnabled);
        
        // Redirection vers la page des paramètres
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Vérifier que les mots de passe correspondent
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = "Les nouveaux mots de passe ne correspondent pas.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Vérifier le mot de passe actuel
        $user = User::getById($userId);
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error_message'] = "Le mot de passe actuel est incorrect.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Mettre à jour le mot de passe
        User::updateProfile($userId, $user['name'], $user['email'], $newPassword);
        
        $_SESSION['success_message'] = "Votre mot de passe a été mis à jour avec succès.";
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    public function removeParent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $seniorId = $_POST['senior_id'] ?? null;
        
        if (!$seniorId) {
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        
        // Supprimer la relation
        $relationModel = new RelationModel();
        $relationModel->removeRelation($userId, $seniorId);
        
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }
    
    private function getNotificationPreferences($userId) {
        $db = new DbConnect();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prefs) {
            // Créer des préférences par défaut si elles n'existent pas
            $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, email_notif, sms_notif, push_notif, dark_mode) VALUES (?, 1, 1, 1, 0)");
            $stmt->execute([$userId]);
            
            return [
                'email_notif' => 1,
                'sms_notif' => 1,
                'push_notif' => 1,
                'dark_mode' => 0
            ];
        }
        
        return $prefs;
    }
    
    private function updateNotificationPreferences($userId, $email, $sms, $push) {
        $db = new DbConnect();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("UPDATE user_preferences SET email_notif = ?, sms_notif = ?, push_notif = ? WHERE user_id = ?");
        return $stmt->execute([$email, $sms, $push, $userId]);
    }
    
}
