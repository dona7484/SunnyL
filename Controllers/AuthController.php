<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $interface = $_POST['interface'] ?? 'default';
    
            $db = new DbConnect();
            $connection = $db->getConnection();
            $query = $connection->prepare("SELECT * FROM users WHERE email = :email");
            $query->execute(['email' => $email]);            
            $query->setFetchMode(PDO::FETCH_OBJ);
            $user = $query->fetch();
    
            if ($user && password_verify($password, $user->password)) {
                // Stocker initialement les infos de l'utilisateur
                $_SESSION['user_id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['role'] = $user->role;
                session_regenerate_id(true);
    
                // Si l'interface choisie est "tablet" et que l'utilisateur est de type famille,
                // récupérer l'ID du senior associé
                if ($interface === 'tablet' && $user->role === 'famille') {
                    $seniorId = $this->getSeniorForFamilyMember($user->id);
                    if ($seniorId) {
                        $_SESSION['user_id'] = $seniorId;
                        $_SESSION['role'] = 'senior';
                    }
                }
    
                // Rediriger selon le rôle final
                if ($_SESSION['role'] === 'senior') {
                    header('Location: index.php?controller=home&action=dashboard');
                } else {
                    header('Location: index.php?controller=home&action=dashboard');
                }
                exit;
            } else {
                $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
                $this->render('auth/login', ['erreur' => $erreur]);
            }
        } else {
            $this->render('auth/login');
        }
    }
    
    /**
     * Méthode pour récupérer l'ID du senior associé à un family member.
     * Cette méthode interroge la table "relations" qui doit contenir (senior_id, family_id).
     */
    private function getSeniorForFamilyMember($familyMemberId) {
        // Assurez-vous que la table "relations" existe et contient des enregistrements.
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT senior_id FROM relations WHERE family_id = ?");
        $stmt->execute([$familyMemberId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->senior_id : null;
    }
    

    public function render($view, $data = [])
    {
        extract($data);
        include "../views/{$view}.php";
    }

    public function logout()
    {
        // Vérifier que la session est démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?controller=auth&action=login');
        exit();
    }
}
?>
