<?php
// Assurez-vous que ce fichier est dans le dossier 'Controllers'

class AuthController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $password = $_POST['password'];

            $db = new DbConnect();
            $connection = $db->getConnection();
            $query = $connection->prepare("SELECT * FROM users WHERE name = :name");
            $query->execute(['name' => $name]);
            $user = $query->fetch();

            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['name'] = $user->name;
                session_regenerate_id(true);
                header('Location: index.php?controller=home&action=index');
                exit;
            } else {
                $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
                $this->render('auth/login', ['erreur' => $erreur]);
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function render($view, $data = [])
    {
        extract($data);
        include "../views/{$view}.php";
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?controller=auth&action=login');
        exit();
    }
}

    
    // public function register() {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $name = $_POST['name'];
    //         $email = $_POST['email'];
    //         $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //         require_once '../config/database.php';
    //         $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    //         $stmt->execute([$name, $email, $password]);

    //         header('Location: index.php?controller=auth&action=login');  // Rediriger vers la page de connexion
    //     } else {
    //         require_once '../app/views/auth/register.php';  // Afficher le formulaire d'inscription
    //     }
    // }

    // public function registerUser()
    // {
    //     require_once "../config/database.php";  // Connexion à la base de données
    
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         $name = htmlspecialchars($_POST["name"]);
    //         $email = htmlspecialchars($_POST["email"]);
    //         $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hachage du mot de passe
    
    //         try {
    //             $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    //             $stmt->execute([$email]);
    //             if ($stmt->rowCount() > 0) {
    //                 die("❌ Erreur : Cet email est déjà utilisé !");
    //             }
    
    //             $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    //             $stmt->execute([$name, $email, $password]);
    
    //             echo "✅ Inscription réussie ! <a href='/SunnyLink/public/login'>Connectez-vous ici</a>";
    //         } catch (PDOException $e) {
    //             die("❌ Erreur SQL : " . $e->getMessage());
    //         }
    //     }
    // }

    // // Méthode pour connecter un utilisateur
    // public function loginUser()
    // {
    //     require_once "../config/database.php"; // Connexion à la base de données
    //     session_start(); // Démarrer la session
    //     session_regenerate_id(true); // Sécuriser la session contre le vol de cookies
    
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         $email = htmlspecialchars($_POST["email"]);
    //         $password = $_POST["password"];
    
    //         try {
    //             // Vérifier si l'utilisateur existe dans la base de données
    //             $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    //             $stmt->execute([$email]);
    //             $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //             if ($user) {
    //                 // Vérifier le mot de passe
    //                 if (password_verify($password, $user["password"])) {
    //                     // Stocker les infos utilisateur dans la session
    //                     $_SESSION["user_id"] = $user["id"];
    //                     $_SESSION["user_name"] = $user["name"];
    //                     $_SESSION["user_email"] = $user["email"];
    //                     $_SESSION["user_avatar"] = !empty($user["avatar"]) ? $user["avatar"] : "default-avatar.png"; // Ajouter un avatar si disponible
    //                     // Rediriger vers le dashboard après une connexion réussie
    //                     header('Location: /SunnyLink/public/dashboard'); 
    //                     exit();
    //                 } else {
    //                     $erreur = "❌ Mot de passe incorrect.";
    //                     $this->render('auth/login', ['erreur' => $erreur]);  // Passer l'erreur à la vue
    //                 }
    //             } else {
    //                 $erreur = "❌ Aucun compte trouvé avec cet email.";
    //                 $this->render('auth/login', ['erreur' => $erreur]);  // Passer l'erreur à la vue
    //             }
    //         } catch (PDOException $e) {
    //             echo "❌ Erreur SQL : " . $e->getMessage();
    //         }
    //     }
    // }
    

    // Méthode pour mettre à jour le profil
//     public function updateProfile()
//     {
//         require_once "../config/database.php";  // Connexion à la base de données
//         session_start();  // Démarrer la session

//         if (!isset($_SESSION["user_id"])) {
//             echo "❌ Vous devez être connecté.";
//             return;
//         }

//         if ($_SERVER["REQUEST_METHOD"] == "POST") {
//             $name = htmlspecialchars($_POST["name"]);
//             $email = htmlspecialchars($_POST["email"]);
//             $userId = $_SESSION["user_id"];

//             try {
//                 $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
//                 $stmt->execute([$email, $userId]);

//                 if ($stmt->rowCount() > 0) {
//                     echo "❌ Cet email est déjà utilisé.";
//                     return;
//                 }

//                 if (!empty($_POST["password"])) {
//                     $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
//                     $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
//                     $stmt->execute([$name, $email, $password, $userId]);
//                 } else {
//                     $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
//                     $stmt->execute([$name, $email, $userId]);
//                 }

//                 $_SESSION["user_name"] = $name;
//                 $_SESSION["user_email"] = $email;

//                 echo "✅ Profil mis à jour avec succès !";
//             } catch (PDOException $e) {
//                 echo "❌ Erreur SQL : " . $e->getMessage();
//             }
//         }

//         // Gestion de l'upload d'image (photo de profil)
//         if (!empty($_FILES["avatar"]["name"])) {
//             $targetDir = "../public/uploads/";
            
//             if (!is_dir($targetDir)) {
//                 mkdir($targetDir, 0777, true);
//             }

//             $fileName = basename($_FILES["avatar"]["name"]);
//             $targetFilePath = $targetDir . $fileName;
//             $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

//             $allowTypes = ["jpg", "png", "jpeg", "gif"];
//             if (in_array($fileType, $allowTypes)) {
//                 if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFilePath)) {
//                     $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
//                     $stmt->execute([$fileName, $userId]);

//                     $_SESSION["user_avatar"] = $fileName;

//                     echo "✅ Photo de profil mise à jour.";
//                 } else {
//                     echo "❌ Erreur lors de l'upload de l'image.";
//                 }
//             } else {
//                 echo "❌ Format de fichier non autorisé.";
//             }
//         }
//     }

//     // Méthode pour afficher le profil
//     public function profile()
//     {
//         session_start();

//         if (!isset($_SESSION["user_id"])) {
//             header("Location: /SunnyLink/public/login");
//             exit();
//         }

//         require_once "../app/views/profile/profile.php";
//     }
// }



