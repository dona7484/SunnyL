<!DOCTYPE html>
<html lang="fr" <?= isset($_SESSION['user_id']) ? 'data-user-id="'.$_SESSION['user_id'].'" data-user-role="'.(isset($_SESSION['role']) ? $_SESSION['role'] : 'unknown').'"' : '' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SunnyLink' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/SunnyLink/public/css/styles.css">
    <link rel="stylesheet" href="/SunnyLink/public/css/notifications.css">
    
    <!-- Précharger les sons -->
    <link rel="preload" href="/SunnyLink/public/audio/notif-sound.mp3" as="audio">
    
    <!-- Méta-informations pour PWA -->
    <meta name="theme-color" content="#FFD700">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SunnyLink">
</head>
<body <?= isset($_SESSION['user_id']) ? 'data-user-id="'.$_SESSION['user_id'].'" data-user-role="'.(isset($_SESSION['role']) ? $_SESSION['role'] : 'unknown').'"' : '' ?>>
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role'])): ?>
    <?php if ($_SESSION['role'] === 'senior'): ?>
  
    <?php elseif ($_SESSION['role'] === 'familymember'): ?>
    <!-- Barre de navigation pour les membres de la famille avec les badges de notification -->
    <div class="family-navbar">
        <a href="/SunnyLink/public/index.php?controller=home&action=family_dashboard" class="family-nav-item">
            <i class="fas fa-home"></i>
            <span>Accueil</span>
        </a>
        <a href="/SunnyLink/public/index.php?controller=message&action=received" class="family-nav-item">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
            <div id="message-badge" class="notification-badge" style="display: none;">0</div>
        </a>
        <a href="/SunnyLink/public/index.php?controller=photo&action=gallery" class="family-nav-item">
            <i class="fas fa-image"></i>
            <span>Photos</span>
            <div id="photo-badge" class="notification-badge" style="display: none;">0</div>
        </a>
        <a href="/SunnyLink/public/index.php?controller=event&action=index" class="family-nav-item">
            <i class="fas fa-calendar"></i>
            <span>Événements</span>
            <div id="event-badge" class="notification-badge" style="display: none;">0</div>
        </a>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Conteneur principal -->
    <div class="content-wrapper">
        <?= $content ?? '' ?>
    </div>
    
    <!-- Conteneur de notifications (sera rempli par JavaScript) -->
    <div id="notifications-container"></div>
    
    <!-- Précharge audio pour les notifications -->
    <audio id="notification-sound" preload="auto" style="display: none;">
        <source src="/SunnyLink/public/audio/notif-sound.mp3" type="audio/mpeg">
    </audio>

    <!-- Scripts JavaScript communs -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Système global de notifications -->
    <script src="/SunnyLink/public/js/global-notifications.js"></script>
    
    <!-- Scripts spécifiques à l'application -->
    <script src="/SunnyLink/public/js/main.js"></script>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Websocket pour les messages en temps réel (uniquement pour les utilisateurs connectés) -->
    <script src="/SunnyLink/public/js/websocket.js"></script>
    <script>
        // Initialiser la connexion WebSocket si l'utilisateur est connecté
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof sunnyLinkWS !== 'undefined') {
                sunnyLinkWS.connect(<?= $_SESSION['user_id'] ?>);
                
                // Mettre à jour les badges de notification toutes les 30 secondes
                setInterval(updateNotificationBadges, 30000);
                
                // Et au chargement de la page
                updateNotificationBadges();
            }
            
            // Activer les sons au clic sur la page
            document.body.addEventListener('click', function activateSound() {
                const audio = document.getElementById('notification-sound');
                if (audio) {
                    audio.volume = 0.1;
                    audio.play().then(() => {
                        console.log("Audio activé avec succès");
                        document.body.removeEventListener('click', activateSound);
                    }).catch(e => {
                        console.warn("Activation du son échouée :", e);
                    });
                }
            }, { once: true });
        });
        
        // Fonction pour mettre à jour les badges de notification
        function updateNotificationBadges() {
            fetch('/SunnyLink/public/index.php?controller=notification&action=getUserNotifications')
                .then(response => response.json())
                .then(notifications => {
                    if (!Array.isArray(notifications)) return;
                    
                    // Compteurs par type
                    let messageBadge = document.getElementById('message-badge');
                    let photoBadge = document.getElementById('photo-badge');
                    let eventBadge = document.getElementById('event-badge');
                    
                    // Réinitialiser les compteurs
                    let messageCount = 0;
                    let photoCount = 0;
                    let eventCount = 0;
                    
                    // Compter les notifications par type
                    notifications.forEach(notif => {
                        if (notif.type === 'message' || notif.type === 'audio') {
                            messageCount++;
                        } else if (notif.type === 'photo') {
                            photoCount++;
                        } else if (notif.type === 'event') {
                            eventCount++;
                        }
                    });
                    
                    // Mettre à jour les badges
                    if (messageBadge) {
                        messageBadge.textContent = messageCount;
                        messageBadge.style.display = messageCount > 0 ? 'flex' : 'none';
                    }
                    
                    if (photoBadge) {
                        photoBadge.textContent = photoCount;
                        photoBadge.style.display = photoCount > 0 ? 'flex' : 'none';
                    }
                    
                    if (eventBadge) {
                        eventBadge.textContent = eventCount;
                        eventBadge.style.display = eventCount > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour des badges:', error);
                });
        }
    </script>
    <?php endif; ?>
    
    <!-- Styles communs pour la barre de navigation -->
    <style>
        .content-wrapper {
            padding-bottom: 70px; /* Espace pour la barre de navigation */
        }
        
        /* Styles de navigation communs */
        .family-navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #fff;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .family-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #333;
            padding: 8px 15px;
            border-radius: 10px;
            position: relative;
        }
   
        
  
        /* Style spécifique pour les seniors (plus grand) */
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'senior'): ?>
        html {
            font-size: 110%; /* Police plus grande pour les seniors */
        }
        
     
        <?php endif; ?>
        
        /* Style spécifique pour les family members */
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'familymember'): ?>
        .family-navbar {
            background-color: #f8f9fa; /* Légèrement différent pour distinguer */
            border-top: 2px solid #FFD700; /* Bordure dorée pour SunnyLink */
        }
        
        .family-nav-item {
            font-weight: 500;
        }
        <?php endif; ?>
    </style>
</body>
</html>