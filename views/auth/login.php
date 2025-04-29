<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// Utilisez le bon chemin vers le fichier User.php dans le dossier models
require_once __DIR__ . '/../../models/User.php';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $interface = $_POST['interface'] ?? 'default'; // Récupère l'interface choisie Cela vous aidera à savoir si les données du formulaire sont correctement envoyées
    echo "Données reçues : ";
    var_dump($_POST);
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez saisir votre email et votre mot de passe.";
    } else {
        // Récupérer l'utilisateur via son email
        $user = User::getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            // Authentification réussie, on stocke les informations dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['interface'] = $interface; // Ajout de l'interface dans la session
            echo "Données reçues : ";
var_dump($_POST);

            // Redirection selon le rôle et l'interface choisie
            if ($_SESSION['role'] === 'senior' && $_SESSION['interface'] === 'tablet') {
                header("Location: index.php?controller=dashboard&action=senior");
            } elseif ($_SESSION['role'] === 'famille' && $_SESSION['interface'] === 'default') {
                header("Location: index.php?controller=dashboard&action=family");
            } else {
                // Si l'interface tablette est choisie pour un membre de la famille
                // ou si l'interface ne correspond pas, on redirige vers le tableau de bord par défaut
                header("Location: index.php?controller=dashboard&action=family");
            }
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SunnyLink - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #FFD700;
            border-color: #FFD700;
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h2>SunnyLink</h2>
            </div>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="forgot-password">
                    <a href="index.php?controller=auth&action=forgotPassword">Mot de passe oublié</a>
                </div>
                
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            
            <div class="register-link">
                <p>Nouveau sur SunnyLink? <a href="index.php?controller=auth&action=register">Créer un compte</a></p>
            </div>
        </div>
    </div>
</body>
</html>

