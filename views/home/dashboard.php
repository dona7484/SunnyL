<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php?controller=auth&action=login');
  exit;
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
      width: 80%;
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

      <div class="dashboard-item" onclick="window.location.href='index.php?controller=spotify&action=player'" class="dashboard-link">
        <img src="images/iconeMusic.png" alt="Musique">
        <i class="fas fa-music"></i>
        <span>Musique</span>
      </div>

      <div class="menuItem Messages" onclick="openMessages()">
        <img src="images/iconeMessage.png" alt="Messages">
        <span>Messages</span>
      </div>

      <div class="menuItem appels" onclick="window.location.href='index.php?controller=call&action=start'">
  <img src="images/IconeTel.jpg" alt="Appels">
  <span>Appels vidéo</span>
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

  <script src="js/websocket.js"></script>
<script src="js/notifications.js"></script>
<script src="/SunnyLink/public/js/global-notifications.js"></script>
<script src="/SunnyLink/public/js/main.js"></script>
<script src="/SunnyLink/public/js/websocket.js"></script>

<script>
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

        // ID utilisateur pour WebSocket
        const userId = <?= $_SESSION['user_id'] ?>;
        console.log('ID utilisateur pour WebSocket:', userId);
        
        // Connexion WebSocket
        if (typeof sunnyLinkWS !== 'undefined') {
            sunnyLinkWS.connect(userId);
            
            // Gérer les messages reçus via WebSocket
            sunnyLinkWS.onMessage(function(data) {
                console.log('Message WebSocket reçu:', data);
                
                // Afficher une notification pour le nouveau message
                if (data.type === 'message' || data.type === 'audio') {
                    showNotification(data.type, data.content || 'Nouveau message');
                    
                    // Jouer le son de notification
                    playNotificationSound();
                }
            });
        }

        // Initialiser les notifications
        if (typeof initNotifications === 'function') {
            initNotifications();
            console.log("Notifications initialisées avec succès");
        } else {
            console.error("La fonction initNotifications n'est pas définie. Vérifiez que le fichier notifications.js est correctement chargé.");
        }
        
        // Afficher la bulle si des notifications existent au chargement
        const bubble = document.getElementById("notif-bubble");
        if (bubble && <?= count($notifs) > 0 ? 'true' : 'false' ?>) {
            bubble.style.display = "flex";
        }
        
        // Gestion du bouton de lecture des notifications
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
        
        // Fonction pour afficher une notification
        function showNotification(type, message) {
            const bubble = document.getElementById('notif-bubble');
            const bubbleText = document.getElementById('notif-bubble-text');
            const markAsReadBtn = document.getElementById('mark-as-read-button');
            
            if (bubble && bubbleText) {
                bubbleText.textContent = message;
                
                // Mettre à jour l'icône en fonction du type
                const bubbleIcon = bubble.querySelector('.notif-bubble-icon');
                if (bubbleIcon) {
                    if (type === 'message') {
                        bubbleIcon.src = 'images/iconeMessage.png';
                    } else if (type === 'audio') {
                        bubbleIcon.src = 'images/iconeMusic.png';
                    } else if (type === 'photo') {
                        bubbleIcon.src = 'images/IconePhoto.png';
                    } else {
                        bubbleIcon.src = 'images/IconeRappel.png';
                    }
                }
                
                // Mettre à jour le type de notification
                const typeLabel = bubble.querySelector('.notif-type-label');
                if (typeLabel) {
                    if (type === 'message') {
                        typeLabel.textContent = 'Nouveau message';
                    } else if (type === 'audio') {
                        typeLabel.textContent = 'Nouveau message audio';
                    } else if (type === 'photo') {
                        typeLabel.textContent = 'Nouvelle photo';
                    } else {
                        typeLabel.textContent = 'Nouvelle notification';
                    }
                }
                
                // Afficher la bulle
                bubble.style.display = 'flex';
                
                // Mettre à jour le timestamp
                const timestamp = bubble.querySelector('.notif-timestamp');
                if (timestamp) {
                    timestamp.textContent = 'À l\'instant';
                }
            }
        }
        
        // Fonction pour jouer le son de notification
        function playNotificationSound() {
            const audio = document.getElementById('notification-sound');
            if (audio) {
                audio.volume = 0.5; // Volume à 50%
                audio.play().catch(e => {
                    console.warn("Impossible de jouer le son de notification:", e);
                });
            }
        }
    });

    // Fonctions de navigation
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
    window.location.href = 'index.php?controller=call&action=start';
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
</script>

</body>
</html>
