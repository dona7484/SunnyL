<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
echo "<p style='color:red;'>🔍 SESSION user_id = " . ($_SESSION['user_id'] ?? 'non défini') . "</p>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<audio id="notif-sound" src="audio/notif-sound.mp3" preload="auto"></audio>

<div id="notif-bubble" class="notif-bubble" style="display:none;">
    <img src="images/IconeRappel.png" class="notif-bubble-icon" alt="🔔">
    <div id="notif-bubble-text" class="notif-bubble-text">Une notification va s’afficher ici...</div>
</div>

<script>
function speakMessage(text) {
    const msg = new SpeechSynthesisUtterance(text);
    msg.lang = 'fr-FR';
    window.speechSynthesis.speak(msg);
}

function showNotif(message) {
    const bubble = document.getElementById("notif-bubble");
    const text = document.getElementById("notif-bubble-text");
    const audio = document.getElementById("notif-sound");

    text.textContent = message;
    bubble.style.display = "flex";

    audio.pause();
    audio.currentTime = 0;
    audio.play().catch(e => console.warn("🔇 Bloqué :", e));

    // Lecture vocale du message
    const msg = new SpeechSynthesisUtterance(message);
    msg.lang = 'fr-FR';
    window.speechSynthesis.speak(msg);
}

// Vérification des notifications toutes les 5 secondes
setInterval(() => {
  fetch('index.php?controller=notification&action=getUserNotifications')
    .then(res => {
      console.log("Statut de la réponse:", res.status);
      return res.json();
    })
    .then(data => {
      console.log("Données reçues:", data);
      if (data && data.length > 0) {
        showNotif(data[0].content);
      }
    })
    .catch(err => {
      console.error('Erreur dans fetch notifications:', err);
    });
}, 5000);

</script>

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

</body>
</html>
