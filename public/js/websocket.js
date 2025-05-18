/**
 * SunnyLinkWebSocket - Client WebSocket pour l'application SunnyLink
 * Ce script gère la connexion WebSocket pour les notifications et messages en temps réel
 */
class SunnyLinkWebSocket {
    constructor() {
        // Configuration initiale
        this.socket = null;
        this.connected = false;
        this.userId = null;
        this.callbacks = {
            onMessage: null,
            onConnect: null,
            onDisconnect: null,
            onError: null,
            onNotification: null
        };
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectInterval = 3000; // 3 secondes
        this.pingInterval = null;
        this.pingIntervalTime = 15000; // 15 secondes
        this.autoReconnect = true;
        
        // Détecter le protocole (ws/wss) en fonction du protocole de la page (http/https)
        this.protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        this.host = window.location.hostname;
        this.port = 8080; // Port par défaut du WebSocket
        
        console.log('SunnyLinkWebSocket initialisé');
    }
handleReconnection() {
    if (this.socket && this.socket.readyState === WebSocket.CLOSED) {
        console.log("Connexion WebSocket fermée, tentative de reconnexion...");
        this.connect(this.userId);
    }
}
    /**
     * Établit la connexion WebSocket
     * @param {number} userId - ID de l'utilisateur connecté
     * @returns {boolean} - Succès de la tentative de connexion
     */
    connect(userId) {
        if (!userId) {
            console.error('ID utilisateur invalide pour la connexion WebSocket');
            return false;
        }
        
        this.userId = userId;
        console.log(`Tentative de connexion WebSocket pour l'utilisateur ${userId}`);
        
        try {
            // URL complète du WebSocket
            const wsUrl = `${this.protocol}//${this.host}:${this.port}/`;
            console.log(`Connexion WebSocket à: ${wsUrl}`);
            
            // Création de la connexion WebSocket
            this.socket = new WebSocket(wsUrl);
            
            // Vérifier que this.socket est bien initialisé
            if (!this.socket) {
                throw new Error("Échec de création du WebSocket");
            }
            
            // Configurez les gestionnaires d'événements
            this.setupEventHandlers();
            
            return true;
        } catch (error) {
            console.error('Erreur lors de la création du WebSocket:', error);
            
            // Planifier une tentative de reconnexion si activé
            if (this.autoReconnect) {
                this.scheduleReconnect();
            }
            
            return false;
        }
    }

    /**
     * Configure les gestionnaires d'événements pour la connexion WebSocket
     */
    setupEventHandlers() {
        // Événement d'ouverture de connexion
        this.socket.onopen = () => {
            console.log('WebSocket connecté avec succès');
            this.connected = true;
            this.reconnectAttempts = 0;
            
            // Authentifier l'utilisateur
            this.authenticate();
            
            // Démarrer le ping régulier pour maintenir la connexion
            this.startPingInterval();
            
            // Déclencher le callback onConnect
            if (this.callbacks.onConnect) {
                this.callbacks.onConnect();
            }
        };
        
        // Événement de réception de message
        this.socket.onmessage = (event) => {
            this.handleMessage(event);
        };
        
        // Événement de fermeture de connexion
        this.socket.onclose = (event) => {
            this.handleClose(event);
        };
        
        // Événement d'erreur
        this.socket.onerror = (error) => {
            console.error('Erreur WebSocket:', error);
            
            if (this.callbacks.onError) {
                this.callbacks.onError(error);
            }
        };
    }

    /**
     * Authentifie l'utilisateur auprès du serveur WebSocket
     */
    authenticate() {
        const authMessage = {
            type: 'auth',
            userId: this.userId
        };
        console.log('Envoi du message d\'authentification:', authMessage);
        this.socket.send(JSON.stringify(authMessage));
    }

    /**
     * Gère la réception d'un message WebSocket
     * @param {MessageEvent} event - Événement de message WebSocket
     */
    handleMessage(event) {
        try {
            console.log('Message WebSocket reçu:', event.data);
            const data = JSON.parse(event.data);
            
            // Traiter différents types de messages
            if (data.type === 'auth_success') {
                console.log('Authentification WebSocket réussie');
            } else if (data.type === 'error') {
                console.error('Erreur WebSocket:', data.message);
                if (this.callbacks.onError) {
                    this.callbacks.onError(new Error(data.message));
                }
            } else if (data.type === 'pong') {
                console.log('Pong reçu du serveur, connexion active');
            } else if (data.type === 'notification') {
                // Gérer les notifications
                if (this.callbacks.onNotification) {
                    this.callbacks.onNotification(data);
                }
                
                // Si la fonction globale showNotification existe, l'utiliser
                if (typeof window.showNotification === 'function') {
                    window.showNotification(
                        data.content, 
                        data.id, 
                        data.notificationType, 
                        data.relatedId
                    );
                }
            } else if (this.callbacks.onMessage) {
                // Pour tous les autres types de messages
                this.callbacks.onMessage(data);
            }
        } catch (error) {
            console.error('Erreur de parsing JSON:', error, 'Données brutes:', event.data);
        }
    }

    /**
     * Gère la fermeture de la connexion WebSocket
     * @param {CloseEvent} event - Événement de fermeture WebSocket
     */
    handleClose(event) {
        console.log('WebSocket déconnecté, code:', event.code, 'raison:', event.reason);
        this.connected = false;
        
        // Arrêter le ping
        this.stopPingInterval();
        
        // Déclencher le callback onDisconnect
        if (this.callbacks.onDisconnect) {
            this.callbacks.onDisconnect();
        }
        
        // Tentative de reconnexion automatique
        if (this.autoReconnect) {
            this.scheduleReconnect();
        }
    }

    /**
     * Planifie une tentative de reconnexion
     */
    scheduleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            
            // Calculer le délai avec backoff exponentiel
            const delay = Math.min(
                this.reconnectInterval * Math.pow(1.5, this.reconnectAttempts - 1),
                60000 // Maximum 1 minute
            );
            
            console.log(`Tentative de reconnexion ${this.reconnectAttempts}/${this.maxReconnectAttempts} dans ${delay/1000} secondes...`);
            
            setTimeout(() => {
                this.connect(this.userId);
            }, delay);
        } else {
            console.error('Nombre maximum de tentatives de reconnexion atteint');
        }
    }

    /**
     * Démarre l'envoi périodique de pings
     */
    startPingInterval() {
        // Arrêter tout intervalle existant
        this.stopPingInterval();
        
        // Envoyer un ping toutes les pingIntervalTime millisecondes
        this.pingInterval = setInterval(() => {
            if (this.connected && this.socket && this.socket.readyState === WebSocket.OPEN) {
                console.log('Envoi de ping au serveur WebSocket');
                this.socket.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
            }
        }, this.pingIntervalTime);
    }
    
    /**
     * Arrête l'envoi périodique de pings
     */
    stopPingInterval() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
        }
    }

    /**
     * Envoie un message texte via WebSocket
     * @param {number} receiverId - ID du destinataire
     * @param {string} content - Contenu du message
     * @returns {boolean} - Succès de l'envoi
     */
    sendMessage(receiverId, content) {
        if (!this.checkConnection()) {
            return false;
        }
        
        try {
            const message = {
                type: 'message',
                receiverId: receiverId,
                content: content
            };
            
            console.log('Envoi de message via WebSocket:', message);
            this.socket.send(JSON.stringify(message));
            return true;
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
            return false;
        }
    }

    /**
     * Envoie un message audio via WebSocket
     * @param {number} receiverId - ID du destinataire
     * @param {string} audioData - Données audio en base64
     * @returns {boolean} - Succès de l'envoi
     */
    sendAudioMessage(receiverId, audioData) {
        if (!this.checkConnection()) {
            return false;
        }
        
        try {
            const message = {
                type: 'audio',
                receiverId: receiverId,
                audioData: audioData
            };
            
            console.log('Envoi de message audio via WebSocket');
            this.socket.send(JSON.stringify(message));
            return true;
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message audio:', error);
            return false;
        }
    }

    /**
     * Vérifie si la connexion WebSocket est active
     * @returns {boolean} - État de la connexion
     */
    checkConnection() {
        if (!this.connected || !this.socket || this.socket.readyState !== WebSocket.OPEN) {
            console.error('WebSocket non connecté');
            
            // Tenter de reconnecter si déconnecté
            if (this.autoReconnect && (!this.socket || this.socket.readyState === WebSocket.CLOSED)) {
                this.connect(this.userId);
            }
            
            return false;
        }
        
        return true;
    }

    /**
     * Ferme la connexion WebSocket
     */
    disconnect() {
        this.stopPingInterval();
        
        if (this.socket) {
            this.socket.close();
            this.socket = null;
            this.connected = false;
        }
    }

    /**
     * Enregistre un callback pour les messages reçus
     * @param {function} callback - Fonction à appeler lors de la réception d'un message
     */
    onMessage(callback) {
        this.callbacks.onMessage = callback;
    }

    /**
     * Enregistre un callback pour les connexions réussies
     * @param {function} callback - Fonction à appeler lors de la connexion
     */
    onConnect(callback) {
        this.callbacks.onConnect = callback;
    }

    /**
     * Enregistre un callback pour les déconnexions
     * @param {function} callback - Fonction à appeler lors de la déconnexion
     */
    onDisconnect(callback) {
        this.callbacks.onDisconnect = callback;
    }

    /**
     * Enregistre un callback pour les erreurs
     * @param {function} callback - Fonction à appeler lors d'une erreur
     */
    onError(callback) {
        this.callbacks.onError = callback;
    }
    
    /**
     * Enregistre un callback pour les notifications
     * @param {function} callback - Fonction à appeler lors de la réception d'une notification
     */
    onNotification(callback) {
        this.callbacks.onNotification = callback;
    }
}

// Créer une instance globale
const sunnyLinkWS = new SunnyLinkWebSocket();
console.log('Instance SunnyLinkWebSocket créée globalement');

// Tenter de se connecter automatiquement si l'ID utilisateur est disponible
document.addEventListener('DOMContentLoaded', function() {
    const userId = document.documentElement.dataset.userId || document.body.dataset.userId;
    
    if (userId) {
        console.log(`ID utilisateur détecté dans le DOM: ${userId}`);
        sunnyLinkWS.connect(parseInt(userId, 10));
        
        // Ecouter les notifications
        sunnyLinkWS.onNotification(function(notification) {
            console.log("Notification WebSocket reçue:", notification);
            
            // Si la fonction globale de notification existe, l'utiliser
            if (typeof window.showNotification === 'function') {
                window.showNotification(
                    notification.content,
                    notification.id,
                    notification.type,
                    notification.related_id
                );
            }
        });
    } else {
        console.warn("Aucun ID utilisateur trouvé dans le DOM. La connexion WebSocket ne sera pas établie.");
    }
});