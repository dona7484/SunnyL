
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
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const port = 8080; // Port du serveur WebSocket distant
        const wsUrl = `${protocol}//${host}:${port}/`;

        try {
            this.socket = new WebSocket(wsUrl);
        } catch (error) {
            console.error('Erreur lors de la création du WebSocket:', error);
            return;
        }

        this.socket.onopen = () => {
            console.log('WebSocket connecté avec succès');
            this.connected = true;
            this.reconnectAttempts = 0;
            // Authentifier l'utilisateur auprès du serveur WebSocket
            this.socket.send(JSON.stringify({ type: 'auth', userId: this.userId }));
            this.startPingInterval();
            if (this.callbacks.onConnect) this.callbacks.onConnect();
        };

        this.socket.onmessage = (event) => {
            let data;
            try {
                data = JSON.parse(event.data);
            } catch (e) {
                console.error('Erreur de parsing JSON:', e, 'Données brutes:', event.data);
                return;
            }
            // Gestion des différents types de messages
            if (data.type === 'auth_success') {
                console.log('Authentification WebSocket réussie');
            } else if (data.type === 'error') {
                console.error('Erreur WebSocket:', data.message);
                if (this.callbacks.onError) this.callbacks.onError(new Error(data.message));
            } else if ((data.type === 'message' || data.type === 'audio') && this.callbacks.onMessage) {
                this.callbacks.onMessage(data);
            } else if (data.type === 'pong') {
                // Réponse au ping
                // Optionnel : console.log('Pong reçu du serveur, connexion active');
            }
        };

        this.socket.onclose = (event) => {
            console.warn('WebSocket déconnecté, code:', event.code, 'raison:', event.reason);
            this.connected = false;
            this.stopPingInterval();
            if (this.callbacks.onDisconnect) this.callbacks.onDisconnect();
            // Tentative de reconnexion automatique
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                setTimeout(() => this.connect(this.userId), 3000);
            } else {
                console.error('Nombre maximum de tentatives de reconnexion atteint');
            }
        };

        this.socket.onerror = (error) => {
            console.error('Erreur WebSocket:', error);
            if (this.callbacks.onError) this.callbacks.onError(error);
        };
    }

    startPingInterval() {
        this.stopPingInterval();
        this.pingInterval = setInterval(() => {
            if (this.connected && this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
            }
        }, 30000); // Ping toutes les 30 secondes
    }

    stopPingInterval() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
        }
    }

    sendMessage(receiverId, text) {
        if (!this.connected || !this.socket || this.socket.readyState !== WebSocket.OPEN) {
            console.error('WebSocket non connecté');
            return false;
        }
        try {
            const message = {
                type: 'message',
                receiverId: receiverId,
                text: text
            };
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

// Instanciation globale et initialisation automatique si userId présent
window.sunnyLinkWS = new SunnyLinkWebSocket();

/**
 * Initialisation automatique de la connexion WebSocket après chargement de la page,
 * si la variable globale window.currentUserId existe (injectée côté PHP).
 */
document.addEventListener('DOMContentLoaded', function() {
    if (window.currentUserId) {
        window.sunnyLinkWS.connect(window.currentUserId);
    } else {
        console.warn('Aucun userId détecté pour la connexion WebSocket');
    }
});

/**
 * Fonction universelle pour envoyer un message audio (avec fallback REST si WebSocket KO)
 * @param {Blob} audioBlob - Blob audio à envoyer
 * @param {number} receiverId - ID du destinataire
 */
function sendAudioMessage(audioBlob, receiverId) {
    // Encodage du blob audio en base64 (DataURL)
    const reader = new FileReader();
    reader.onload = function() {
        const audioData = reader.result;
        // Essayer d'envoyer via WebSocket
        if (window.sunnyLinkWS && window.sunnyLinkWS.connected) {
            const sent = window.sunnyLinkWS.sendAudioMessage(receiverId, audioData);
            if (sent) {
                showStatus('Message audio envoyé via WebSocket.', 'success');
                resetAudioRecording && resetAudioRecording();
                return;
            }
        }
        // Fallback REST API si WebSocket non dispo
        sendAudioViaREST(receiverId, audioData);
    };
    reader.readAsDataURL(audioBlob);
}

/**
 * Fallback REST API pour l'envoi d'un message audio
 * @param {number} receiverId
 * @param {string} audioData (base64)
 */
function sendAudioViaREST(receiverId, audioData) {
    showStatus('Envoi du message audio via REST API...', 'info');
    fetch('index.php?controller=message&action=sendAudio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_id: receiverId,
            audio_data: audioData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showStatus('Message audio envoyé avec succès (via API REST).', 'success');
            resetAudioRecording && resetAudioRecording();
        } else {
            showStatus('Erreur lors de l\'envoi du message audio: ' + (data.message || 'Erreur inconnue'), 'danger');
        }
    })
    .catch(error => {
        showStatus('Erreur de connexion: ' + error.message, 'danger');
    });
}

// Utilitaires d'affichage de statut (à adapter selon ton UI)
function showStatus(msg, type = 'info') {
    // Affiche un message dans la console et dans l'UI si besoin
    console.log(`[${type}]`, msg);
    // À adapter : afficher un toast, une alerte, etc.
}

// À implémenter selon ton code d'enregistrement audio
function resetAudioRecording() {
    // Réinitialise l'UI d'enregistrement audio
}

