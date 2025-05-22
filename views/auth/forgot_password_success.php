<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email envoyé - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f8fa;
            font-family: 'Arial', sans-serif;
            padding-top: 60px;
        }
        .success-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .logo {
            margin-bottom: 30px;
        }
        .logo img {
            height: 80px;
        }
        .icon-success {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #FFD700;
            border-color: #FFD700;
            color: #333;
            margin-top: 20px;
        }
        .btn-primary:hover {
            background-color: #e6c200;
            border-color: #e6c200;
            color: #333;
        }
        .development-link {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="SunnyLink Logo">
                </a>
            </div>
            
            <div class="icon-success">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            
            <h2>Email envoyé !</h2>
            
            <p class="text-muted mt-3">
                <?= htmlspecialchars($message) ?>
            </p>
            
            <p>
                Si vous ne recevez pas d'email dans les prochaines minutes, vérifiez votre dossier de spam ou contactez notre support.
            </p>
            
            <a href="index.php?controller=auth&action=login" class="btn btn-primary">
                Retour à la connexion
            </a>
            
            <?php if (isset($development_link)): ?>
                <div class="development-link">
                    <p><strong>Mode développement :</strong> Lien de réinitialisation :</p>
                    <p><a href="<?= htmlspecialchars($development_link) ?>" class="text-break"><?= htmlspecialchars($development_link) ?></a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>