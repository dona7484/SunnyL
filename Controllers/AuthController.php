<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $interface = $_POST['interface'] ?? 'default'; // Interface choisie
    
            // Connexion à la base de données
            $db = new DbConnect();
            $connection = $db->getConnection();
            $query = $connection->prepare("SELECT * FROM users WHERE email = :email");
            $query->execute(['email' => $email]);
            $query->setFetchMode(PDO::FETCH_OBJ);
            $user = $query->fetch();
    
            if ($user && password_verify($password, $user->password)) {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user_id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['role'] = $user->role;
                session_regenerate_id(true);
    
                // Si l'interface est 'tablet' et que le rôle est famille, on redirige vers un tableau de bord senior
                if ($interface === 'tablet' && $user->role === 'familymember') {
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
                $erreur = "Identifiants incorrects.";
                $this->render('auth/login', ['erreur' => $erreur]);
            }
        } else {
            $this->render('auth/login');
        }
    }
    
    public function getSeniorForFamilyMember($familyMemberId) {
        $query = $this->connection->prepare("SELECT senior_id FROM family_members WHERE family_member_id = :id");
        $query->execute(['id' => $familyMemberId]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['senior_id'] : null;
    }

    public function render($view, $data = [])
    {
        extract($data);
        include "../views/{$view}.php";
    }

    public function logout()
    {
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
