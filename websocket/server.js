const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');
const path = require('path');

// Chemins vers les certificats
const certPath = '/var/www/html/SunnyLink/websocket/certs/fullchain.pem';
const keyPath = '/var/www/html/SunnyLink/websocket/certs/privkey.pem';

// Stockage des clients connectés
const clients = new Map();

try {
    // Vérifier si les fichiers existent
    if (!fs.existsSync(certPath)) {
        throw new Error(`Le certificat n'existe pas : ${certPath}`);
    }
    if (!fs.existsSync(keyPath)) {
        throw new Error(`La clé privée n'existe pas : ${keyPath}`);
    }

    // Création du serveur HTTPS avec les certificats SSL
    const server = https.createServer({
        cert: fs.readFileSync(certPath),
        key: fs.readFileSync(keyPath)
    });

    const wss = new WebSocket.Server({ server });

    // Fonction pour envoyer un message à un utilisateur spécifique
    function sendToUser(userId, message) {
        const client = clients.get(userId);
        if (client && client.ws.readyState === WebSocket.OPEN) {
            client.ws.send(JSON.stringify(message));
            return true;
        }
        return false;
    }

    // Fonction pour diffuser un message à tous les clients connectés
    function broadcast(message, exclude = null) {
        clients.forEach((client, userId) => {
            if (exclude !== userId && client.ws.readyState === WebSocket.OPEN) {
                client.ws.send(JSON.stringify(message));
            }
        });
    }

    wss.on('connection', function connection(ws) {
        console.log('Nouvelle connexion WebSocket établie');
        let userId = null;
        
        // Définir le statut "vivant" pour le ping/pong
        ws.isAlive = true;
        
        // Répondre aux pings
        ws.on('pong', () => {
            ws.isAlive = true;
        });

        ws.on('message', function incoming(message) {
            try {
                // Tenter de parser le message comme JSON
                const data = JSON.parse(message.toString());
                console.log('Message reçu:', data);

                // Authentification
                if (data.type === 'auth') {
                    userId = data.userId;
                    clients.set(userId, { ws, lastActivity: Date.now() });
                    console.log(`Utilisateur ${userId} authentifié`);
                    ws.send(JSON.stringify({ type: 'auth_success', userId }));
                    return;
                }

                // Vérifier que l'utilisateur est authentifié
                if (!userId) {
                    ws.send(JSON.stringify({ type: 'error', message: 'Authentification requise' }));
                    return;
                }

                // Mise à jour de l'activité
                if (clients.has(userId)) {
                    clients.get(userId).lastActivity = Date.now();
                }

                // Traitement des différents types de messages
                switch (data.type) {
                    case 'audio':
                        // Envoyer un message audio à un destinataire spécifique
                        if (data.receiverId && data.audioData) {
                            const sent = sendToUser(data.receiverId, {
                                type: 'audio',
                                senderId: userId,
                                audioData: data.audioData,
                                timestamp: Date.now()
                            });
                            
                            ws.send(JSON.stringify({
                                type: 'delivery_status',
                                messageType: 'audio',
                                receiverId: data.receiverId,
                                status: sent ? 'delivered' : 'pending'
                            }));
                        }
                        break;
                        
                    case 'message':
                        // Envoyer un message texte à un destinataire spécifique
                        if (data.receiverId && data.text) {
                            const sent = sendToUser(data.receiverId, {
                                type: 'message',
                                senderId: userId,
                                text: data.text,
                                timestamp: Date.now()
                            });
                            
                            ws.send(JSON.stringify({
                                type: 'delivery_status',
                                messageType: 'message',
                                receiverId: data.receiverId,
                                status: sent ? 'delivered' : 'pending'
                            }));
                        }
                        break;
                        
                    case 'notification':
                        // Envoyer une notification à un destinataire spécifique
                        if (data.receiverId && data.content) {
                            sendToUser(data.receiverId, {
                                type: 'notification',
                                senderId: userId,
                                content: data.content,
                                notifType: data.notifType || 'info',
                                timestamp: Date.now()
                            });
                        }
                        break;
                        
                    case 'ping':
                        // Répondre au ping client
                        ws.send(JSON.stringify({ type: 'pong', timestamp: Date.now() }));
                        break;
                        
                    default:
                        console.log(`Type de message non géré: ${data.type}`);
                }
            } catch (error) {
                console.error('Erreur lors du traitement du message:', error);
                ws.send(JSON.stringify({ type: 'error', message: 'Format de message invalide' }));
            }
        });

        ws.on('close', function() {
            console.log('Connexion fermée');
            if (userId) {
                clients.delete(userId);
                console.log(`Utilisateur ${userId} déconnecté`);
            }
        });
        
        ws.on('error', function(error) {
            console.error('Erreur WebSocket:', error);
            if (userId) {
                clients.delete(userId);
            }
        });
    });

    // Ping pour garder les connexions actives
    const interval = setInterval(function ping() {
        wss.clients.forEach(function each(ws) {
            if (ws.isAlive === false) {
                return ws.terminate();
            }
            
            ws.isAlive = false;
            ws.ping();
        });
    }, 30000); // Ping toutes les 30 secondes

    wss.on('close', function close() {
        clearInterval(interval);
    });

    server.listen(8080, function listening() {
        console.log('Serveur WebSocket sécurisé démarré sur le port 8080');
    });
} catch (error) {
    console.error('Erreur lors du démarrage du serveur WebSocket:', error);
    process.exit(1);
}
