<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .message-container {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .audio-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .audio-status {
            margin-top: 15px;
            text-align: center;
        }
        
        #recordingStatus {
            font-weight: bold;
            color: #dc3545;
        }
        
        #audioWaveform {
            height: 60px;
            background-color: #e9ecef;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .message-type-selector {
            margin-bottom: 20px;
        }
        
        .btn-record {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-stop {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-send {
            background-color: #28a745;
            color: white;
        }
        
        .message-history {
            margin-top: 30px;
        }
        
        .message-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            background-color: #e9ecef;
        }
        
        .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Envoyer un message</h2>
        
        <div class="recipient-selector mb-3">
            <label for="receiver_id" class="form-label">Sélectionner un destinataire</label>
            <?php if (empty($seniors)): ?>
                <div class="alert alert-warning">
                    Aucun destinataire disponible. Veuillez d'abord établir une relation avec un senior.
                </div>
            <?php else: ?>
                <select id="receiver_id" name="receiver_id" class="form-select">
                    <?php foreach ($seniors as $senior): ?>
                        <option value="<?= $senior['user_id'] ?>"><?= htmlspecialchars($senior['name'] ?? 'Senior #'.$senior['user_id']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        
        <div class="message-type-selector">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="message_type" id="text_type" value="text" checked>
                <label class="btn btn-outline-primary" for="text_type">Message texte</label>
                
                <input type="radio" class="btn-check" name="message_type" id="audio_type" value="audio">
                <label class="btn btn-outline-primary" for="audio_type">Message audio</label>
            </div>
        </div>
        
        <!-- Section message texte -->
        <div id="text_message_section" class="message-container">
            <h3>Message texte</h3>
            <div class="mb-3">
                <textarea id="textMessage" class="form-control" rows="3" placeholder="Écrivez votre message..."></textarea>
            </div>
            <button id="sendTextButton" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Envoyer
            </button>
        </div>
        
        <!-- Section message audio -->
        <div id="audio_message_section" class="message-container" style="display: none;">
            <h3>Message audio</h3>
            <div class="audio-controls">
                <button id="recordButton" class="btn btn-record">
                    <i class="fas fa-microphone"></i> Enregistrer
                </button>
                <button id="stopButton" class="btn btn-stop" disabled>
                    <i class="fas fa-stop"></i> Arrêter
                </button>
                <button id="sendAudioButton" class="btn btn-send" disabled>
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
            <div class="audio-status">
                <span id="recordingStatus"></span>
                <div id="audioWaveform"></div>
                <div id="audioPreviewContainer"></div>
            </div>
        </div>
        
        <div class="message-history">
            <h3>Historique des messages</h3>
            <div id="messageList">
                <!-- Les messages seront ajoutés ici dynamiquement -->
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let mediaRecorder;
        let audioChunks = [];
        let audioBlob;
        let websocket;
        
        const textTypeRadio = document.getElementById('text_type');
        const audioTypeRadio = document.getElementById('audio_type');
        const textMessageSection = document.getElementById('text_message_section');
        const audioMessageSection = document.getElementById('audio_message_section');
        
        const recordButton = document.getElementById('recordButton');
        const stopButton = document.getElementById('stopButton');
        const sendAudioButton = document.getElementById('sendAudioButton');
        const sendTextButton = document.getElementById('sendTextButton');
        const recordingStatus = document.getElementById('recordingStatus');
        const receiverSelect = document.getElementById('receiver_id');
        const textMessageInput = document.getElementById('textMessage');
        
        // Gestion du changement de type de message
        textTypeRadio.addEventListener('change', function() {
            if (this.checked) {
                textMessageSection.style.display = 'block';
                audioMessageSection.style.display = 'none';
            }
        });
        
        audioTypeRadio.addEventListener('change', function() {
            if (this.checked) {
                textMessageSection.style.display = 'none';
                audioMessageSection.style.display = 'block';
            }
        });
        
        // Connexion WebSocket
        function connectWebSocket() {
            websocket = new WebSocket('ws://<?= $_SERVER['HTTP_HOST'] ?>:8080');
            
            websocket.onopen = function() {
                console.log('Connexion WebSocket établie');
                // Identifier l'utilisateur
                websocket.send(JSON.stringify({
                    type: 'identify',
                    userId: '<?= $_SESSION['user_id'] ?>'
                }));
            };
            
            websocket.onclose = function() {
                console.log('Connexion WebSocket fermée');
                // Tentative de reconnexion après 5 secondes
                setTimeout(connectWebSocket, 5000);
            };
            
            websocket.onerror = function(error) {
                console.error('Erreur WebSocket:', error);
            };
            
            websocket.onmessage = function(e) {
                const data = JSON.parse(e.data);
                
                if (data.type === 'audio') {
                    // Ajouter le message audio reçu à l'historique
                    addMessageToHistory({
                        type: 'audio',
                        sender_name: data.sender_name,
                        timestamp: data.timestamp,
                        audioData: data.audioData
                    });
                } else if (data.type === 'message') {
                    // Ajouter le message texte reçu à l'historique
                    addMessageToHistory({
                        type: 'text',
                        sender_name: data.sender_name,
                        timestamp: data.timestamp,
                        content: data.content
                    });
                }
            };
        }
        
        // Connexion initiale
        connectWebSocket();
        
        // Fonction pour ajouter un message à l'historique
        function addMessageToHistory(message) {
            const messageList = document.getElementById('messageList');
            const messageItem = document.createElement('div');
            messageItem.className = 'message-item';
            
            const sender = document.createElement('div');
            sender.className = 'message-sender';
            sender.textContent = message.sender_name || 'Vous';
            
            const time = document.createElement('div');
            time.className = 'message-time';
            time.textContent = new Date(message.timestamp * 1000).toLocaleString();
            
            messageItem.appendChild(sender);
            messageItem.appendChild(time);
            
            if (message.type === 'audio') {
                const audio = document.createElement('audio');
                audio.controls = true;
                audio.src = `data:audio/wav;base64,${message.audioData}`;
                messageItem.appendChild(audio);
            } else if (message.type === 'text') {
                const text = document.createElement('p');
                text.textContent = message.content;
                messageItem.appendChild(text);
            }
            
            messageList.prepend(messageItem);
        }
        
        // Gestion de l'envoi de message texte
        sendTextButton.addEventListener('click', function() {
            const receiverId = receiverSelect.value;
            const messageText = textMessageInput.value.trim();
            
            if (!receiverId) {
                alert('Veuillez sélectionner un destinataire');
                return;
            }
            
            if (!messageText) {
                alert('Veuillez entrer un message');
                return;
            }
            
            // Envoyer le message via WebSocket
            websocket.send(JSON.stringify({
                type: 'message',
                sender: '<?= $_SESSION['user_id'] ?>',
                sender_name: '<?= $_SESSION['name'] ?>',
                receiver: receiverId,
                content: messageText,
                timestamp: Math.floor(Date.now() / 1000)
            }));
            
            // Enregistrer le message via AJAX
            fetch('index.php?controller=message&action=send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: receiverId,
                    message: messageText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Ajouter le message à l'historique
                    addMessageToHistory({
                        type: 'text',
                        sender_name: 'Vous',
                        timestamp: Math.floor(Date.now() / 1000),
                        content: messageText
                    });
                    
                    // Effacer le champ de texte
                    textMessageInput.value = '';
                } else {
                    alert('Erreur lors de l\'envoi du message: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'envoi du message');
            });
        });
        
        // Gestion de l'enregistrement audio
        recordButton.addEventListener('click', async () => {
            audioChunks = [];
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                
                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                };
                
                mediaRecorder.onstop = () => {
                    audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    // Créer un élément audio pour prévisualiser
                    const audioPreviewContainer = document.getElementById('audioPreviewContainer');
                    audioPreviewContainer.innerHTML = '';
                    
                    const audioPreview = document.createElement('audio');
                    audioPreview.src = audioUrl;
                    audioPreview.controls = true;
                    audioPreviewContainer.appendChild(audioPreview);
                    
                    sendAudioButton.disabled = false;
                };
                
                mediaRecorder.start();
                recordingStatus.textContent = 'Enregistrement en cours...';
                recordButton.disabled = true;
                stopButton.disabled = false;
            } catch (err) {
                console.error('Erreur lors de l\'accès au microphone:', err);
                recordingStatus.textContent = 'Erreur: Impossible d\'accéder au microphone';
            }
        });
        
        stopButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                recordingStatus.textContent = 'Enregistrement terminé';
                recordButton.disabled = false;
                stopButton.disabled = true;
            }
        });
        
        sendAudioButton.addEventListener('click', () => {
            const receiverId = receiverSelect.value;
            
            if (!receiverId) {
                alert('Veuillez sélectionner un destinataire');
                return;
            }
            
            if (audioBlob) {
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = function() {
                    const base64data = reader.result.split(',')[1];
                    
                    // Envoyer le message audio via WebSocket
                    websocket.send(JSON.stringify({
                        type: 'audio',
                        sender: '<?= $_SESSION['user_id'] ?>',
                        sender_name: '<?= $_SESSION['name'] ?>',
                        recipient: receiverId,
                        audioData: base64data,
                        timestamp: Math.floor(Date.now() / 1000)
                    }));
                    
                    // Enregistrer le message audio via AJAX
                    fetch('index.php?controller=message&action=sendAudio', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            receiver_id: receiverId,
                            audio_data: base64data
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Ajouter le message à l'historique
                            addMessageToHistory({
                                type: 'audio',
                                sender_name: 'Vous',
                                timestamp: Math.floor(Date.now() / 1000),
                                audioData: base64data
                            });
                            
                            // Réinitialiser l'interface
                            recordingStatus.textContent = '';
                            sendAudioButton.disabled = true;
                            const audioPreviewContainer = document.getElementById('audioPreviewContainer');
                            audioPreviewContainer.innerHTML = '';
                        } else {
                            alert('Erreur lors de l\'envoi du message audio: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de l\'envoi du message audio');
                    });
                };
            }
        });
    });
    </script>
</body>
</html>
