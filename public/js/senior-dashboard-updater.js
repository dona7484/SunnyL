/**
 * Script d'actualisation des notifications en temps réel pour le tableau de bord senior
 */
class SeniorDashboardUpdater {
    constructor(options = {}) {
        // Options par défaut avec possibilité de personnalisation
        this.options = {
            // Vérifier toutes les 8 secondes (plus rapide que le tableau familial)
            refreshInterval: options.refreshInterval || 8000,
            // Endpoint pour récupérer les notifications
            notificationEndpoint: options.notificationEndpoint || 'index.php?controller=notification&action=getUserNotifications',
            // Sélecteurs DOM adaptés au tableau de bord senior
            selectors: {
                notificationBubble: '#notif-bubble',
                notificationText: '#notif-bubble-text',
                notificationIcon: '.notif-bubble-icon',
                typeLabel: '.notif-type-label',
                readButton: '#mark-as-read-button',
                notificationSound: '#notification-sound'
            }
        };
        
        // État interne
        this.lastUpdateTime = Date.now();
        this.lastNotificationId = null;
        this.refreshTimer = null;
        
        // Lier les méthodes au contexte actuel
        this.init = this.init.bind(this);
        this.checkForNewNotifications = this.checkForNewNotifications.bind(this);
        this.updateNotificationBubble = this.updateNotificationBubble.bind(this);
        this.playNotificationSound = this.playNotificationSound.bind(this);
    }
    
    /**
     * Initialise le système de mise à jour
     */
    init() {
        console.log('Initialisation du système de mise à jour du tableau de bord senior...');
        
        // Récupérer l'ID de notification initial si une bulle est déjà affichée
        const readButton = document.querySelector(this.options.selectors.readButton);
        if (readButton && readButton.dataset.notifId) {
            this.lastNotificationId = readButton.dataset.notifId;
        }
        
        // Démarrer la vérification périodique
        this.refreshTimer = setInterval(this.checkForNewNotifications, this.options.refreshInterval);
        
        // Première vérification immédiate
        this.checkForNewNotifications();
        
        console.log('Système de mise à jour initialisé avec succès, vérification toutes les', 
                   this.options.refreshInterval / 1000, 'secondes');
    }
    
    /**
     * Vérifie s'il y a de nouvelles notifications
     */
    checkForNewNotifications() {
        fetch(this.options.notificationEndpoint)
            .then(response => response.json())
            .then(notifications => {
                console.log('Réponse API notifications (senior):', notifications);
                
                if (Array.isArray(notifications) && notifications.length > 0) {
                    // Récupérer la notification la plus récente
                    const latestNotification = notifications[0];
                    
                    // Vérifier si c'est une nouvelle notification
                    if (latestNotification.id !== this.lastNotificationId) {
                        // Jouer un son pour la nouvelle notification
                        this.playNotificationSound();
                        
                        // Mettre à jour la bulle de notification
                        this.updateNotificationBubble(latestNotification);
                        
                        // Mettre à jour l'ID de la dernière notification
                        this.lastNotificationId = latestNotification.id;
                        console.log('Nouvelle notification détectée, ID:', this.lastNotificationId);
                    }
                } else if (Array.isArray(notifications) && notifications.length === 0) {
                    // Masquer la bulle s'il n'y a plus de notifications
                    const bubble = document.querySelector(this.options.selectors.notificationBubble);
                    if (bubble && bubble.style.display !== 'none') {
                        bubble.style.display = 'none';
                    }
                    this.lastNotificationId = null;
                }
                
                // Mettre à jour le temps de la dernière vérification
                this.lastUpdateTime = Date.now();
            })
            .catch(error => {
                console.error('Erreur lors de la vérification des notifications:', error);
            });
    }
    
    /**
     * Met à jour la bulle de notification
     */
    updateNotificationBubble(notification) {
        // Récupérer les éléments DOM
        const bubble = document.querySelector(this.options.selectors.notificationBubble);
        const textElement = document.querySelector(this.options.selectors.notificationText);
        const typeLabel = document.querySelector(this.options.selectors.typeLabel);
        const iconElement = document.querySelector(this.options.selectors.notificationIcon);
        const readButton = document.querySelector(this.options.selectors.readButton);
        
        if (!bubble || !textElement || !readButton) {
            console.error('Éléments de notification non trouvés dans le DOM');
            return;
        }
        
        // Mettre à jour le contenu de la notification
        textElement.textContent = notification.content;
        
        // Mettre à jour le type de notification
        if (typeLabel) {
            switch (notification.type) {
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
            switch (notification.type) {
                case 'message':
                    iconElement.src = 'images/iconeMessage.png';
                    break;
                case 'audio':
                    iconElement.src = 'images/iconeMusic.png';
                    break;
                case 'photo':
                    iconElement.src = 'images/IconePhoto.png';
                    break;
                case 'event':
                    iconElement.src = 'images/iconeAgenda.png';
                    break;
                default:
                    iconElement.src = 'images/IconeRappel.png';
            }
        }
        
        // Mettre à jour les attributs du bouton de lecture
        readButton.setAttribute('data-notif-id', notification.id);
        readButton.setAttribute('data-type', notification.type || '');
        readButton.setAttribute('data-related-id', notification.related_id || '');
        
        // Afficher la bulle avec une animation
        bubble.style.display = 'none';
        setTimeout(() => {
            bubble.style.display = 'flex';
            bubble.classList.add('notification-show');
            setTimeout(() => {
                bubble.classList.remove('notification-show');
            }, 1000);
        }, 100);
        
        // Mettre à jour la timestamp
        const timestamp = bubble.querySelector('.notif-timestamp');
        if (timestamp) {
            timestamp.textContent = 'À l\'instant';
        }
    }
    
    /**
     * Joue le son de notification
     */
    playNotificationSound() {
        const audio = document.querySelector(this.options.selectors.notificationSound);
        if (audio) {
            audio.volume = 0.5; // Volume à 50%
            audio.currentTime = 0; // Remettre au début pour rejouer
            audio.play().catch(e => {
                console.warn('Impossible de jouer le son de notification:', e);
            });
        }
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si nous sommes sur le tableau de bord senior
    const isDashboard = document.getElementById('dashboardContainer') !== null;
    
    if (isDashboard) {
        const dashboardUpdater = new SeniorDashboardUpdater();
        dashboardUpdater.init();
        
        // Rendre l'instance disponible globalement
        window.seniorDashboardUpdater = dashboardUpdater;
        
        console.log('SeniorDashboardUpdater initialisé avec succès!');
    }
});