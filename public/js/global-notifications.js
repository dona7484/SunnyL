// global-notifications.js
// Script global pour gérer les notifications sur toutes les pages du site SunnyLink

// Configuration
const NOTIFICATION_CHECK_INTERVAL = 30000; // 30 secondes
let notificationCheckTimer = null;
let currentNotifications = [];
let lastNotificationId = null;

// Initialisation du système de notifications
function initGlobalNotifications() {
    console.log("Initialisation du système de notifications global...");
    
    // Créer les éléments du DOM pour les notifications si nécessaire
    createNotificationElements();
    
    // Vérifier les notifications immédiatement
    checkForNewNotifications();
    
    // Configurer la vérification périodique
    startNotificationTimer();
    
    // Activer le son pour les notifications
    setupNotificationSound();
    
    // Enregistrer la fonction comme globale pour pouvoir l'appeler depuis n'importe où
    window.showNotification = showNotification;
    window.checkForNewNotifications = checkForNewNotifications;
    
    console.log("Système de notifications initialisé avec succès");
}

// Création des éléments DOM pour les notifications s'ils n'existent pas déjà
function createNotificationElements() {
    // Vérifier si les éléments existent déjà
    if (document.getElementById('notif-bubble')) {
        return; // Les éléments existent déjà
    }
    
    console.log("Création des éléments DOM pour les notifications...");
    
    // Créer l'élément audio pour les sons de notification
    const audioElement = document.createElement('audio');
    audioElement.id = 'notification-sound';
    audioElement.preload = 'auto';
    audioElement.style.display = 'none';
    
    const audioSource = document.createElement('source');
    audioSource.src = '/SunnyLink/public/audio/notif-sound.mp3';
    audioSource.type = 'audio/mpeg';
    
    audioElement.appendChild(audioSource);
    document.body.appendChild(audioElement);
    
    // Créer la bulle de notification
    const notifBubble = document.createElement('div');
    notifBubble.id = 'notif-bubble';
    notifBubble.className = 'notif-bubble';
    notifBubble.style.display = 'none';
    
    // Style de base pour la bulle
    notifBubble.style.position = 'fixed';
    notifBubble.style.top = '20%';
    notifBubble.style.left = '50%';
    notifBubble.style.transform = 'translateX(-50%)';
    notifBubble.style.backgroundColor = '#fff';
    notifBubble.style.borderLeft = '5px solid #ffc107';
    notifBubble.style.borderRadius = '12px';
    notifBubble.style.padding = '25px 30px';
    notifBubble.style.display = 'flex';
    notifBubble.style.alignItems = 'center';
    notifBubble.style.gap = '20px';
    notifBubble.style.boxShadow = '0 8px 20px rgba(0,0,0,0.15)';
    notifBubble.style.zIndex = '9999';
    notifBubble.style.width = '80%';
    notifBubble.style.maxWidth = '600px';
    
    // Icône de notification
    const iconElement = document.createElement('img');
    iconElement.className = 'notif-bubble-icon';
    iconElement.src = '/SunnyLink/public/images/IconeRappel.png';
    iconElement.alt = '🔔';
    iconElement.style.width = '70px';
    iconElement.style.height = '70px';
    iconElement.style.padding = '10px';
    iconElement.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
    iconElement.style.borderRadius = '50%';
    
    // Contenu de la notification
    const contentContainer = document.createElement('div');
    contentContainer.style.flexGrow = '1';
    
    // Type de notification
    const typeLabel = document.createElement('div');
    typeLabel.className = 'notif-type-label';
    typeLabel.textContent = 'Nouvelle notification';
    typeLabel.style.fontSize = '14px';
    typeLabel.style.color = '#666';
    typeLabel.style.marginBottom = '5px';
    typeLabel.style.textTransform = 'uppercase';
    typeLabel.style.letterSpacing = '1px';
    
    // Texte de la notification
    const textElement = document.createElement('div');
    textElement.id = 'notif-bubble-text';
    textElement.className = 'notif-bubble-text';
    textElement.textContent = 'Vous avez une nouvelle notification';
    textElement.style.fontSize = '24px';
    textElement.style.fontWeight = '600';
    textElement.style.color = '#333';
    textElement.style.marginBottom = '10px';
    textElement.style.lineHeight = '1.4';
    
    // Timestamp
    const timestamp = document.createElement('div');
    timestamp.className = 'notif-timestamp';
    timestamp.textContent = 'À l\'instant';
    timestamp.style.fontSize = '14px';
    timestamp.style.color = '#888';
    timestamp.style.fontStyle = 'italic';
    
    // Bouton de validation
    const button = document.createElement('button');
    button.id = 'mark-as-read-button';
    button.className = 'notif-button';
    button.setAttribute('data-notif-id', '');
    button.setAttribute('data-type', '');
    button.setAttribute('data-related-id', '');
    button.style.backgroundColor = '#4CAF50';
    button.style.color = 'white';
    button.style.border = 'none';
    button.style.borderRadius = '50%';
    button.style.width = '70px';
    button.style.height = '70px';
    button.style.display = 'flex';
    button.style.justifyContent = 'center';
    button.style.alignItems = 'center';
    button.style.cursor = 'pointer';
    button.style.marginLeft = 'auto';
    button.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
    
    const buttonImg = document.createElement('img');
    buttonImg.src = '/SunnyLink/public/images/check-button.png';
    buttonImg.alt = 'Valider';
    buttonImg.style.width = '35px';
    buttonImg.style.height = '35px';
    
    // Assembler tous les éléments
    button.appendChild(buttonImg);
    contentContainer.appendChild(typeLabel);
    contentContainer.appendChild(textElement);
    contentContainer.appendChild(timestamp);
    
    notifBubble.appendChild(iconElement);
    notifBubble.appendChild(contentContainer);
    notifBubble.appendChild(button);
    
    document.body.appendChild(notifBubble);
    
    // Ajouter l'écouteur d'événement au bouton
    button.addEventListener('click', handleNotificationClick);
    
    console.log("Éléments DOM pour les notifications créés avec succès");
}

// Démarrer le timer pour vérifier les notifications périodiquement
function startNotificationTimer() {
    // Nettoyer l'ancien timer s'il existe
    if (notificationCheckTimer) {
        clearInterval(notificationCheckTimer);
    }
    
    // Configurer un nouveau timer
    notificationCheckTimer = setInterval(checkForNewNotifications, NOTIFICATION_CHECK_INTERVAL);
    console.log(`Timer de vérification des notifications démarré (intervalle: ${NOTIFICATION_CHECK_INTERVAL}ms)`);
}

// Configurer les sons de notification
function setupNotificationSound() {
    // Précharger le son pour éviter les délais
    const audio = document.getElementById('notification-sound');
    if (audio) {
        audio.load();
    }
    
    // Ajouter un écouteur d'événement pour le bouton d'activation du son si présent
    const enableSoundBtn = document.getElementById('enable-sound');
    if (enableSoundBtn) {
        enableSoundBtn.addEventListener('click', function() {
            playNotificationSound(0.1); // Volume bas pour le test
            this.textContent = 'Son activé';
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
        });
    }
}

// Fonction pour jouer le son de notification
function playNotificationSound(volume = 0.5) {
    const audio = document.getElementById('notification-sound');
    if (audio) {
        audio.volume = volume;
        audio.currentTime = 0; // Remettre au début pour rejouer
        
        audio.play().catch(e => {
            console.warn("Impossible de jouer le son de notification:", e);
            // Si le son est bloqué, on peut essayer de demander la permission
            if (e.name === 'NotAllowedError') {
                console.log("L'autoplay est bloqué, demandons la permission au prochain clic utilisateur");
                document.body.addEventListener('click', function enableAudio() {
                    audio.play().catch(err => console.warn("Toujours impossible de jouer le son:", err));
                    document.body.removeEventListener('click', enableAudio);
                }, { once: true });
            }
        });
    }
}

// Fonction pour lire le texte à voix haute
function speakNotification(text) {
    if ('speechSynthesis' in window) {
        // Annuler toute synthèse vocale en cours
        window.speechSynthesis.cancel();
        
        // Créer un nouvel objet de synthèse vocale
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR'; // Français
        utterance.rate = 0.9; // Un peu plus lent pour la clarté
        utterance.pitch = 1;
        utterance.volume = 1;
        
        // Lire le texte
        window.speechSynthesis.speak(utterance);
    }
}

// Vérifier s'il y a de nouvelles notifications
function checkForNewNotifications() {
    console.log("Vérification des nouvelles notifications...");
    
    fetch("/SunnyLink/public/index.php?controller=notification&action=getUserNotifications")
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Vérifier si la réponse est une erreur
            if (data.error) {
                console.warn("Erreur reçue du serveur:", data.error);
                return;
            }
            
            // Traiter les notifications reçues
            handleNotifications(data);
        })
        .catch(error => {
            console.error("Erreur lors de la vérification des notifications:", error);
        });
}

// Traiter les notifications reçues
function handleNotifications(notifications) {
    console.log("Traitement des notifications:", notifications);
    
    // Si pas de notifications ou tableau vide
    if (!notifications || notifications.length === 0) {
        // Masquer la bulle de notification si elle est affichée
        const bubble = document.getElementById('notif-bubble');
        if (bubble && bubble.style.display !== 'none') {
            bubble.style.display = 'none';
        }
        return;
    }
    
    // Mettre à jour la liste des notifications courantes
    currentNotifications = notifications;
    
    // Obtenir la première notification (la plus récente)
    const notification = notifications[0];
    
    // Si c'est une nouvelle notification (différente de la dernière affichée)
    if (notification.id !== lastNotificationId) {
        console.log("Nouvelle notification détectée:", notification);
        lastNotificationId = notification.id;
        
        // Afficher la notification
        showNotification(
            notification.content,
            notification.id,
            notification.type,
            notification.related_id
        );
    }
}

// Afficher une notification dans l'interface
function showNotification(message, notifId, type, relatedId) {
    console.log("Affichage de la notification:", { message, notifId, type, relatedId });
    
    // Récupérer les éléments
    const bubble = document.getElementById('notif-bubble');
    const textElement = document.getElementById('notif-bubble-text');
    const typeLabel = document.querySelector('.notif-type-label');
    const iconElement = document.querySelector('.notif-bubble-icon');
    const button = document.getElementById('mark-as-read-button');
    
    if (!bubble || !textElement) {
        console.error("Éléments de notification non trouvés dans le DOM, tentative de création...");
        createNotificationElements();
        return showNotification(message, notifId, type, relatedId); // Réessayer après création
    }
    
    // Mettre à jour le contenu de la notification
    textElement.textContent = message;
    
    // Mettre à jour le type de notification
    if (typeLabel) {
        switch (type) {
            case 'message':
                typeLabel.textContent = 'Nouveau message';
                break;
            case 'audio':
                typeLabel.textContent = 'Nouveau message audio';
                break;
            case 'photo':
                typeLabel.textContent = 'Nouvelle photo';
                break;
            case 'event':
                typeLabel.textContent = 'Nouvel événement';
                break;
            default:
                typeLabel.textContent = 'Nouvelle notification';
        }
    }
    
    // Mettre à jour l'icône en fonction du type
    if (iconElement) {
        switch (type) {
            case 'message':
                iconElement.src = '/SunnyLink/public/images/iconeMessage.png';
                break;
            case 'audio':
                iconElement.src = '/SunnyLink/public/images/iconeMusic.png';
                break;
            case 'photo':
                iconElement.src = '/SunnyLink/public/images/IconePhoto.png';
                break;
            case 'event':
                iconElement.src = '/SunnyLink/public/images/iconeAgenda.png';
                break;
            default:
                iconElement.src = '/SunnyLink/public/images/IconeRappel.png';
        }
    }
    
    // Mettre à jour les attributs du bouton
    if (button) {
        button.setAttribute('data-notif-id', notifId);
        button.setAttribute('data-type', type || '');
        button.setAttribute('data-related-id', relatedId || '');
    }
    
    // Afficher la bulle avec une animation
    bubble.style.display = 'flex';
    bubble.classList.add('notification-show');
    
    // Jouer le son de notification
    playNotificationSound();
    
    // Lire la notification à voix haute
    speakNotification(message);
}

// Gérer le clic sur le bouton de notification
function handleNotificationClick() {
    const button = document.getElementById('mark-as-read-button');
    if (!button) return;
    
    const notifId = button.getAttribute('data-notif-id');
    const type = button.getAttribute('data-type');
    const relatedId = button.getAttribute('data-related-id');
    
    console.log("Notification marquée comme lue:", { notifId, type, relatedId });
    
    // Cacher la bulle de notification avec animation
    const bubble = document.getElementById('notif-bubble');
    if (bubble) {
        bubble.classList.remove('notification-show');
        bubble.classList.add('notification-hide');
        
        // Pour les messages et audio, récupérer le contenu complet avant de marquer comme lu
        if ((type === 'message' || type === 'audio') && relatedId) {
            // Récupérer le contenu du message à partir de l'API
            fetch(`index.php?controller=message&action=getContent&id=${relatedId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        // Lire le contenu complet du message
                        speakMessage(data.content);
                        console.log("Lecture vocale du message:", data.content);
                    }
                    
                    // Cacher la bulle après l'animation
                    setTimeout(() => {
                        bubble.style.display = 'none';
                        bubble.classList.remove('notification-hide');
                        
                        // Marquer la notification comme lue sur le serveur après lecture
                        markNotificationAsRead(notifId, type, relatedId);
                    }, 500);
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération du contenu du message:", error);
                    
                    // Cacher la bulle et marquer comme lu même en cas d'erreur
                    setTimeout(() => {
                        bubble.style.display = 'none';
                        bubble.classList.remove('notification-hide');
                        markNotificationAsRead(notifId, type, relatedId);
                    }, 500);
                });
        } else {
            // Pour les autres types de notifications, comportement normal
            setTimeout(() => {
                bubble.style.display = 'none';
                bubble.classList.remove('notification-hide');
                
                // Marquer la notification comme lue sur le serveur
                markNotificationAsRead(notifId, type, relatedId);
            }, 500);
        }
    } else {
        // Si la bulle n'existe pas, vérifier quand même le contenu pour messages et audio
        if ((type === 'message' || type === 'audio') && relatedId) {
            // Récupérer le contenu du message à partir de l'API
            fetch(`index.php?controller=message&action=getContent&id=${relatedId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        // Lire le contenu complet du message
                        speakMessage(data.content);
                        console.log("Lecture vocale du message:", data.content);
                    }
                    
                    // Marquer comme lu après lecture
                    markNotificationAsRead(notifId, type, relatedId);
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération du contenu du message:", error);
                    // Marquer comme lu même en cas d'erreur
                    markNotificationAsRead(notifId, type, relatedId);
                });
        } else {
            // Pour les autres types de notifications, marquer directement comme lue
            markNotificationAsRead(notifId, type, relatedId);
        }
    }
}
// Marquer une notification comme lue sur le serveur
function markNotificationAsRead(notifId, type, relatedId) {
    if (!notifId) {
        console.warn("Impossible de marquer la notification comme lue: ID manquant");
        return;
    }
    
    fetch('/SunnyLink/public/index.php?controller=notification&action=markNotificationAsRead', {
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
        console.log("Réponse du serveur pour marquer comme lu:", data);
        
        if (data.success) {
            // Mettre à jour lastNotificationId pour ne pas réafficher cette notification
            if (lastNotificationId === notifId) {
                lastNotificationId = null;
            }
            
            // Rediriger vers la page appropriée selon le type
            redirectBasedOnType(type, relatedId);
        } else {
            console.error("Erreur lors du marquage comme lu:", data.error || "Erreur inconnue");
        }
    })
    .catch(error => {
        console.error("Erreur réseau lors du marquage comme lu:", error);
    });
}

// Rediriger vers la page appropriée selon le type de notification
function redirectBasedOnType(type, relatedId) {
    console.log("Redirection basée sur le type:", { type, relatedId });
    
    let redirectUrl = '/SunnyLink/public/index.php?controller=home&action=dashboard';
    
    switch (type) {
        case 'message':
        case 'audio':
            redirectUrl = '/SunnyLink/public/index.php?controller=message&action=received';
            break;
        case 'photo':
            redirectUrl = '/SunnyLink/public/index.php?controller=photo&action=gallery';
            break;
        case 'event':
            redirectUrl = '/SunnyLink/public/index.php?controller=event&action=index';
            break;
    }
    
    // Si la page actuelle est différente de la destination, rediriger
    if (window.location.href !== redirectUrl) {
        window.location.href = redirectUrl;
    }
}

// Styles CSS pour les animations de notification
const notifStyles = `
@keyframes notification-in {
    0% { opacity: 0; transform: translate(-50%, -30px); }
    100% { opacity: 1; transform: translate(-50%, 0); }
}

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
`;

// Ajouter les styles CSS au document
function addNotificationStyles() {
    const styleElement = document.createElement('style');
    styleElement.textContent = notifStyles;
    document.head.appendChild(styleElement);
}

// Fonction d'initialisation à appeler au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les styles
    addNotificationStyles();
    
    // Initialiser le système de notifications
    initGlobalNotifications();
});

// Exporter les fonctions pour les rendre disponibles globalement
window.initGlobalNotifications = initGlobalNotifications;
window.checkForNewNotifications = checkForNewNotifications;
window.markNotificationAsRead = markNotificationAsRead;
window.speakNotification = speakNotification;
window.playNotificationSound = playNotificationSound;