<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

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
                $_SESSION['user_id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['role'] = $user->role; // Cette ligne doit être présente
                session_regenerate_id(true);
            
                // Redirection en fonction du rôle
                if ($_SESSION['role'] === 'senior') {
                    header('Location: index.php?controller=home&action=dashboard');
                } else {
                    header('Location: index.php?controller=home&action=family_dashboard');
                }
                exit;
            }
            
                // Si l'interface est 'tablet' et que le rôle est famille, on redirige vers un tableau de bord senior
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
                $erreur = "Identifiants incorrects.";
                $this->render('auth/login', ['erreur' => $erreur]);
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

    // Register method
    public function register() {
        // If form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get form data
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // IMPORTANT: Changé de 'familymember' à 'famille' pour correspondre à la base de données
            $role = $_POST['role'] ?? 'famille'; 
            
            // Simple validation
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
            
            // Check if email already exists
            $user = User::getByEmail($email);
            if ($user) {
                $errors[] = "Cet email est déjà utilisé";
            }
            
            // If no errors, register the user
            if (empty($errors)) {
                $result = User::register($name, $email, $password, $role);
                
                if ($result) {
                    // Redirect to login page with success message
                    $_SESSION['success_message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    header('Location: index.php?controller=auth&action=login');
                    exit;
                } else {
                    $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
            
            // If there are errors, show the register form again with errors
            $this->render('auth/register', ['errors' => $errors, 'name' => $name, 'email' => $email]);
        } else {
            // Show the register form
            $this->render('auth/register', []);
        }
    }

    // Forgot password method
    public function forgotPassword() {
        // If form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get email from form
            $email = $_POST['email'] ?? '';
            
            // Simple validation
            $errors = [];
            if (empty($email)) {
                $errors[] = "L'email est requis";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            
            // Check if user exists
            $user = User::getByEmail($email);
            if (!$user) {
                $errors[] = "Aucun compte n'est associé à cet email";
            }
            
            if (empty($errors)) {
                // Generate a random temporary password
                $tempPassword = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                
                // Update user password
                $userId = $user['id'];
                $userName = $user['name'];
                
                // Hash the new password
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Update the password in database
                $db = new DbConnect();
                $connection = $db->getConnection();
                $stmt = $connection->prepare("UPDATE users SET password = :password WHERE id = :id");
                $result = $stmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $userId
                ]);
                
                if ($result) {
                    // Here we would normally send an email with the temporary password
                    // But for this example, we'll just display it to the user
                    
                    // Success message
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
            
            // If there are errors, show the form again with errors
            $this->render('auth/forgot_password', ['errors' => $errors, 'email' => $email]);
        } else {
            // Show the forgot password form
            $this->render('auth/forgot_password', []);
        }
    }
}
?>