// Système de mise à jour des notifications en temps réel

class NotificationUpdater {
    constructor(options = {}) {
        this.options = {
            updateInterval: 30000, // Vérifier toutes les 30 secondes
            apiEndpoint: 'index.php?controller=notification&action=getUserNotifications',
            notificationSound: 'audio/notif-sound.mp3',
            ...options
        };
        
        this.lastNotificationId = null;
        this.updateTimer = null;
        this.initialized = false;
        
        // Lier les méthodes
        this.init = this.init.bind(this);
        this.checkForNewNotifications = this.checkForNewNotifications.bind(this);
        this.updateNotificationWidget = this.updateNotificationWidget.bind(this);
        this.playNotificationSound = this.playNotificationSound.bind(this);
    }
    
    init() {
        if (this.initialized) return;
        
        console.log('Initialisation du système de notifications...');
        
        // Vérifier les notifications immédiatement
        this.checkForNewNotifications();
        
        // Puis vérifier périodiquement
        this.updateTimer = setInterval(this.checkForNewNotifications, this.options.updateInterval);
        
        // Marquer comme initialisé
        this.initialized = true;
    }
    
    checkForNewNotifications() {
        fetch(this.options.apiEndpoint)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    // Si de nouvelles notifications sont disponibles
                    if (!this.lastNotificationId || this.lastNotificationId !== data[0].id) {
                        this.updateNotificationWidget(data);
                        this.playNotificationSound();
                        this.lastNotificationId = data[0].id;
                    }
                }
            })
            .catch(error => {
                console.error('Erreur lors de la vérification des notifications:', error);
            });
    }
    
    updateNotificationWidget(notifications) {
        
        // Mettre à jour le DOM pour refléter les nouvelles notifications
        const widget = document.querySelector('.family-notification-widget');
        if (!widget) return;
        
        // Compter les notifications par type
        let messageCount = 0;
        let readConfirmationCount = 0;
        let photoCount = 0;
        let eventCount = 0;
        
        notifications.forEach(notif => {
            switch (notif.type) {
                case 'message':
                case 'audio':
                    messageCount++;
                    break;
                case 'read_confirmation':
                    readConfirmationCount++;
                    break;
                case 'photo':
                    photoCount++;
                    break;
                case 'event':
                    eventCount++;
                    break;
            }
        });
        
        const totalCount = notifications.length;
        
        // Mettre à jour le compteur de notification
        const badgeContainer = widget.querySelector('.widget-title .badge');
        if (badgeContainer) {
            badgeContainer.textContent = totalCount;
            badgeContainer.style.display = totalCount > 0 ? 'inline-block' : 'none';
        }
        
        // Recharger complètement le widget si nécessaire
        if (totalCount > 0) {
            location.reload();
        }
        // Afficher un toast pour la dernière notification
if (notifications.length > 0) {
    const lastNotif = notifications[0];
    const toastEl = document.getElementById('notification-toast');
    const contentEl = document.getElementById('notification-content');
    
    if (toastEl && contentEl) {
        contentEl.textContent = lastNotif.content;
        
        // Utiliser l'API Bootstrap Toast
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}
    }
    
    playNotificationSound() {
        const audio = document.getElementById('notification-sound');
        if (audio) {
            audio.volume = 0.5;
            audio.play().catch(e => console.warn('Impossible de jouer le son:', e));
        }
    }
}

// Initialiser le système de notification au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const notificationUpdater = new NotificationUpdater();
    notificationUpdater.init();
    
    // Rendre accessible globalement
    window.notificationUpdater = notificationUpdater;
});