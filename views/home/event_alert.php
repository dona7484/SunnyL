<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifId = $_GET['id'] ?? null;
if (!$notifId) {
    // Si l'ID n'est pas passé, afficher une erreur ou rediriger
    echo "<p style='color:red;'>Erreur : ID de notification manquant.</p>";
    exit;
}

// Récupérer la notification en fonction de l'ID
require_once __DIR__ . '/../../Models/Notification.php';
$notif = Notification::getById($notifId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evenement -alerte</title>
</head>
<body>
<audio id="notif-sound" src="audio/notif-sound.mp3" preload="auto"></audio>
<button onclick="document.getElementById('notif-sound').play()" style="display:none;" id="audio-unlock"></button>

<div style="display: none;">
    <ul id="notif-list"></ul>
    <span id="notif-count">0</span>
</div>
<div class="notif-screen">
<?php if ($notif): ?>
    <img src="images/IconeRappel.png" class="notif-icon" alt="Notification">

    <div class="notif-message">
        <?= htmlspecialchars($notif['content']) ?>
    </div>
    <form method="POST" action="/notifications/markAsRead">
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


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentNotifId = null;
        let lastNotifCount = 0;

        function showNotif(message) {
            const bubble = document.getElementById("notif-bubble");
            const text = document.getElementById("notif-bubble-text");
            const audio = document.getElementById("notif-sound");

            text.textContent = message;
            bubble.style.display = "flex"; // Affiche la bulle de notification

            audio.pause();
            audio.currentTime = 0; // Reset au début du son
            audio.play().catch(e => console.warn("🔇 Son bloqué :", e)); // Joue le son

            speakMessage(message); // Lecture vocale
        }

        function speakMessage(text) {
            const msg = new SpeechSynthesisUtterance(text);
            msg.lang = 'fr-FR';
            window.speechSynthesis.speak(msg);
        }

        function loadNotifications() {
            fetch("index.php?controller=notification&action=getUserNotifications")
            .then(response => response.json())
            .then(data => {
                const notifSound = document.getElementById("notif-sound");

                if (data.error) return;

                if (data.length > lastNotifCount) {
                    showNotif(data[0].content); // Afficher la notification dès qu'elle arrive
                }

                lastNotifCount = data.length;
            });
        }

        // Démarrage initial pour charger les notifications
        loadNotifications();
        setInterval(() => {
            fetch("index.php?controller=notification&action=getUserNotifications")
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    showNotif(data[0].content); // Afficher la première notification reçue
                }
            });
        }, 5000);
    });
    header('Location: index.php?controller=home&action=eventAlert');
exit;

</script>

<div id="notif-bubble" class="notif-bubble" onclick="markFirstAsRead()" style="display:none;">
    <img src="images/IconeRappel.png" alt="🔔" class="notif-bubble-icon">
    <div id="notif-bubble-text" class="notif-bubble-text">Vous avez une nouvelle notification</div>
</div>

</body>
</html>
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

    .toast {
    visibility: hidden;
    min-width: 250px;
    background-color: #323232;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 16px;
    position: fixed;
    z-index: 1000;
    left: 50%;
    bottom: 30px;
    transform: translateX(-50%);
    font-size: 18px;
    opacity: 0;
    transition: opacity 0.5s ease, bottom 0.5s ease;
}

.toast.show {
    visibility: visible;
    opacity: 1;
    bottom: 50px;
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

