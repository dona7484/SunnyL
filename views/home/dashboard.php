<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir la clé VAPID publique pour les notifications push
define('VAPID_PUBLIC_KEY', 'BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI');

// Récupération des notifications non lues
$notifModel = new NotificationModel();
$notifs = $notifModel->getUnreadNotifications($_SESSION['user_id'] ?? 0) ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Senior - SunnyLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: rgb(255, 255, 255);
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
      height: 100vh;
    }
    
    .leftSection {
      flex: 2;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgb(242, 239, 244);
    }

    .photoSunnylink {
      width: 100%;
      height: auto;
    }

    .rightSection {
      flex: 2;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      padding: 20px;
      background-color: rgb(242, 239, 244);
    }

    .menuItem {
      width: 140px;
      height: 140px;
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .menuItem img {
      width: 60px;
    }

    .menuItem span {
      margin-top: .5rem;
    }

    .Photos { background-color: #87CEEB; }
    .musique { background-color: #FFD700; }
    .Messages { background-color: #FFB6C1; }
    .appels { background-color: #ADD8E6; }
    .agenda { background-color: #DDA0DD; }
    .rappels { background-color: #98FB98; }
    
    .menuItem:hover {
      transform: scale(1.05);
    }
    
    /* Styles améliorés pour la bulle de notification */
    .notif-bubble {
  position: fixed;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #fff; /* Fond blanc pour plus de clarté */
  border-left: 5px solid #ffc107; /* Bordure latérale colorée */
  border-radius: 12px;
  padding: 25px 30px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15); /* Ombre plus douce et plus profonde */
  z-index: 9999;
  animation: slideInDown 0.5s ease, pulse 2s infinite ease-in-out; /* Animation d'entrée et pulsation légère */
  width: 80%;
  max-width: 600px;
  transition: all 0.3s ease; /* Transition fluide pour toutes les propriétés */
}

.notif-bubble-icon {
  width: 70px;
  height: 70px;
  padding: 10px;
  background-color: rgba(255, 193, 7, 0.1); /* Fond subtil autour de l'icône */
  border-radius: 50%;
  transition: transform 0.3s ease;
}

.notif-bubble-text {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  flex-grow: 1;
  margin-bottom: 10px;
  line-height: 1.4;
}
.notif-button {
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  margin-left: auto;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  transition: transform 0.2s ease, background-color 0.3s ease;
}
.notif-button:hover {
  transform: scale(1.1);
  background-color: #45a049;
}

.notif-button img {
  width: 35px !important;
  height: 35px !important;
}
   /* Animations */
@keyframes slideInDown {
  from { transform: translate(-50%, -50px); opacity: 0; }
  to   { transform: translate(-50%, 0); opacity: 1; }
}

@keyframes pulse {
  0% { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
  50% { box-shadow: 0 8px 25px rgba(0,0,0,0.25); }
  100% { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
}
/* Animation d'entrée */
@keyframes notification-in {
  0% { opacity: 0; transform: translate(-50%, -30px); }
  100% { opacity: 1; transform: translate(-50%, 0); }
}

/* Animation de sortie */
@keyframes notification-out {
  0% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, -30px); }
}

.notification-show {
  animation: notification-in 0.5s forwards;
}

.notification-hide {
  animation: notification-out 0.5s forwards;
}


.notif-type-label {
  font-size: 14px;
  color: #666;
  margin-bottom: 5px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.notif-timestamp {
  font-size: 14px;
  color: #888;
  margin-top: 5px;
  font-style: italic;
}
.senior-audio-messages {
        margin-top: 30px;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .new-message-alert {
        background-color: #ffc107;
        color: #212529;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .audio-message-list {
        margin-bottom: 20px;
    }
    
    .audio-message-item {
        background-color: white;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .audio-message-item audio {
        width: 100%;
        margin: 10px 0;
    }
    
    .audio-recorder-senior {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    @media (min-width: 768px) {
        .audio-recorder-senior {
            flex-direction: row;
        }
    }
  </style>
</head>
<body>
<div id="header">
  <div>
    <img src="images/IconeSourdine.png" alt="Volume" onclick="toggleVolume()">
    <span>SunnyLink</span>
  </div>
  <button id="enable-sound" class="btn btn-primary">Activer les sons de notification</button>
  <button id="monCompteBtn" class="btn btn-outline-dark">Mon compte</button>
</div>

  
  <div id="dashboardContainer">
    <div class="leftSection">
      <img src="images/OldPerson.jpg" alt="SunnyLink" class="photoSunnylink">
    </div>

    <div class="rightSection">
      <div class="menuItem Photos" onclick="openPhotos()">
        <img src="images/IconePhoto.png" alt="Photos">
        <span>Photos</span>
      </div>

      <div class="menuItem musique" onclick="openSpotify()">
        <img src="images/iconeMusic.png" alt="Musique">
        <span>Musique</span>
      </div>

      <div class="menuItem Messages" onclick="openMessages()">
        <img src="images/iconeMessage.png" alt="Messages">
        <span>Messages</span>
      </div>

      <div class="menuItem appels" onclick="openCalls()">
        <img src="images/IconeTel.jpg" alt="Appels">
        <span>Appels</span>
      </div>

      <div class="menuItem agenda" onclick="openAgenda()">
        <img src="images/iconeAgenda.png" alt="Agenda">
        <span>Agenda</span>
      </div>

      <div class="menuItem rappels" onclick="openReminders()">
        <img src="images/IconeRappel.png" alt="Rappels">
        <span>Rappels</span>
      </div>
    </div>
  </div>

  <!-- Bulle de notification améliorée -->
  <div id="notif-bubble" class="notif-bubble" style="display:none;">
  <img src="images/IconeRappel.png" alt="🔔" class="notif-bubble-icon">
  <div style="flex-grow: 1;">
    <div class="notif-type-label">Nouvelle notification</div>
    <div id="notif-bubble-text" class="notif-bubble-text">
      <?php if (count($notifs) > 0): ?>
        <?= htmlspecialchars($notifs[0]['content'], ENT_QUOTES) ?>
      <?php else: ?>
        Aucune nouvelle notification
      <?php endif; ?>
    </div>
    <div class="notif-timestamp">À l'instant</div>
  </div>
  <button id="mark-as-read-button" class="notif-button" 
        data-notif-id="<?= count($notifs) > 0 ? $notifs[0]['id'] : '' ?>" 
        data-type="<?= count($notifs) > 0 ? $notifs[0]['type'] : '' ?>" 
        data-related-id="<?= count($notifs) > 0 ? $notifs[0]['related_id'] : '' ?>">
  <img src="images/check-button.png" alt="Valider" style="width: 35px; height: 35px;">
</button>
</div>

  <!-- Bouton pour activer les sons (caché visuellement mais accessible)
  <button id="enable-sound" style="position: absolute; top: -9999px;">Activer les sons</button> -->

  <!-- Audio préchargé pour les notifications -->
  <audio id="notification-sound" preload="auto" style="display:none;">
    <source src="audio/notif-sound.mp3" type="audio/mpeg">
  </audio>

  <script src="/SunnyLink/js/notifications.js"></script>

  <script>
  // Fonctions de navigation (à conserver dans dashboard.php)
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
  
  function toggleVolume() {
    alert("Fonctionnalité de volume en cours de développement");
  }

  // Initialisation spécifique au dashboard
  document.addEventListener('DOMContentLoaded', function() {
    // Activer le son au premier clic sur la page
    document.body.addEventListener('click', function activateSound() {
      const audio = document.getElementById('notification-sound');
      audio.volume = 0.1; // Volume très bas pour ne pas déranger
      audio.play().then(() => {
        console.log("Audio activé avec succès");
        document.body.removeEventListener('click', activateSound);
      }).catch(e => {
        console.warn("Activation du son échouée :", e);
      });
    }, { once: true });

    // Enregistrement du service worker
    if ('serviceWorker' in navigator && 'PushManager' in window) {
      navigator.serviceWorker.register('/SunnyLink/service-worker.js')
        .then(function(registration) {
          console.log('Service Worker enregistré avec succès');
        })
        .catch(function(error) {
          console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
        });
    }

    // Initialiser les notifications
    if (window.location.search.includes('notifications=enabled') || localStorage.getItem('notificationsEnabled') === 'true') {
    localStorage.setItem('notificationsEnabled', 'true');
    initNotifications();
  }
    
    // Afficher la bulle si des notifications existent au chargement
    const bubble = document.getElementById("notif-bubble");
    if (bubble && <?= count($notifs) > 0 ? 'true' : 'false' ?>) {
      bubble.style.display = "flex";
    }
  });



document.addEventListener('DOMContentLoaded', function() {
  const markAsReadBtn = document.getElementById('mark-as-read-button');
  if (markAsReadBtn) {
    markAsReadBtn.addEventListener('click', function() {
      const notifId = this.dataset.notifId;
      const type = this.dataset.type;
      const relatedId = this.dataset.relatedId;
      
      console.log("Clic sur notification - Données:", {notifId, type, relatedId});
      
      if (notifId) {
        markNotificationAsRead(notifId, type, relatedId);
      }
    });
  }
});

  
</script>
</body>
</html>
