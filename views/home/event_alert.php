<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifId = $_GET['id'] ?? null;
if (!$notifId) {
    echo "<p style='color:red;'>Erreur : ID de notification manquant.</p>";
    exit;
}

require_once __DIR__ . '/../../Models/Notification.php';
$notif = Notification::getById($notifId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événement - alerte</title>
    <style>
        .notif-screen {
            text-align: center;
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        .notif-icon {
            width: 100px;
            animation: pulse 1.2s infinite;
        }

        .notif-message {
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0;
        }

        .notif-validate {
            width: 100px;
            cursor: pointer;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notif-bubble {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #ffefc1;
            border: 3px solid #ffc107;
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            cursor: pointer;
            animation: slideUp 0.6s ease;
            z-index: 9999;
        }

        .notif-bubble-icon {
            width: 60px;
            height: 60px;
        }

        .notif-bubble-text {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            max-width: 300px;
        }

        @keyframes slideUp {
            from { transform: translate(-50%, 100px); opacity: 0; }
            to   { transform: translate(-50%, 0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Audio préchargé pour les notifications -->
    <audio id="notification-sound" preload="auto" style="display:none;">
        <source src="audio/notif-sound.mp3" type="audio/mpeg">
    </audio>

    <div class="notif-screen">
    <?php if ($notif): ?>
        <img src="images/IconeRappel.png" class="notif-icon" alt="Notification">

        <div class="notif-message">
            <?= htmlspecialchars($notif['content']) ?>
        </div>
        <form method="POST" action="index.php?controller=notification&action=markNotificationAsRead">
            <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
            <button type="submit" style="border:none;background:none;">
                <img src="images/check-button.png" class="notif-validate" alt="Valider">
            </button>
        </form>
    <?php else: ?>
        <div class="notif-message">
            Aucune nouvelle notification.
        </div>
    <?php endif; ?>
    </div>

    <!-- Inclure le fichier JavaScript centralisé -->
    <script src="js/notifications.js"></script>
    <script>
        // Jouer le son et lire la notification au chargement
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($notif): ?>
            // Lecture vocale du message
            speakMessage("<?= htmlspecialchars($notif['content'], ENT_QUOTES) ?>");
            
            // Jouer le son
            const audio = document.getElementById('notification-sound');
            if (audio) {
                audio.play().catch(e => console.warn("🔇 Son bloqué :", e));
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>
