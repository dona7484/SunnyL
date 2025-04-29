<!-- Dans views/message/sent.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?controller=auth&action=login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages envoyés - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f8fa;
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        
        .page-title {
            color: #4a4a4a;
            margin-bottom: 30px;
            font-weight: 700;
            text-align: center;
        }
        
        .message-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .message-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            position: relative;
        }
        
        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
        
        .message-header {
            background-color: #4a90e2;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .receiver-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .receiver-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #4a90e2;
        }
        
        .receiver-name {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .message-date {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
        }
        
        .message-body {
            padding: 20px;
            font-size: 1.1rem;
            color: #333;
            min-height: 100px;
        }
        
        .message-footer {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .message-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-read {
            background-color: #28a745;
            color: white;
        }
        
        .status-unread {
            background-color: #dc3545;
            color: white;
        }
        
        .audio-message {
            background-color: #f0f7ff;
        }
        
        .audio-controls {
            width: 100%;
            margin-top: 10px;
        }
        
        .back-button {
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #4a4a4a;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        
        .back-button:hover {
            background-color: #333;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #4a90e2;
            margin-bottom: 20px;
        }
        
        .empty-state-text {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
        
        .nav-tabs {
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link.active {
            color: #4a90e2;
            background-color: transparent;
            border-bottom: 3px solid #4a90e2;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php?controller=home&action=family_dashboard" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        
        <h1 class="page-title">Mes Messages</h1>
        
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="index.php?controller=message&action=received">Messages reçus</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php?controller=message&action=sent">Messages envoyés</a>
            </li>
        </ul>
        
        <?php if (empty($messages) && empty($audioMessages)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="empty-state-text">
                    Vous n'avez pas encore envoyé de messages.
                </div>
                <a href="index.php?controller=message&action=send" class="btn btn-primary">Envoyer un message</a>
            </div>
            <?php else: ?>
    <div class="message-container">
        <?php foreach ($messages as $message): ?>
            <div class="card message-card">
                <div class="message-header">
                    <div class="receiver-info">
                        <div class="receiver-avatar">
                            <?= substr($message['receiver_name'], 0, 1) ?>
                        </div>
                        <div>
                            <div class="receiver-name"><?= htmlspecialchars($message['receiver_name']) ?></div>
                            <div class="message-date">
                                <?= date('d/m/Y à H:i', strtotime($message['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="message-status <?= $message['is_read'] ? 'status-read' : 'status-unread' ?>">
                    <?= $message['is_read'] ? 'Lu' : 'Non lu' ?>
                </div>
                
                <div class="message-body">
                    <?= nl2br(htmlspecialchars($message['message'])) ?>
                </div>
                
                <div class="message-footer">
                    <div class="message-actions">
                        <a href="index.php?controller=message&action=send&reply_to=<?= $message['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-reply"></i> Répondre
                        </a>
                        <!-- Bouton SUPPRIMER -->
                        <a href="index.php?controller=message&action=delete&id=<?= $message['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

                
<?php if (!empty($audioMessages)): ?>
    <h2 class="mt-4">Messages audio envoyés</h2>
    <?php foreach ($audioMessages as $audio): ?>
        <div class="card message-card">
            <div class="message-header">
                <div class="receiver-info">
                    <div class="receiver-avatar">
                        <?= substr($audio['receiver_name'], 0, 1) ?>
                    </div>
                    <div>
                        <div class="receiver-name"><?= htmlspecialchars($audio['receiver_name']) ?></div>
                        <div class="message-date">
                            <?= date('d/m/Y à H:i', strtotime($audio['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="message-body">
                <audio controls src="<?= htmlspecialchars($audio['audio_url']) ?>"></audio>
            </div>
            <div class="message-footer">
                <div class="message-actions">
                    <!-- Bouton SUPPRIMER AUDIO -->
                    <a href="index.php?controller=message&action=deleteAudio&id=<?= $audio['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Voulez-vous vraiment supprimer ce message audio ?');">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
