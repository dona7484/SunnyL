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
    <title>Alertes - SunnyLink</title>
    <style>
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
            cursor: default;
            animation: slideUp 0.6s ease;
            z-index: 9999;
        }

        .notif-bubble-icon {
            width: 60px;
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

    <!-- Bulle de notification -->
    <div id="notif-bubble" class="notif-bubble" style="display:none;">
        <img src="images/IconeRappel.png" class="notif-bubble-icon" alt="🔔">
        <div id="notif-bubble-text" class="notif-bubble-text">Une notification va s'afficher ici...</div>
    </div>

    <!-- Inclure le fichier JavaScript centralisé -->
    <script src="js/notifications.js"></script>
</body>
</html>
