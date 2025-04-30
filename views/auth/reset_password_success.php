<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SunnyLink - Mot de passe réinitialisé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .success-container {
            max-width: 500px;
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
        .password-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #FFD700;
            border-color: #FFD700;
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
        .note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="logo">
                <h2>SunnyLink</h2>
                <p>Mot de passe réinitialisé</p>
            </div>
            
            <div class="alert alert-success">
                La réinitialisation du mot de passe a réussi !
            </div>
            
            <p>Un mot de passe temporaire a été généré pour <strong><?= htmlspecialchars($email) ?></strong>.</p>
            
            <p>Voici votre mot de passe temporaire :</p>
            
            <div class="password-box">
                <?= htmlspecialchars($tempPassword) ?>
            </div>
            
            <p class="note">
                <strong>Remarque importante :</strong> Dans un environnement de production, ce mot de passe serait envoyé par email au lieu d'être affiché ici. Cette méthode est utilisée uniquement pour démonstration.
            </p>
            
            <p class="note">
                Nous vous recommandons de modifier ce mot de passe temporaire dès que possible après vous être connecté.
            </p>
            
            <a href="index.php?controller=auth&action=login" class="btn btn-primary mt-3">Retour à la connexion</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>