<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur Spotify - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .back-dashboard-btn {
    display: inline-flex;
    align-items: center;
    background-color: #4a4a4a;
    color: white;
    padding: 12px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease;
    border: none;
    margin-bottom: 20px;
}

.back-dashboard-btn:hover {
    background-color: #333333;
    color: white;
    text-decoration: none;
}

.back-dashboard-btn i {
    margin-right: 8px;
}
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .btn-retry {
            background-color: #1DB954;
            border-color: #1DB954;
            margin-top: 20px;
        }
        .back-dashboard-btn {
    display: inline-flex;
    align-items: center;
    background-color: #4a4a4a;
    color: white;
    padding: 12px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease;
    border: none;
    margin-bottom: 20px;
}

.back-dashboard-btn:hover {
    background-color: #333333;
    color: white;
    text-decoration: none;
}

.back-dashboard-btn i {
    margin-right: 8px;
}
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h2>Erreur d'authentification Spotify</h2>
            <p class="lead"><?= htmlspecialchars($message) ?></p>
            <p>Veuillez réessayer l'authentification ou contacter l'administrateur si le problème persiste.</p>
            <a href="index.php?controller=spotify&action=auth" class="btn btn-retry">Réessayer</a>
            <a href="index.php?controller=home&action=dashboard" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>
