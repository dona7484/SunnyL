/**
 * Système amélioré de notifications pour le tableau de bord senior
 * Optimisé pour une réactivité et fiabilité maximales
 */
class SeniorDashboardNotifications {
    constructor(options = {}) {
        // Configuration avec valeurs par défaut
        this.config = {
            // Intervalle de vérification plus court pour une meilleure réactivité
            checkInterval: options.checkInterval || 5000, // 5 secondes
            endpoint: options.endpoint || 'index.php?controller=notification&action=getUserNotifications',
            selectors: {
                bubble: '#notif-bubble',
                bubbleText: '#notif-bubble-text',
                bubbleIcon: '.notif-bubble-icon',
                typeLabel: '.notif-type-label',
                timestamp: '.notif-timestamp',
                readButton: '#mark-as-read-button',
                soundElement: '#notification-sound'
            },
            debug: options.debug || true
        };

        // État interne
        this.isInitialized = false;
        this.checkTimer = null;
        this.lastNotificationId = null;
        this.lastCheckTime = 0;
        this.isCheckingNow = false;

        // Lier les méthodes au contexte actuel
        this.initSystem = this.initSystem.bind(this);
        this.checkNotifications = this.checkNotifications.bind(this);
        this.updateNotificationUI = this.updateNotificationUI.bind(this);
        this.playNotificationSound = this.playNotificationSound.bind(this);
        this.log = this.log.bind(this);
    }

    /**
     * Initialise le système de notifications
     */
    initSystem() {
        // Éviter l'initialisation multiple
        if (this.isInitialized) return;

        this.log('Initialisation du système de notifications dashboard senior...');

        // Vérifier si nous sommes sur le dashboard senior
        if (!this.isDashboardPage()) {
            this.log('Cette page n\'est pas le dashboard senior, initialisation annulée', 'warn');
            return;
        }

        // Vérifier et créer les éléments de notification si nécessaire
        if (!this.ensureNotificationElements()) {
            this.log('Impossible de trouver ou créer les éléments de notification', 'error');
            return;
        }

        // Premier check immédiat
        this.checkNotifications();

        // Puis à intervalles réguliers
        this.checkTimer = setInterval(this.checkNotifications, this.config.checkInterval);
        
        // Marquer comme initialisé
        this.isInitialized = true;
        this.log(`Système de notifications senior initialisé! Vérification toutes les ${this.config.checkInterval/1000}s`);
    }

    /**
     * Vérifie si nous sommes sur la page du dashboard senior
     */
    isDashboardPage() {
        // Vérifier plusieurs conditions pour confirmer que nous sommes sur le dashboard senior
        const isDashboard = document.getElementById('dashboardContainer') !== null;
        const hasRightSection = document.querySelector('.rightSection') !== null;
        const userIsLoggedIn = document.body.dataset.userId !== undefined;
        const userIsSenior = document.body.dataset.userRole === 'senior';
        
        return isDashboard || (hasRightSection && userIsLoggedIn && userIsSenior);
    }

    /**
     * Assure que tous les éléments de notification existent
     */
    ensureNotificationElements() {
        const bubble = document.querySelector(this.config.selectors.bubble);
        
        // Si la bulle existe déjà, vérifier ses composants
        if (bubble) {
            const textElement = bubble.querySelector(this.config.selectors.bubbleText);
            const readButton = bubble.querySelector(this.config.selectors.readButton);
            
            if (!textElement || !readButton) {
                this.log('Éléments de notification incomplets', 'warn');
                return false;
            }
            
            return true;
        }
        
        // Si la bulle n'existe pas, essayer de la créer
        this.log('Bulle de notification non trouvée, tentative de création...', 'warn');
        return this.createNotificationBubble();
    }

    /**
     * Crée une bulle de notification si elle n'existe pas
     */
    createNotificationBubble() {
        if (document.querySelector(this.config.selectors.bubble)) return true;
        
        try {
            const bubbleHtml = `
            <div id="notif-bubble" class="notif-bubble" style="display:none;">
                <img src="images/IconeRappel.png" alt="🔔" class="notif-bubble-icon">
                <div style="flex-grow: 1;">
                    <div class="notif-type-label">Nouvelle notification</div>
                    <div id="notif-bubble-text" class="notif-bubble-text">
                        Vous avez une notification
                    </div>
                    <div class="notif-timestamp">À l'instant</div>
                </div>
                <button id="mark-as-read-button" class="notif-button" data-notif-id="" data-type="" data-related-id="">
                    <img src="images/check-button.png" alt="Valider" style="width: 35px; height: 35px;">
                </button>
            </div>`;
            
            document.body.insertAdjacentHTML('beforeend', bubbleHtml);
            
            // Ajouter les styles si nécessaires
            if (!document.getElementById('notification-bubble-styles')) {
                const styleEl = document.createElement('style');
                styleEl.id = 'notification-bubble-styles';
                styleEl.textContent = `
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
                    z-index: 10000;
                    width: 80%;
                    max-width: 600px;
                    transition: all 0.3s ease;
                }
                .notif-bubble-icon {
                    width: 70px;
                    height: 70px;
                    padding: 10px;
                    background-color: rgba(255, 193, 7, 0.1);
                    border-radius: 50%;
                }
                .notif-bubble-text {
                    font-size: 24px;
                    font-weight: 600;
                    color: #333;
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
                    font-style: italic;
                }
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
                }`;
                document.head.appendChild(styleEl);
            }
            
            // Attacher l'événement au bouton
            const readButton = document.getElementById('mark-as-read-button');
            if (readButton) {
                readButton.addEventListener('click', function() {
                    const notifId = this.dataset.notifId;
                    const type = this.dataset.type;
                    const relatedId = this.dataset.relatedId;
                    
                    // Utiliser la fonction globale si disponible
                    if (typeof markNotificationAsRead === 'function') {
                        markNotificationAsRead(notifId, type, relatedId);
                    } else {
                        // Fallback: appel direct à l'API
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
                                // Masquer la bulle
                                const bubble = document.getElementById('notif-bubble');
                                if (bubble) {
                                    bubble.style.display = 'none';
                                }
                                
                                // Redirection selon le type
                                switch (type) {
                                    case 'message':
                                    case 'audio':
                                        window.location.href = 'index.php?controller=message&action=received';
                                        break;
                                    case 'photo':
                                        window.location.href = 'index.php?controller=photo&action=gallery';
                                        break;
                                    case 'event':
                                        window.location.href = relatedId ? 
                                            `index.php?controller=event&action=show&id=${relatedId}` : 
                                            'index.php?controller=event&action=index';
                                        break;
                                }
                            }
                        });
                    }
                });
            }
            
            this.log('Bulle de notification créée avec succès');
            return true;
        } catch (error) {
            this.log('Erreur lors de la création de la bulle: ' + error.message, 'error');
            return false;
        }
    }

    /**
     * Vérifie les nouvelles notifications via l'API
     */
    checkNotifications() {
        // Éviter les vérifications simultanées
        if (this.isCheckingNow) return;
        
        // Marquer comme en cours de vérification
        this.isCheckingNow = true;
        this.lastCheckTime = Date.now();
        
        // Log avec timestamp pour aider au débogage
        this.log(`Vérification des notifications à ${new Date().toLocaleTimeString()}...`);
        
        // Utiliser fetch avec un délai maximum pour éviter les appels bloquants
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);
        
        fetch(this.config.endpoint, { signal: controller.signal })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(notifications => {
                this.log(`${notifications.length} notification(s) reçue(s)`, 'info');
                
                if (Array.isArray(notifications) && notifications.length > 0) {
                    // Obtenir la notification la plus récente
                    const newestNotification = notifications[0];
                    
                    // Vérifier si c'est une nouvelle notification
                    if (!this.lastNotificationId || newestNotification.id !== this.lastNotificationId) {
                        this.log(`Nouvelle notification détectée! ID: ${newestNotification.id}, Type: ${newestNotification.type}`);
                        
                        // Mettre à jour la notification
                        this.updateNotificationUI(newestNotification);
                        
                        // Jouer le son de notification
                        this.playNotificationSound();
                        
                        // Mettre à jour l'ID de dernière notification
                        this.lastNotificationId = newestNotification.id;
                    } else {
                        this.log('Pas de nouvelles notifications');
                    }
                } else if (Array.isArray(notifications) && notifications.length === 0) {
                    // Masquer la bulle s'il n'y a pas de notifications
                    const bubble = document.querySelector(this.config.selectors.bubble);
                    if (bubble && bubble.style.display !== 'none') {
                        bubble.style.display = 'none';
                    }
                    this.lastNotificationId = null;
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    this.log('Requête annulée: délai dépassé', 'warn');
                } else {
                    this.log(`Erreur lors de la vérification: ${error.message}`, 'error');
                }
            })
            .finally(() => {
                // Nettoyer le timeout et réinitialiser l'état
                clearTimeout(timeoutId);
                this.isCheckingNow = false;
            });
    }

    /**
     * Met à jour l'interface utilisateur avec la nouvelle notification
     */
    updateNotificationUI(notification) {
        // Récupérer les éléments DOM nécessaires
        const bubble = document.querySelector(this.config.selectors.bubble);
        const bubbleText = document.querySelector(this.config.selectors.bubbleText);
        const typeLabel = document.querySelector(this.config.selectors.typeLabel);
        const iconElement = document.querySelector(this.config.selectors.bubbleIcon);
        const timestamp = document.querySelector(this.config.selectors.timestamp);
        const readButton = document.querySelector(this.config.selectors.readButton);
        
        // Vérifier que tous les éléments existent
        if (!bubble || !bubbleText || !readButton) {
            this.log('Éléments DOM requis non trouvés', 'error');
            return;
        }
        
        // Mettre à jour le contenu et les attributs
        bubbleText.textContent = notification.content || 'Nouvelle notification';
        
        if (readButton) {
            readButton.dataset.notifId = notification.id || '';
            readButton.dataset.type = notification.type || '';
            readButton.dataset.relatedId = notification.related_id || '';
        }
        
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
        
        if (timestamp) {
            timestamp.textContent = 'À l\'instant';
        }

        // Afficher la bulle avec animation
        bubble.style.opacity = '0';
        bubble.style.display = 'flex';
        
        // Force le reflow pour que l'animation fonctionne
        void bubble.offsetWidth;
        
        // Appliquer l'animation d'entrée
        bubble.style.opacity = '1';
        bubble.classList.add('notification-show');
        
        // Lire vocalement la notification si la fonction est disponible
        if (typeof speakMessage === 'function') {
            speakMessage(notification.content);
        }
    }

    /**
     * Joue le son de notification
     */
    playNotificationSound() {
        // Trouver l'élément audio
        const audio = document.querySelector(this.config.selectors.soundElement);
        
        if (!audio) {
            this.log('Élément audio non trouvé, création d\'un élément temporaire', 'warn');
            
            // Créer un élément audio temporaire si nécessaire
            const tempAudio = new Audio('audio/notif-sound.mp3');
            tempAudio.volume = 0.5;
            tempAudio.play().catch(e => this.log('Erreur de lecture audio: ' + e.message, 'error'));
            return;
        }
        
        // Réinitialiser l'audio et le jouer
        audio.pause();
        audio.currentTime = 0;
        audio.volume = 0.5;
        
        const playPromise = audio.play();
        
        if (playPromise !== undefined) {
            playPromise
                .then(() => this.log('Son de notification joué'))
                .catch(e => {
                    this.log('Erreur lors de la lecture du son: ' + e.message, 'warn');
                    
                    // Tenter d'activer le son lors du prochain clic utilisateur
                    if (e.name === 'NotAllowedError') {
                        document.body.addEventListener('click', function enableAudio() {
                            audio.play().catch(() => {});
                            document.body.removeEventListener('click', enableAudio);
                        }, { once: true });
                    }
                });
        }
    }

    /**
     * Fonction de journalisation avec niveaux
     */
    log(message, level = 'log') {
        if (!this.config.debug) return;
        
        const prefix = '[SeniorNotify] ';
        
        switch (level) {
            case 'error':
                console.error(prefix + message);
                break;
            case 'warn':
                console.warn(prefix + message);
                break;
            case 'info':
                console.info(prefix + message);
                break;
            default:
                console.log(prefix + message);
        }
    }
}

// Initialiser avec auto-détection
document.addEventListener('DOMContentLoaded', function() {
    // Délai court pour s'assurer que le DOM est complètement chargé
    setTimeout(() => {
        window.seniorNotifications = new SeniorDashboardNotifications();
        window.seniorNotifications.initSystem();
        
        // En cas d'échec d'initialisation automatique, mettre un bouton debug dans la console
        console.log('%c[DEBUG] Si les notifications ne fonctionnent pas, exécutez: window.seniorNotifications.initSystem()', 
                   'background:#ff9; color:#333; padding:4px;');
    }, 500);
});

// Fonction utilitaire pour l'activation manuelle depuis la console
function initSeniorNotifications() {
    if (window.seniorNotifications) {
        window.seniorNotifications.initSystem();
    } else {
        window.seniorNotifications = new SeniorDashboardNotifications();
        window.seniorNotifications.initSystem();
    }
    return "Système de notifications senior initialisé manuellement";
}