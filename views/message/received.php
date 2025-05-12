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
    <title>Messages reçus - SunnyLink</title>
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
            background-color: #FFD700;
            color: #333;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sender-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #333;
        }
        
        .sender-name {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .message-date {
            font-size: 0.8rem;
            color: #555;
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
        
        .message-actions {
            display: flex;
            gap: 15px;
        }
        
        .action-button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }
        
        .action-button:hover {
            color: #FFD700;
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
        
        .status-unread {
            background-color: #FFD700;
            color: #333;
        }
        
        .status-read {
            background-color: #e0e0e0;
            color: #666;
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
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #FFD700;
            margin-bottom: 20px;
        }
        
        .empty-state-text {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php?controller=home&action=<?= ($_SESSION['role'] === 'senior') ? 'dashboard' : 'family_dashboard' ?>" class="back-button">
    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
</a>
        
        <h1 class="page-title">Mes Messages</h1>
        <ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="index.php?controller=message&action=received">Messages reçus</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="index.php?controller=message&action=sent">Messages envoyés</a>
    </li>
</ul>
<div class="row mb-4">
    <div class="col-12 text-end">
        <a href="index.php?controller=message&action=send" class="btn btn-primary">
            <i class="fas fa-pen"></i> Écrire un message
        </a>
    </div>
</div>
        <?php if (empty($messages) && empty($audioMessages)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="empty-state-text">
                    Vous n'avez pas encore reçu de messages.
                </div>
            </div>
        <?php else: ?>
            <div class="message-container">
                <?php foreach ($messages as $message): ?>
                    <div class="card message-card">
                        <div class="message-header">
                            <div class="sender-info">
                                <div class="sender-avatar">
                                    <?= substr($message['sender_name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="sender-name"><?= htmlspecialchars($message['sender_name']) ?></div>
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
                                <button class="action-button mark-read" data-id="<?= $message['id'] ?>" title="Marquer comme lu">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-button reply" data-id="<?= $message['id'] ?>" title="Répondre">
                                    <i class="fas fa-reply"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($audioMessages as $audioMessage): ?>
                    <div class="card message-card audio-message">
                        <div class="message-header">
                            <div class="sender-info">
                                <div class="sender-avatar">
                                    <?= substr($audioMessage['sender_name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="sender-name"><?= htmlspecialchars($audioMessage['sender_name']) ?></div>
                                    <div class="message-date">
                                        <?= date('d/m/Y à H:i', strtotime($audioMessage['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="message-status <?= $audioMessage['is_read'] ? 'status-read' : 'status-unread' ?>">
                            <?= $audioMessage['is_read'] ? 'Lu' : 'Non lu' ?>
                        </div>
                        
                        <div class="message-body">
                            <div>
                                <i class="fas fa-microphone"></i> Message audio
                            </div>
                            <audio controls class="audio-controls">
                                <source src="data:audio/webm;base64,<?= $audioMessage['audio_data'] ?>" type="audio/webm">
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                        </div>
                        
                        <div class="message-footer">
                            <div class="message-actions">
                                <button class="action-button mark-read" data-id="<?= $audioMessage['id'] ?>" title="Marquer comme lu">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-button reply-audio" data-id="<?= $audioMessage['id'] ?>" title="Répondre par audio">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour marquer un message comme lu
        document.querySelectorAll('.mark-read').forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                
                fetch('index.php?controller=message&action=markAsRead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message_id: messageId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour l'interface utilisateur
                        const card = this.closest('.message-card');
                        const statusBadge = card.querySelector('.message-status');
                        
                        statusBadge.classList.remove('status-unread');
                        statusBadge.classList.add('status-read');
                        statusBadge.textContent = 'Lu';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
            });
        });
        
        // Fonction pour répondre à un message
        document.querySelectorAll('.reply').forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                window.location.href = 'index.php?controller=message&action=send&reply_to=' + messageId;
            });
        });
        
        // Fonction pour répondre par audio
        document.querySelectorAll('.reply-audio').forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                window.location.href = 'index.php?controller=message&action=send&audio=1&reply_to=' + messageId;
            });
        });
    </script>
</body>
</html>
