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
    <title>Connexion - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="mb-4 text-center">Connexion</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="interface" class="form-label">Choisissez votre interface</label>
                    <select name="interface" id="interface" class="form-control">
                        <!-- "default" correspond à l'interface classique pour les proches -->
                        <option value="default">Interface proche</option>
                        <!-- "tablet" correspond à l'interface tablette (dashboard senior) -->
                        <option value="tablet">Interface tablette (dashboard senior)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
