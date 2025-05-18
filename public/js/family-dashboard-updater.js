/**
 * Script d'actualisation des notifications en temps réel pour le tableau de bord familial
 */
class FamilyDashboardUpdater {
    constructor(options = {}) {
        // Options par défaut avec possibilité de personnalisation
this.options = {
    // Vérifier toutes les 10 secondes par défaut
    refreshInterval: options.refreshInterval || 10000,
    // Endpoint pour récupérer les notifications
    notificationEndpoint: options.notificationEndpoint || 'index.php?controller=notification&action=getUserNotifications',
    // Sélecteurs DOM
    selectors: {
        notificationWidget: '.family-notification-widget, #notifications-card .card-body',
        notificationCounter: '.widget-title .badge, .card-header .badge',
        notificationCategories: '.notification-categories',
        notificationList: '.notification-list',
        notificationSound: '#notification-sound'
    }
};
        
        // État interne
        this.lastUpdateTime = Date.now();
        this.lastNotificationCount = 0;
        this.refreshTimer = null;
        
        // Lier les méthodes au contexte actuel
        this.init = this.init.bind(this);
        this.checkForNewNotifications = this.checkForNewNotifications.bind(this);
        this.updateNotificationWidget = this.updateNotificationWidget.bind(this);
        this.playNotificationSound = this.playNotificationSound.bind(this);
    }
    
    /**
     * Initialise le système de mise à jour
     */
    init() {
        console.log('Initialisation du système de mise à jour du tableau de bord familial...');
        
        // Récupérer le nombre initial de notifications
        const badgeElement = document.querySelector(this.options.selectors.notificationCounter);
        if (badgeElement) {
            this.lastNotificationCount = parseInt(badgeElement.textContent) || 0;
        }
        
        // Démarrer la vérification périodique
        this.refreshTimer = setInterval(this.checkForNewNotifications, this.options.refreshInterval);
        
        // Première vérification immédiate
        this.checkForNewNotifications();
        
        console.log('Système de mise à jour initialisé avec succès, vérification toutes les', 
                   this.options.refreshInterval / 1000, 'secondes');
    }
    // Ajouter cette fonction dans la classe FamilyDashboardUpdater
createNotificationWidget() {
    const container = document.querySelector('.col-md-4'); // Choisir un conteneur approprié
    
    if (!container) return false;
    
    console.log('Création dynamique du widget de notification...');
    
    // Créer le widget s'il n'existe pas
    const widgetHTML = `
    <div class="family-notification-widget">
        <h4 class="widget-title">
            <i class="fas fa-bell"></i> Notifications
            <span class="badge bg-danger" style="display: none;">0</span>
        </h4>
        
        <div class="notification-categories">
            <div class="no-notifications">
                <i class="fas fa-check-circle"></i>
                <p>Vous n'avez pas de nouvelles notifications</p>
            </div>
        </div>
    </div>`;
    
    // Insérer au début du conteneur
    container.insertAdjacentHTML('afterbegin', widgetHTML);
    
    // Ajouter les styles si nécessaire
    if (!document.getElementById('notification-widget-styles')) {
        const styleElement = document.createElement('style');
        styleElement.id = 'notification-widget-styles';
        styleElement.textContent = `
            .family-notification-widget {
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                padding: 20px;
                margin-bottom: 25px;
            }
            
            .widget-title {
                margin-top: 0;
                margin-bottom: 15px;
                font-weight: 600;
                color: #333;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .widget-title i {
                color: #FFD700;
            }
            
            .notification-categories {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            
            .notif-category {
                display: flex;
                align-items: center;
                padding: 15px;
                border-radius: 8px;
                transition: background-color 0.2s;
            }
            
            .notif-category:hover {
                background-color: #f8f9fa;
            }
            
            .notif-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 15px;
                font-size: 18px;
            }
            
            .notif-messages .notif-icon {
                background-color: rgba(66, 133, 244, 0.1);
                color: #4285F4;
            }
            
            .notif-confirmations .notif-icon {
                background-color: rgba(52, 168, 83, 0.1);
                color: #34A853;
            }
            
            .notif-photos .notif-icon {
                background-color: rgba(234, 67, 53, 0.1);
                color: #EA4335;
            }
            
            .notif-events .notif-icon {
                background-color: rgba(251, 188, 5, 0.1);
                color: #FBBC05;
            }
            
            .notif-details {
                flex-grow: 1;
            }
            
            .notif-title {
                font-weight: 600;
                color: #333;
            }
            
            .notif-count {
                font-size: 14px;
                color: #666;
            }
            
            .notif-action {
                background-color: #f1f3f4;
                color: #333;
                text-decoration: none;
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: 500;
                font-size: 14px;
                transition: background-color 0.2s;
            }
            
            .notif-action:hover {
                background-color: #e2e6ea;
                color: #333;
            }
            
            .no-notifications {
                padding: 20px;
                text-align: center;
                color: #666;
            }
            
            .no-notifications i {
                font-size: 40px;
                color: #34A853;
                margin-bottom: 10px;
            }
        `;
        document.head.appendChild(styleElement);
    }
    
    return true;
}

// Puis modifiez la méthode updateNotificationUI pour appeler cette fonction si le widget n'existe pas
updateNotificationUI(notifications) {
    const widgetElement = document.querySelector(this.selectors.notificationWidget);
    
    // Si le widget n'existe pas, essayer de le créer
    if (!widgetElement && !this.createNotificationWidget()) {
        console.error("Impossible de mettre à jour les notifications : widget non trouvé et création impossible");
        return;
    }
    
    // Le reste du code de mise à jour...
}
    /**
     * Vérifie s'il y a de nouvelles notifications
     */
    checkForNewNotifications() {
        fetch(this.options.notificationEndpoint)
            .then(response => response.json())
            .then(data => {
                console.log('Réponse API notifications:', data);
                
                if (Array.isArray(data)) {
                    // Comparer avec l'état précédent
                    const newCount = data.length;
                    
                    if (newCount > this.lastNotificationCount) {
                        // Jouer un son si de nouvelles notifications
                        this.playNotificationSound();
                        
                        // Mettre à jour visuellement
                        this.updateNotificationWidget(data);
                        
                        console.log(`${newCount - this.lastNotificationCount} nouvelles notifications!`);
                    }
                    
                    // Mettre à jour l'état
                    this.lastNotificationCount = newCount;
                    this.lastUpdateTime = Date.now();
                }
            })
            .catch(error => {
                console.error('Erreur lors de la vérification des notifications:', error);
            });
    }
    
    /**
     * Met à jour dynamiquement le widget de notification sans recharger la page
     */
updateNotificationWidget(notifications) {
    // Trouver les éléments de notification dans la page, avec plusieurs sélecteurs possibles
    const widgetElements = document.querySelectorAll('.family-notification-widget, .card .notification-list');
    const badgeElements = document.querySelectorAll('.widget-title .badge, .card-header .badge');
    
    // Mettre à jour tous les badges trouvés
    badgeElements.forEach(badge => {
        badge.textContent = notifications.length;
        badge.style.display = notifications.length > 0 ? 'inline-block' : 'none';
    });
    
    // Si aucun widget trouvé, essayer d'en créer un
    if (widgetElements.length === 0) {
        console.warn('Aucun widget de notification trouvé, tentative de création...');
        if (!this.createNotificationWidget()) {
            console.error('Impossible de créer le widget de notification');
            return;
        }
    }
    
    // Pour chaque conteneur de notifications trouvé
    widgetElements.forEach(container => {
        // Calculer le HTML à insérer
        let newHtml = '';
        
        // Si aucune notification, afficher un message
        if (notifications.length === 0) {
            newHtml = `
            <div class="no-notifications">
                <i class="fas fa-check-circle"></i>
                <p>Vous n'avez pas de nouvelles notifications</p>
            </div>`;
        } else {
            // Sinon, générer la liste des notifications
            newHtml = '<ul class="list-group notification-list">';
            
            notifications.forEach(notification => {
                // Déterminer l'icône en fonction du type
                let icon = 'fas fa-bell';
                let badge = 'bg-secondary';
                
                switch(notification.type) {
                    case 'message':
                        icon = 'fas fa-envelope';
                        badge = 'bg-primary';
                        break;
                    case 'audio':
                        icon = 'fas fa-microphone';
                        badge = 'bg-info';
                        break;
                    case 'photo':
                        icon = 'fas fa-image';
                        badge = 'bg-success';
                        break;
                    case 'event':
                        icon = 'fas fa-calendar';
                        badge = 'bg-warning';
                        break;
                    case 'read_confirmation':
                        icon = 'fas fa-check-double';
                        badge = 'bg-success';
                        break;
                }
                
                newHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge ${badge} me-2">
                            <i class="${icon}"></i>
                        </span>
                        ${notification.content}
                    </div>
                    <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                            data-notif-id="${notification.id}"
                            data-type="${notification.type}"
                            data-related-id="${notification.related_id || ''}">
                        <i class="fas fa-check"></i>
                    </button>
                </li>`;
            });
            
            newHtml += '</ul>';
        }
        
        // Mettre à jour le contenu avec animation
        container.style.opacity = '0.5';
        
        // Utiliser un setTimeout pour créer une animation fluide
        setTimeout(() => {
            container.innerHTML = newHtml;
            container.style.opacity = '1';
            
            // Réattacher les gestionnaires d'événements aux nouveaux boutons
            container.querySelectorAll('.mark-read-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const notifId = this.dataset.notifId;
                    const type = this.dataset.type;
                    const relatedId = this.dataset.relatedId;
                    
                    // Utiliser la fonction globale si disponible
                    if (typeof markNotificationAsRead === 'function') {
                        markNotificationAsRead(notifId, type, relatedId);
                    } else {
                        // Sinon, implémenter le traitement ici
                        console.log('Marquage comme lu:', {notifId, type, relatedId});
                        // Code de marquage comme lu...
                    }
                });
            });
            
            // Mettre en évidence le widget mis à jour
            container.closest('.card').classList.add('notification-updated');
            setTimeout(() => {
                container.closest('.card').classList.remove('notification-updated');
            }, 2000);
        }, 200);
    });
    
    // Afficher une notification toast si disponible
    this.showToastNotification(notifications[0]);
}
    
    /**
     * Joue le son de notification
     */
    playNotificationSound() {
        const audio = document.querySelector(this.options.selectors.notificationSound);
        if (audio) {
            audio.volume = 0.5; // Volume à 50%
            audio.play().catch(e => {
                console.warn('Impossible de jouer le son de notification:', e);
            });
        }
    }
    
    /**
     * Affiche une notification Toast Bootstrap
     */
    showToastNotification(notification) {
        // Vérifier si Toast est disponible
        if (typeof bootstrap === 'undefined' || !notification) {
            return;
        }
        
        // Vérifier si l'élément Toast existe
        const toastElement = document.getElementById('notification-toast');
        if (!toastElement) {
            return;
        }
        
        // Mettre à jour le contenu du toast
        const toastBody = toastElement.querySelector('.toast-body');
        if (toastBody) {
            toastBody.textContent = notification.content;
        }
        
        // Définir l'icône en fonction du type
        const toastIcon = toastElement.querySelector('.toast-header img');
        if (toastIcon) {
            switch (notification.type) {
                case 'message':
                    toastIcon.src = 'images/iconeMessage.png';
                    break;
                case 'audio':
                    toastIcon.src = 'images/iconeMusic.png';
                    break;
                case 'photo':
                    toastIcon.src = 'images/IconePhoto.png';
                    break;
                case 'event':
                    toastIcon.src = 'images/iconeAgenda.png';
                    break;
                default:
                    toastIcon.src = 'images/IconeRappel.png';
            }
        }
        
        // Afficher le toast
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const dashboardUpdater = new FamilyDashboardUpdater();
    dashboardUpdater.init();
    
    // Rendre l'instance disponible globalement pour le débogage
    window.dashboardUpdater = dashboardUpdater;
});