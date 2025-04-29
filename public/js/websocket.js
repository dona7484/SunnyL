class SunnyLinkWebSocket {
    constructor() {
        this.socket = null;
        this.connected = false;
        this.userId = null;
        this.callbacks = {
            onMessage: null,
            onConnect: null,
            onDisconnect: null,
            onError: null
        };
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.pingInterval = null;
        
        console.log('SunnyLinkWebSocket initialisé');
    }

    connect(userId) {
        if (!userId) {
            console.error('ID utilisateur invalide pour la connexion WebSocket');
            return;
        }
        
        this.userId = userId;
        console.log(`Tentative de connexion WebSocket pour l'utilisateur ${userId}`);
        
        try {
            // Utiliser le même protocole que la page (HTTP/HTTPS -> WS/WSS)
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const host = window.location.hostname;
            
            // Création du WebSocket avec le port 8080
            this.socket = new WebSocket(`${protocol}//${host}:8080/`);
            
            // Vérifier que this.socket est bien initialisé
            if (!this.socket) {
                throw new Error("Échec de création du WebSocket");
            }
            
            this.socket.onopen = () => {
                console.log('WebSocket connecté avec succès');
                this.connected = true;
                this.reconnectAttempts = 0;
                
                // Authentifier l'utilisateur
                const authMessage = {
                    type: 'auth',
                    userId: this.userId
                };
                console.log('Envoi du message d\'authentification:', authMessage);
                this.socket.send(JSON.stringify(authMessage));
                
                // Démarrer le ping régulier pour maintenir la connexion
                this.startPingInterval();
                
                if (this.callbacks.onConnect) {
                    this.callbacks.onConnect();
                }
            };
            
            this.socket.onmessage = (event) => {
                try {
                    console.log('Message WebSocket reçu:', event.data);
                    const data = JSON.parse(event.data);
                    
                    if (data.type === 'auth_success') {
                        console.log('Authentification WebSocket réussie');
                    } else if (data.type === 'error') {
                        console.error('Erreur WebSocket:', data.message);
                        if (this.callbacks.onError) {
                            this.callbacks.onError(new Error(data.message));
                        }
                    } else if ((data.type === 'message' || data.type === 'audio') && this.callbacks.onMessage) {
                        this.callbacks.onMessage(data);
                    } else if (data.type === 'pong') {
                        console.log('Pong reçu du serveur, connexion active');
                    }
                } catch (error) {
                    console.error('Erreur de parsing JSON:', error, 'Données brutes:', event.data);
                }
            };
            
            this.socket.onclose = (event) => {
                console.log('WebSocket déconnecté, code:', event.code, 'raison:', event.reason);
                this.connected = false;
                
                // Arrêter le ping
                this.stopPingInterval();
                
                if (this.callbacks.onDisconnect) {
                    this.callbacks.onDisconnect();
                }
                
                // Tentative de reconnexion
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    console.log(`Tentative de reconnexion ${this.reconnectAttempts}/${this.maxReconnectAttempts} dans 3 secondes...`);
                    setTimeout(() => this.connect(this.userId), 3000);
                } else {
                    console.error('Nombre maximum de tentatives de reconnexion atteint');
                }
            };
            
            this.socket.onerror = (error) => {
                console.error('Erreur WebSocket:', error);
                
                if (this.callbacks.onError) {
                    this.callbacks.onError(error);
                }
            };
            
        } catch (error) {
            console.error('Erreur lors de la création du WebSocket:', error);
        }
    }

    startPingInterval() {
        // Envoyer un ping toutes les 30 secondes pour maintenir la connexion active
        this.pingInterval = setInterval(() => {
            if (this.connected && this.socket && this.socket.readyState === WebSocket.OPEN) {
                console.log('Envoi de ping au serveur WebSocket');
                this.socket.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
            }
        }, 30000);
    }
    
    stopPingInterval() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
        }
    }

    sendMessage(receiverId, content) {
        if (!this.connected || !this.socket || this.socket.readyState !== WebSocket.OPEN) {
            console.error('WebSocket non connecté');
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

    sendAudioMessage(receiverId, audioData) {
        if (!this.connected || !this.socket || this.socket.readyState !== WebSocket.OPEN) {
            console.error('WebSocket non connecté');
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

    disconnect() {
        this.stopPingInterval();
        
        if (this.socket) {
            this.socket.close();
            this.socket = null;
            this.connected = false;
        }
    }

    onMessage(callback) {
        this.callbacks.onMessage = callback;
    }

    onConnect(callback) {
        this.callbacks.onConnect = callback;
    }

    onDisconnect(callback) {
        this.callbacks.onDisconnect = callback;
    }

    onError(callback) {
        this.callbacks.onError = callback;
    }
}

// Créer une instance globale
const sunnyLinkWS = new SunnyLinkWebSocket();
console.log('Instance SunnyLinkWebSocket créée globalement');