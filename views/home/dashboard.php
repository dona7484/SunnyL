<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p style='color:red;'>🔍 SESSION user_id = " . ($_SESSION['user_id'] ?? 'non défini') . "</p>";

// Ajouter une vérification des notifications actuelles
$notifModel = new NotificationModel();
$notifs = $notifModel->getUnreadNotifications($_SESSION['user_id'] ?? 0);
echo "<p style='color:blue;'>Notifications non lues: " . count($notifs) . "</p>";
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Senior - SunnyLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f7f7f7;
      margin: 0;
      padding: 0;
      font-family: 'Arial', sans-serif;
    }
    #header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #FFD700;
      padding: 10px 20px;
      color: white;
    }
    #header img {
      width: 40px;
      height: 40px;
      cursor: pointer;
    }
    #header .logo {
      font-size: 1.8rem;
      font-weight: bold;
    }
    #dashboardContainer {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 2rem;
      margin-top: 2rem;
    }
    .menuItem {
      width: 140px;
      height: 140px;
      background: white;
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      cursor: pointer;
      text-align: center;
      transition: transform 0.2s;
      background-color: #f5f5f5;
    }
    .menuItem:hover {
      transform: scale(1.05);
    }
    .menuItem img {
      width: 60px;
      height: 60px;
    }
    .menuItem span {
      margin-top: 0.5rem;
      font-size: 1.1rem;
      color: #333;
    }
    .notif-bubble {
      position: fixed;
      top: 20%;
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
    }

    .notif-bubble-text {
      font-size: 22px;
      font-weight: bold;
      color: #333;
      max-width: 300px;
    }

    .notif-button {
      background-color: green;
      color: white;
      border-radius: 5px;
      padding: 10px 20px;
      cursor: pointer;
      margin-top: 10px;
    }

    @keyframes slideUp {
      from { transform: translate(-50%, 100px); opacity: 0; }
      to   { transform: translate(-50%, 0); opacity: 1; }
    }

  </style>
</head>
<body>

<!-- Barre supérieure -->
<div id="header">
  <div class="logo">
    <img src="images/IconeSourdine.png" alt="Volume" onclick="toggleVolume()">
    SunnyLink
  </div>
  <button id="monCompteBtn" class="btn btn-outline-dark">Mon compte</button>
</div>

<!-- Partie spécifique au senior -->
<div class="container mt-4">
  <h2>Dashboard Senior</h2>
  <p>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?>.</p>
</div>

<!-- Tuiles principales du dashboard -->
<div id="dashboardContainer">
  <?php if ($_SESSION['role'] === 'senior'): ?>
    <!-- Les éléments spécifiques au senior -->
    <div class="menuItem" onclick="openMusic()">
      <img src="images/iconeMusic.png" alt="Musique">
      <span>Écouter de la musique</span>
    </div>
    <div class="menuItem" onclick="receiveCalls()">
      <img src="images/IconeAppel.png" alt="Appels">
      <span>Recevoir des appels</span>
    </div>
    <div class="menuItem" onclick="receiveRappel()">
      <img src="images/IconeAgenda.png" alt="Agenda">
      <span>Recevoir les rappels</span>
    </div>
    <div class="menuItem" onclick="receiveMessages()">
      <img src="images/iconeMessage.png" alt="Messages">
      <span>Recevoir les messages</span>
    </div>
    <div class="menuItem" onclick="receiveEvent()">
      <img src="images/IconeEvent.png" alt="Evénements">
      <span>Recevoir les événements</span>
    </div>
  <?php endif; ?>
</div>

<!-- Notification -->
<?php if (count($notifs) > 0): ?>
    <div id="notif-bubble" class="notif-bubble" style="display:flex;">
        <img src="images/IconeRappel.png" alt="🔔" class="notif-bubble-icon">
        <div id="notif-bubble-text" class="notif-bubble-text">
            <?= htmlspecialchars($notifs[0]['content'], ENT_QUOTES) ?>
        </div>
        <!-- Passer l'ID de la notification dans l'attribut data-notif-id -->
        <button id="mark-as-read-button" class="notif-button" data-notif-id="<?= $notifs[0]['id'] ?>">Lire</button>
    </div>
<?php else: ?>
    <div id="notif-bubble" class="notif-bubble" style="display:none;">
        <div class="notif-bubble-text">Aucune nouvelle notification</div>
    </div>
<?php endif; ?>

<script>

const ws = new WebSocket('ws://localhost:8080');

ws.onmessage = function(e) {
    const data = JSON.parse(e.data);
    if(data.type === 'message') {
        showMessageAlert(data);
    } else if(data.type === 'read_confirmation') {
        showReadConfirmation(data);
    }
};

function showMessageAlert(message) {
    const bubble = document.getElementById("notif-bubble");
    bubble.innerHTML = `
        <p>Nouveau message de ${message.sender_name}</p>
        <button onclick="markAsRead(${message.id})">Lire</button>
    `;
    bubble.style.display = 'block';
}

async function markAsRead(messageId) {
    const response = await fetch('index.php?controller=message&action=markAsRead', {
        method: 'POST',
        body: JSON.stringify({ message_id: messageId })
    });
    
    if(response.ok) {
        ws.send(JSON.stringify({
            type: 'read_confirmation',
            message_id: messageId,
            reader_id: <?= $_SESSION['user_id'] ?>
        }));
    }
}


  // Fonction pour gérer l'affichage des notifications
   // Fonction pour gérer l'affichage des notifications
   function showNotif(message) {
    const bubble = document.getElementById("notif-bubble");
    const text = document.getElementById("notif-bubble-text");
    const audio = new Audio('audio/notif-sound.mp3'); // Chemin vers le son de notification

    text.textContent = message;
    bubble.style.display = "flex"; // Affiche la bulle de notification

    audio.play().catch(e => console.warn("🔇 Son bloqué :", e)); // Joue le son de notification

    // Lecture vocale du message
    const msg = new SpeechSynthesisUtterance(message);
    msg.lang = 'fr-FR';
    window.speechSynthesis.speak(msg);
  }

// Fonction pour marquer la notification comme lue
const markAsReadBtn = document.getElementById('mark-as-read-button');
if (markAsReadBtn) {
    markAsReadBtn.addEventListener('click', function() {
        const notifId = this.dataset.notifId;  // Récupérer l'ID de la notification depuis l'attribut data
        markNotificationAsRead(notifId);       // Passer l'ID à la fonction qui marque la notification comme lue
    });
}

// Fonction pour marquer la notification comme lue côté serveur
function markNotificationAsRead(notifId) {
  fetch('index.php?controller=notification&action=markNotificationAsRead', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ notif_id: notifId })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const notifBubble = document.getElementById("notif-bubble");
        notifBubble.style.display = "none";

        // Afficher les détails associés
        const details = data.data;
        if (details.type === "photo") {
          // Afficher une photo
          const img = document.createElement("img");
          img.src = details.url;
          img.alt = "Photo envoyée";
          img.style.maxWidth = "100%";
          document.body.appendChild(img);

          alert(`Message associé : ${details.message}`);
        } else if (details.type === "event") {
          alert(`Événement : ${details.title}\nDescription : ${details.description}`);
        } else if (details.type === "message") {
          alert(`Message reçu : ${details.content}`);
        }
      } else {
        alert('Erreur : ' + data.error);
      }
    })
    .catch(error => console.error('Erreur:', error));
}


// Vérification des notifications toutes les 5 secondes
// Modifier cette section dans le setInterval de dashboard.php
setInterval(() => {
  fetch('index.php?controller=notification&action=getUserNotifications')
    .then(res => res.json())
    .then(data => {
      // Affiche uniquement si des notifications non lues existent
      if (data && data.length > 0) {
        // Vérification que la notification n'est pas déjà affichée
        const currentNotifId = document.querySelector('#mark-as-read-button')?.dataset?.notifId;
        if (!currentNotifId || currentNotifId != data[0].id) {
          showNotif(data[0].content);
          
          // Mettre à jour l'ID dans le bouton
          const button = document.querySelector('#mark-as-read-button');
          if (button) button.dataset.notifId = data[0].id;
        }
      } else {
        // Cacher la notification s'il n'y en a plus
        document.getElementById("notif-bubble").style.display = 'none';
      }
    })
    .catch(err => console.error('Erreur:', err));
}, 5000);

</script>

</body>
</html>
