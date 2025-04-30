<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Relations - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .relations-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .page-title {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }
        .senior-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }
        .senior-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .senior-info {
            display: flex;
            align-items: center;
        }
        .senior-avatar {
            width: 60px;
            height: 60px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
            font-size: 24px;
            color: #333;
        }
        .senior-name {
            font-size: 18px;
            font-weight: bold;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-view {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-message {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-delete {
            background-color: #F44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
        }
        .add-button {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #FFD700;
            color: #333;
            border: none;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: none;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #333;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="relations-container">
        <a href="index.php?controller=home&action=family_dashboard" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        
        <h1 class="page-title">Mes Parents Âgés</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($seniors)): ?>
            <div class="empty-state">
                <img src="/SunnyLink/public/images/empty-icon.png" alt="Aucune relation" style="width: 100px; height: 100px; margin-bottom: 20px;">
                <h3>Vous n'êtes encore relié à aucun parent âgé</h3>
                <p>Ajoutez un parent âgé pour commencer à partager des photos, des messages et des événements.</p>
            </div>
        <?php else: ?>
            <?php foreach ($seniors as $senior): ?>
                <div class="senior-card">
                    <div class="senior-info">
                        <div class="senior-avatar">
                            <?= substr($senior['name'], 0, 1) ?>
                        </div>
                        <div>
                            <div class="senior-name"><?= htmlspecialchars($senior['name']) ?></div>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="index.php?controller=message&action=send&receiver_id=<?= $senior['user_id'] ?>" class="btn-message">
                            <i class="fas fa-comment"></i> Message
                        </a>
                        <a href="index.php?controller=photo&action=form&senior_id=<?= $senior['user_id'] ?>" class="btn-view">
                            <i class="fas fa-camera"></i> Photo
                        </a>
                        <a href="index.php?controller=relation&action=delete&id=<?= $senior['user_id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette relation ?');">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <a href="index.php?controller=relation&action=create" class="add-button">
            <i class="fas fa-plus"></i> Ajouter un parent âgé
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>