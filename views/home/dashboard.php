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

// Récupérer les activités récentes pour l'historique
require_once __DIR__ . '/../../models/Activity.php';
$activities = Activity::getRecentActivities($_SESSION['user_id'], 10);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Senior - SunnyLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    .historique { background-color: #ADD8E6; }
    .agenda { background-color: #DDA0DD; }
    .manquees { background-color: #98FB98; }
    
    .menuItem:hover {
      transform: scale(1.05);
    }
    
    /* Styles améliorés pour la bulle de notification */
.notif-bubble {
    position: fixed;
    top: 20%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #fff;
    border-left: 5px solid #ffc107;
    border-radius: 12px;
    padding: 25px 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    z-index: 10000; /* Augmenté pour être au-dessus du diaporama */
    width: 80%;
    max-width: 600px;
    transition: all 0.3s ease;
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
    
    keyframes notification-in {
    0% { opacity: 0; transform: translate(-50%, -30px); }
    100% { opacity: 1; transform: translate(-50%, 0); }
}

/* Animation de sortie pour les notifications */
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
 Lorsqu'une notification est affichée pendant le diaporama, appliquer ces styles spéciaux */
.notif-bubble.over-slideshow {
    background-color: rgba(255, 255, 255, 0.95); /* Plus opaque */
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
    border-width: 5px; /* Bordure plus épaisse */
    transform: translate(-50%, 0) scale(1.05); /* Légèrement plus grand */
}

/* Conteneur de diaporama lorsqu'une notification est affichée */
.slideshow-container.notification-active {
    opacity: 0.3 !important; /* Forcer une faible opacité */
    filter: blur(2px); /* Ajouter un flou */
}

/* Le bouton de notification doit être plus visible pendant le diaporama */
.notif-bubble.over-slideshow .notif-button {
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
}

/* Au survol du bouton de notification */
.notif-button:hover {
    transform: scale(1.15) !important;
    background-color: #45a049;
}

/* Styles pour améliorer la lisibilité du texte de notification sur le diaporama */
.notif-bubble.over-slideshow .notif-bubble-text {
    font-size: 26px;
    text-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
}

/* Animation de pulsation pour attirer l'attention sur la notification */
@keyframes pulse-attention {
    0% { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    50% { box-shadow: 0 8px 30px rgba(255, 193, 7, 0.4); }
    100% { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
}

.notif-bubble.over-slideshow {
    animation: pulse-attention 2s infinite;
}

    .notif-timestamp {
      font-size: 14px;
      color: #888;
      margin-top: 5px;
      font-style: italic;
    }
    
    /* Styles pour la modal d'historique */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 10% auto;
      padding: 20px;
      border-radius: 15px;
      width: 80%;
      max-width: 700px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .close-btn {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close-btn:hover {
      color: black;
    }

    .activity-item {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
    }

    .activity-icon {
      width: 40px;
      height: 40px;
      background-color: #f8f9fa;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 18px;
    }

    .activity-content {
      flex-grow: 1;
    }

    .activity-time {
      color: #888;
      font-size: 14px;
    }
    
    /* Style spécifique pour les notifications manquées */
    .missed-notification {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
    }

    .notification-content {
      flex-grow: 1;
    }

    .notification-action {
      margin-left: 10px;
    }

    .slideshow-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    #slideshow-image {
        max-width: 90%;
        max-height: 80%;
        object-fit: contain;
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        transition: opacity 0.3s ease;
    }
    
    #slideshow-close {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: white;
        background: none;
        border: none;
        cursor: pointer;
    }
    
    #slideshow-caption {
        position: absolute;
        bottom: 50px;
        left: 0;
        width: 100%;
        text-align: center;
        color: white;
        font-size: 24px;
        padding: 10px;
        background-color: rgba(0, 0, 0, 0.5);
    }
    #voice-control-btn.active {
    background-color: #dc3545; /* Rouge quand actif */
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

#voice-status {
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    font-family: 'Arial', sans-serif;
    text-align: center;
}
  </style>
</head>
<body>
<div id="header">
  <div>
    <img src="images/IconeSourdine.png" alt="Volume" onclick="toggleVolume()">
    <span>SunnyLink</span>
  </div>
  <div class="d-flex align-items-center justify-content-end" style="gap: 16px;">
  <button id="enable-sound" class="btn btn-primary">
    <i class="fas fa-volume-up"></i> Activer les sons
  </button>
  
  <button id="voice-control-btn" class="btn btn-success">
    <i class="fas fa-microphone"></i> Contrôle vocal
  </button>
  
  <a href="index.php?controller=parametres&action=index" class="btn btn-settings">
    <i class="fa fa-gear fa-2x text-dark" style="vertical-align: middle;"></i>
  </a>
  
  <a href="index.php?controller=auth&action=logout" class="btn btn-outline-danger">
    <i class="fas fa-sign-out-alt"></i> Déconnexion
  </a>
</div>
  <div class="d-flex align-items-center justify-content-end" style="gap: 16px;">
  <a href="index.php?controller=parametres&action=index" class="btn btn-settings">
    <i class="fa fa-gear fa-2x text-dark" style="vertical-align: middle;"></i>
</a>
    <a href="index.php?controller=auth&action=logout" class="btn btn-outline-danger">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</div>

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

    <div class="menuItem musique" onclick="window.location.href='index.php?controller=spotify&action=player'">
      <img src="images/iconeMusic.png" alt="Musique">
      <span>Musique</span>
    </div>

    <div class="menuItem Messages" onclick="openMessages()">
      <img src="images/iconeMessage.png" alt="Messages">
      <span>Messages</span>
    </div>

    <div class="menuItem historique" onclick="openHistorique()">
      <img src="images/iconeAgenda.png" alt="Historique">
      <span>Historique</span>
    </div>

    <div class="menuItem agenda" onclick="openAgenda()">
      <img src="images/iconeAgenda.png" alt="Agenda">
      <span>Agenda</span>
    </div>

    <div class="menuItem manquees" onclick="openMissedNotifications()">
      <img src="images/IconeRappel.png" alt="Notifications manquées">
      <span>Notifications manquées</span>
    </div>
  </div>
</div>

<!-- Modal pour Historique -->
<div id="historiqueModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeHistorique()">&times;</span>
    <h2>Historique de vos activités</h2>
    <div id="historiqueContent">
      <?php if (empty($activities)): ?>
        <p class="text-center text-muted">Aucune activité récente</p>
      <?php else: ?>
        <?php foreach ($activities as $activity): ?>
          <div class="activity-item">
            <div class="activity-icon">
              <?php
              $icon = 'fas fa-check-circle';
              
              switch ($activity['type']) {
                case 'message':
                  echo '<i class="fas fa-envelope"></i>';
                  break;
                case 'photo':
                  echo '<i class="fas fa-image"></i>';
                  break;
                case 'event':
                  echo '<i class="fas fa-calendar"></i>';
                  break;
                case 'audio':
                  echo '<i class="fas fa-microphone"></i>';
                  break;
                default:
                  echo '<i class="fas fa-check-circle"></i>';
              }
              ?>
            </div>
            <div class="activity-content">
              <div><?= htmlspecialchars($activity['content']) ?></div>
              <div class="activity-time"><?= date('d/m/Y à H:i', strtotime($activity['created_at'])) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal pour Notifications Manquées -->
<div id="missedNotificationsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeMissedNotifications()">&times;</span>
    <h2>Notifications manquées</h2>
    <div id="missedNotificationsContent">
      <?php if (empty($notifs)): ?>
        <p class="text-center text-muted">Aucune notification manquée</p>
      <?php else: ?>
        <?php foreach ($notifs as $notif): ?>
          <div class="missed-notification">
            <div class="notification-content">
              <h5>
                <?php
                switch($notif['type']) {
                  case 'message':
                    echo '<i class="fas fa-envelope"></i> Message';
                    break;
                  case 'audio':
                    echo '<i class="fas fa-microphone"></i> Message audio';
                    break;
                  case 'photo':
                    echo '<i class="fas fa-image"></i> Photo';
                    break;
                  case 'event':
                    echo '<i class="fas fa-calendar"></i> Événement';
                    break;
                  default:
                    echo '<i class="fas fa-bell"></i> Notification';
                }
                ?>
              </h5>
              <p><?= htmlspecialchars($notif['content']) ?></p>
              <div class="activity-time"><?= date('d/m/Y à H:i', strtotime($notif['created_at'])) ?></div>
            </div>
            <div class="notification-action">
              <button class="btn btn-primary btn-sm view-notification" 
                      data-id="<?= $notif['id'] ?>" 
                      data-type="<?= $notif['type'] ?>" 
                      data-related="<?= $notif['related_id'] ?>">
                Voir
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
<div id="slideshow-container" class="slideshow-container">
    <img id="slideshow-image" src="" alt="Photo du diaporama">
    <button id="slideshow-close">&times;</button>
    <div id="slideshow-caption"></div>
</div>
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
        
        // Ajouter les gestionnaires d'événements pour les boutons de vue de notification dans la modal
        document.querySelectorAll('.view-notification').forEach(button => {
            button.addEventListener('click', function() {
                const notifId = this.dataset.id;
                const type = this.dataset.type;
                const relatedId = this.dataset.related;
                
                // Marquer la notification comme lue
                markNotificationAsRead(notifId, type, relatedId);
                
                // Fermer la modal
                closeMissedNotifications();
            });
        });
        
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

    function openMessages() {
        window.location.href = 'index.php?controller=message&action=received';
    }

    function openAgenda() {
        window.location.href = 'index.php?controller=event&action=index';
    }
    
    // Nouvelles fonctions pour les modales
    function openHistorique() {
        document.getElementById('historiqueModal').style.display = 'block';
    }
    
    function closeHistorique() {
        document.getElementById('historiqueModal').style.display = 'none';
    }
    
    function openMissedNotifications() {
        document.getElementById('missedNotificationsModal').style.display = 'block';
    }
    
    function closeMissedNotifications() {
        document.getElementById('missedNotificationsModal').style.display = 'none';
    }
    
    function toggleVolume() {
        alert("Fonctionnalité de volume en cours de développement");
    }
    
    // Fermer les modales si on clique ailleurs
    window.onclick = function(event) {
        const historiqueModal = document.getElementById('historiqueModal');
        const missedNotificationsModal = document.getElementById('missedNotificationsModal');
        
        if (event.target == historiqueModal) {
            historiqueModal.style.display = 'none';
        }
        
        if (event.target == missedNotificationsModal) {
            missedNotificationsModal.style.display = 'none';
        }
    }
    
    // Fonction pour marquer une notification comme lue
    function markNotificationAsRead(notifId, type, relatedId) {
        fetch('index.php?controller=notification&action=markNotificationAsRead', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notif_id: notifId,
                type: type,
                related_id: relatedId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Rediriger en fonction du type
                redirectBasedOnType(type, relatedId);
            }
        })
        .catch(error => {
            console.error('Erreur lors du marquage de la notification comme lue:', error);
        });
    }
    
    // Fonction pour rediriger en fonction du type de notification
    function redirectBasedOnType(type, relatedId) {
        switch (type) {
            case 'message':
            case 'audio':
                window.location.href = 'index.php?controller=message&action=received';
                break;
            case 'photo':
                window.location.href = 'index.php?controller=photo&action=gallery';
                break;
            case 'event':
                if (relatedId) {
                    window.location.href = 'index.php?controller=event&action=show&id=' + relatedId;
                } else {
                    window.location.href = 'index.php?controller=event&action=index';
                }
                break;
            default:
                // Recharger la page pour mettre à jour la liste des notifications
                window.location.reload();
        }
    }
</script>

<script src="/SunnyLink/public/js/slideshow.js"></script>
<!-- Ajoutez ce code juste avant la fermeture de la balise </body> dans views/home/dashboard.php -->
<script>
// Script de diaporama en ligne pour le débogage
class SlideshowManager {
    constructor(options = {}) {
        // Options par défaut
        this.options = {
            inactivityTime: 60000, // Temps d'inactivité avant le lancement du diaporama (1 minute par défaut)
            slideDuration: 5000, // Durée d'affichage de chaque image (5 secondes par défaut)
            containerId: 'slideshow-container', // ID du conteneur pour le diaporama
            fetchUrl: 'index.php?controller=photo&action=getAllForSlideshow', // URL pour récupérer les photos
            ...options // Fusion avec les options fournies
        };

        // État interne
        this.inactivityTimer = null;
        this.slideshowTimer = null;
        this.isActive = false;
        this.photos = [];
        this.currentPhotoIndex = 0;
        
        // Créer le conteneur de diaporama s'il n'existe pas
        this.createSlideshowContainer();
        
        // Lier les méthodes au contexte actuel
        this.resetInactivityTimer = this.resetInactivityTimer.bind(this);
        this.startSlideshow = this.startSlideshow.bind(this);
        this.stopSlideshow = this.stopSlideshow.bind(this);
        this.showNextPhoto = this.showNextPhoto.bind(this);
        this.loadPhotos = this.loadPhotos.bind(this);
    }

    // Initialiser le système de diaporama
    init() {
        console.log('Initialisation du système de diaporama...');
        
        // Événements pour détecter l'activité de l'utilisateur
        document.addEventListener('mousemove', this.resetInactivityTimer);
        document.addEventListener('mousedown', this.resetInactivityTimer);
        document.addEventListener('keypress', this.resetInactivityTimer);
        document.addEventListener('touchstart', this.resetInactivityTimer);
        document.addEventListener('scroll', this.resetInactivityTimer);
        
        // Événement pour stopper le diaporama lors d'une interaction
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.addEventListener('click', this.stopSlideshow);
        }
        
        // Démarrer le timer d'inactivité
        this.resetInactivityTimer();
        
        // Charger les photos initialement
        this.loadPhotos();
        
        console.log('Système de diaporama initialisé');
    }

    // Créer le conteneur pour le diaporama
    createSlideshowContainer() {
        if (!document.getElementById(this.options.containerId)) {
            console.log('Création du conteneur de diaporama');
            const container = document.createElement('div');
            container.id = this.options.containerId;
            container.className = 'slideshow-container';
            container.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.9);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.5s ease;
            `;
            
            // Ajouter un élément pour afficher l'image
            const imgElement = document.createElement('img');
            imgElement.id = 'slideshow-image';
            imgElement.style.cssText = `
                max-width: 90%;
                max-height: 80%;
                object-fit: contain;
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
                border-radius: 8px;
                transition: opacity 0.3s ease;
            `;
            container.appendChild(imgElement);
            
            // Ajouter un bouton de fermeture
            const closeButton = document.createElement('button');
            closeButton.id = 'slideshow-close';
            closeButton.innerHTML = '&times;';
            closeButton.style.cssText = `
                position: absolute;
                top: 20px;
                right: 30px;
                font-size: 40px;
                color: white;
                background: none;
                border: none;
                cursor: pointer;
            `;
            closeButton.addEventListener('click', this.stopSlideshow);
            container.appendChild(closeButton);
            
            // Ajouter un élément pour le message/titre
            const captionElement = document.createElement('div');
            captionElement.id = 'slideshow-caption';
            captionElement.style.cssText = `
                position: absolute;
                bottom: 50px;
                left: 0;
                width: 100%;
                text-align: center;
                color: white;
                font-size: 24px;
                padding: 10px;
                background-color: rgba(0, 0, 0, 0.5);
            `;
            container.appendChild(captionElement);
            
            // Ajouter au body
            document.body.appendChild(container);
        }
    }

    // Réinitialiser le timer d'inactivité
    resetInactivityTimer() {
        // Si le diaporama est déjà actif, ne rien faire
        if (this.isActive) return;
        
        // Effacer le timer existant
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
        }
        
        // Définir un nouveau timer
        this.inactivityTimer = setTimeout(() => {
            console.log(`Inactivité détectée après ${this.options.inactivityTime / 1000} secondes`);
            this.startSlideshow();
        }, this.options.inactivityTime);
    }

    // Charger les photos depuis l'API
    loadPhotos() {
        console.log('Chargement des photos pour le diaporama...');
        
        fetch(this.options.fetchUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Réponse API diaporama:', data);
                
                if (Array.isArray(data) && data.length > 0) {
                    this.photos = data;
                    console.log(`${this.photos.length} photos chargées pour le diaporama`);
                } else {
                    console.log('Aucune photo disponible pour le diaporama');
                    setTimeout(this.loadPhotos, 60000); // Réessayer dans 1 minute
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des photos:', error);
                setTimeout(this.loadPhotos, 60000); // Réessayer dans 1 minute en cas d'erreur
            });
    }

    // Démarrer le diaporama
    startSlideshow() {
        console.log('Démarrage du diaporama...');
        
        // Si pas de photos, essayer de les charger à nouveau
        if (this.photos.length === 0) {
            this.loadPhotos();
            console.log('Aucune photo disponible, chargement en cours...');
            
            // Essayer de démarrer après un délai pour laisser le temps de charger
            setTimeout(() => {
                if (this.photos.length > 0) {
                    this.startSlideshow();
                } else {
                    console.log('Démarrage annulé: aucune photo disponible');
                    alert('Aucune photo disponible pour le diaporama.');
                }
            }, 2000);
            return;
        }
        
        this.isActive = true;
        
        // Afficher le conteneur du diaporama
        const container = document.getElementById(this.options.containerId);
        container.style.display = 'flex';
        
        // Animation d'entrée
        setTimeout(() => {
            container.style.opacity = '1';
        }, 10);
        
        // Réinitialiser l'index et afficher la première photo
        this.currentPhotoIndex = 0;
        this.showNextPhoto();
        
        // Démarrer le timer pour faire défiler les photos
        this.slideshowTimer = setInterval(this.showNextPhoto, this.options.slideDuration);
        
        console.log('Diaporama démarré');
    }

    // Arrêter le diaporama
    stopSlideshow() {
        console.log('Arrêt du diaporama...');
        
        if (this.slideshowTimer) {
            clearInterval(this.slideshowTimer);
            this.slideshowTimer = null;
        }
        
        this.isActive = false;
        
        // Animation de sortie
        const container = document.getElementById(this.options.containerId);
        container.style.opacity = '0';
        
        // Cacher le conteneur après l'animation
        setTimeout(() => {
            container.style.display = 'none';
        }, 500);
        
        // Réinitialiser le timer d'inactivité
        this.resetInactivityTimer();
        
        console.log('Diaporama arrêté');
    }

    // Afficher la photo suivante
    showNextPhoto() {
        if (this.photos.length === 0) {
            this.stopSlideshow();
            return;
        }
        
        const photo = this.photos[this.currentPhotoIndex];
        const imgElement = document.getElementById('slideshow-image');
        const captionElement = document.getElementById('slideshow-caption');
        
        // Animation de transition
        imgElement.style.opacity = '0';
        
        // Changer l'image après un court délai
        setTimeout(() => {
            // Mettre à jour l'image
            let imgUrl = photo.url;
            if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('/')) {
                imgUrl = '/' + imgUrl;
            }
            imgElement.src = imgUrl;
            
            // Mettre à jour la légende
            captionElement.textContent = photo.message || '';
            
            // Rendre l'image visible
            imgElement.style.opacity = '1';
        }, 300);
        
        // Passer à l'image suivante
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.photos.length;
    }
}

// Fonction d'initialisation globale
function initSlideshow() {
    console.log('Initialisation du diaporama (inline)...');
    // Créer et initialiser le gestionnaire de diaporama
    const slideshow = new SlideshowManager({
        inactivityTime: 60000, // 1 minute d'inactivité avant démarrage
        slideDuration: 7000, // 7 secondes par photo
        fetchUrl: 'index.php?controller=photo&action=getAllForSlideshow' // URL pour récupérer les photos
    });
    
    slideshow.init();
    
    // Rendre accessible globalement pour le débogage
    window.slideshowManager = slideshow;
    
    return slideshow;
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, initialisation du diaporama...');
    initSlideshow();
});

// Ajouter un événement pour le bouton de test
document.addEventListener('DOMContentLoaded', function() {
    const testBtn = document.getElementById('start-slideshow-test');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            console.log('Test du diaporama demandé...');
            
            // Vérifier si le gestionnaire de diaporama est disponible
            if (window.slideshowManager) {
                console.log('Lancement manuel du diaporama');
                window.slideshowManager.startSlideshow();
            } else {
                console.error('Gestionnaire de diaporama non disponible!');
                alert('Erreur: Le gestionnaire de diaporama n\'est pas initialisé.');
                
                // Tentative de réinitialisation
                window.slideshowManager = initSlideshow();
                
                // Nouvel essai après initialisation
                setTimeout(() => {
                    if (window.slideshowManager) {
                        window.slideshowManager.startSlideshow();
                    } else {
                        alert('Échec de l\'initialisation du diaporama!');
                    }
                }, 1000);
            }
        });
    }
});
</script>

<script src="js/voice-controls.js"></script>
<!-- Bouton de test -->
<div class="diaporama-debug" style="position: fixed; bottom: 20px; right: 20px; z-index: 100;">
    <button id="start-slideshow-test" class="btn btn-info">
        <i class="fas fa-play"></i> Tester Diaporama
    </button>
</div>
</body>
</html>