<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// echo "<p style='color:red;'>🔍 SESSION user_id = " . ($_SESSION['user_id'] ?? 'non défini') . "</p>";

// Ajouter une vérification des notifications actuelles
$notifModel = new NotificationModel();
$notifs = $notifModel->getUnreadNotifications($_SESSION['user_id'] ?? 0);
// echo "<p style='color:blue;'>Notifications non lues: " . count($notifs) . "</p>";
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Senior - SunnyLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
<style>
body {
            font-family: Arial, sans-serif;
            background-color:rgb(255, 255, 255);
            margin: 0;
            padding: 0;
        }
        #header {
            background-color: #FFD700;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #header img {
            width: 30px;
            cursor: pointer;
        }
        #dashboardContainer {
            display: flex;
            height: 100vh; /* Prend toute la hauteur de l'écran */
        }
        .leftSection {
            flex: 2; /* Occupe tout l'espace disponible à gauche */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color:rgb(242, 239, 244); /* Couleur de fond */
        }

        .photoSunnylink {
            width: 100%; /* Ajustez selon vos besoins */
            height: auto;
        }

        .rightSection {
            flex: 2; /* Occupe deux fois plus d'espace que la section gauche */
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Deux colonnes pour les boutons */
            gap: 20px;
            padding: 20px;
            background-color:rgb(242, 239, 244); /* Couleur de fond */
        }

        .menuItem {
            width: 140px; /* Agrandissez si nécessaire */
            height: 140px; /* Agrandissez si nécessaire */
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
        }

        .menuItem img {
            width: 60px; /* Ajustez la taille des icônes */
        }

        .menuItem span {
            margin-top: .5rem;
        }

        /* Couleurs d'arrière-plan pour chaque bouton */
        .Photos { background-color: #87CEEB; }
        .musique { background-color: #FFD700; }
        .Messages { background-color: #FFB6C1; }
        .appels { background-color: #ADD8E6; }
        .agenda { background-color: #DDA0DD; }
        .rappels { background-color: #98FB98; }
        .menuItem:hover {
            transform: scale(1.05); /* Agrandit légèrement au survol */
            transition: transform 0.2s;
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
    .oldPerson {
  grid-column: span 1; /* L'image occupe une seule colonne */
  display: flex;
  justify-content: flex-start; /* Aligner à gauche */
  align-items: center; /* Centrer verticalement si nécessaire */
}

  </style>
</head>
<body>


<div id="header">
    <div>
        <img src="images/IconeSourdine.png" alt="Volume" onclick="toggleVolume()">
        <span>SunnyLink</span>
    </div>
    <button id="monCompteBtn" class="btn btn-outline-dark">Mon compte</button>
</div>
<div id="dashboardContainer">
    <div class="leftSection">
        <img src="images/OldPerson.jpg" alt="SunnyLink" class="photoSunnylink">
    </div>

    <!-- Boutons à droite -->
    <div class="rightSection">
        <!-- Bouton Photos -->
        <div class="menuItem Photos" onclick="openPhotos()">
            <img src="images/IconePhoto.png" alt="Photos">
            <span>Photos</span>
        </div>

        <!-- Bouton Musique -->
        <div class="menuItem musique" onclick="openSpotify()">
            <img src="images/iconeMusic.png" alt="Musique">
            <span>Musique</span>
        </div>

        <!-- Bouton Messages -->
        <div class="menuItem Messages" onclick="openMessages()">
            <img src="images/iconeMessage.png" alt="Messages">
            <span>Messages</span>
        </div>

        <!-- Bouton Appels -->
        <div class="menuItem appels" onclick="openCalls()">
            <img src="images/IconeTel.jpg" alt="Appels">
            <span>Appels</span>
        </div>

        <!-- Bouton Agenda -->
        <div class="menuItem agenda" onclick="openAgenda()">
            <img src="images/iconeAgenda.png" alt="Agenda">
            <span>Agenda</span>
        </div>

        <!-- Bouton Rappels -->
        <div class="menuItem rappels" onclick="openReminders()">
            <img src="images/IconeRappel.png" alt="Rappels">
            <span>Rappels</span>
        </div>
    </div>
</div>
<div id="notif-bubble" class="notif-bubble" style="display:none;">
    <img src="images/IconeRappel.png" alt="">
    <span id="notif-text"></span>
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
function openPhotos() {
    window.location.href = 'index.php?controller=photo&action=gallery';
}

function openSpotify() {
    window.open('https://open.spotify.com', '_blank');
}

function openMessages() {
    window.location.href = 'index.php?controller=message&action=received';
}

function openCalls() {
    alert("La fonctionnalité d'appels n'est pas encore disponible.");
}

function openAgenda() {
    window.location.href = 'index.php?controller=event&action=index';
}

function openReminders() {
    window.location.href = 'index.php?controller=notification&action=index';
}
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
