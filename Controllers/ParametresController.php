<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SeniorModel.php';
require_once __DIR__ . '/Controller.php';

class ParametresController extends Controller
{
    // Affichage de la page paramètres
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        $user = User::getById($userId);

        // Récupérer les seniors liés à ce membre de la famille
        $seniorModel = new SeniorModel();
        $linkedParents = $seniorModel->getSeniorsForFamilyMember($userId);

        $this->render('parametres/index', [
            'user' => $user,
            'linkedParents' => $linkedParents
        ]);
    }

    // Mise à jour du profil (nom, email, avatar)
    public function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $avatar = null;

        // Gestion de l'upload d'avatar si besoin
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/images/';
            $filename = uniqid() . '-' . basename($_FILES['avatar']['name']);
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename);
            $avatar = $filename;
        }

        $result = User::updateProfile($userId, $name, $email, null, $avatar);

        if ($result) {
            $_SESSION['success_message'] = "Profil mis à jour.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour du profil.";
        }
        header('Location: index.php?controller=parametres&action=index');
        exit;
    }

    // Changement de mot de passe
    public function updatePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = "Le mot de passe doit contenir au moins 6 caractères.";
            header('Location: index.php?controller=parametres&action=index');
            exit;
        }
        $user = User::getById($userId);
        $result = User::updateProfile($userId, $user['name'], $user['email'], $newPassword, $user['avatar'] ?? null);

        if ($result) {
            $_SESSION['success_message'] = "Mot de passe modifié.";
        } else {
            $_SESSION['error_message'] = "Erreur lors du changement de mot de passe.";
        }
        header('Location: index.php?controller=parametres&action=index');
        exit;
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
