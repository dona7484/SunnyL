<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un message - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .message-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .message-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-online {
            background-color: #28a745;
        }
        .status-offline {
            background-color: #dc3545;
        }
        .audio-controls {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
        .btn-record {
            background-color: #dc3545;
            color: white;
        }
        .btn-stop {
            background-color: #6c757d;
            color: white;
        }
        .message-type-selector {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="message-tabs mb-4">
  <ul class="nav nav-pills nav-fill">
    <li class="nav-item">
      <a class="nav-link active" id="text-tab" data-bs-toggle="tab" href="#text-message">Message texte</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="audio-tab" data-bs-toggle="tab" href="#audio-message">Message audio</a>
    </li>
  </ul>
</div>

<div class="tab-content">
  <!-- Onglet message texte -->
  <div class="tab-pane fade show active" id="text-message">
    <form method="post" id="messageForm" action="index.php?controller=message&action=send">
      <div class="form-group mb-3">
        <label for="receiver_id">Destinataire</label>
        <select class="form-select" id="receiver_id" name="receiver_id" required>
          <option value="">Choisir un destinataire</option>
          <?php foreach ($seniors as $senior): ?>
            <option value="<?= $senior['user_id'] ?>"><?= htmlspecialchars($senior['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="form-group mb-3">
        <label for="message">Message</label>
        <textarea class="form-control" id="message" name="message" required></textarea>
      </div>
      
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
  </div>
  
  <!-- Onglet message audio -->
  <div class="tab-pane fade" id="audio-message">
    <div class="audio-recorder-container">
      <div class="form-group mb-3">
        <label for="audio_receiver_id">Destinataire</label>
        <select class="form-select" id="audio_receiver_id" required>
          <option value="">Choisir un destinataire</option>
          <?php foreach ($seniors as $senior): ?>
            <option value="<?= $senior['user_id'] ?>"><?= htmlspecialchars($senior['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="audio-controls mb-3">
        <button id="startRecording" class="btn btn-danger">
          <i class="fas fa-microphone"></i> Commencer l'enregistrement
        </button>
        <button id="stopRecording" class="btn btn-secondary" disabled>
          <i class="fas fa-stop"></i> Arrêter l'enregistrement
        </button>
      </div>
      
      <div class="audio-preview mb-3" style="display: none;">
        <p>Aperçu de l'enregistrement :</p>
        <audio id="audioPreview" controls></audio>
      </div>
      
      <button id="sendAudio" class="btn btn-primary" disabled>Envoyer le message audio</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const connectionStatus = document.getElementById('connection-status');
    const connectionText = document.getElementById('connection-text');
    const statusMessage = document.getElementById('status-message');
    const sendBtn = document.getElementById('send-btn');
    const messageTypeRadios = document.querySelectorAll('input[name="message_type"]');
    const textMessageSection = document.getElementById('text_message_section');
    const audioMessageSection = document.getElementById('audio_message_section');
    const recordButton = document.getElementById('recordButton');
    const stopButton = document.getElementById('stopButton');
    const sendAudioButton = document.getElementById('sendAudioButton');
    const recordingStatus = document.getElementById('recordingStatus');
    const audioPreviewContainer = document.getElementById('audioPreviewContainer');
    
    // Variables pour l'enregistrement audio
    let mediaRecorder;
    let audioChunks = [];
    let audioBlob = null;
    let audioUrl = null;
    
    // ID utilisateur pour WebSocket
    const userId = <?= $_SESSION['user_id'] ?>;
    console.log('ID utilisateur pour WebSocket:', userId);
    
    // Connexion au WebSocket
    if (typeof sunnyLinkWS !== 'undefined') {
        // Gérer les événements de connexion
        sunnyLinkWS.onConnect(function() {
            if (connectionStatus) {
                connectionStatus.classList.add('status-online');
                connectionStatus.classList.remove('status-offline');
            }
            if (connectionText) {
                connectionText.textContent = 'Connecté';
            }
            showStatus('Connecté au serveur', 'success');
        });
        
        sunnyLinkWS.onDisconnect(function() {
            if (connectionStatus) {
                connectionStatus.classList.add('status-offline');
                connectionStatus.classList.remove('status-online');
            }
            if (connectionText) {
                connectionText.textContent = 'Déconnecté (tentative de reconnexion...)';
            }
            showStatus('Déconnecté du serveur, tentative de reconnexion...', 'warning');
        });
        
        sunnyLinkWS.onError(function(error) {
            console.error('Erreur WebSocket:', error);
            showStatus('Erreur de connexion au serveur', 'danger');
        });
        
        // Gérer les messages reçus via WebSocket
        sunnyLinkWS.onMessage(function(data) {
            console.log('Message WebSocket reçu:', data);
            
            if (data.type === 'message_sent' || data.type === 'audio_sent') {
                showStatus('Message envoyé avec succès!', 'success');
                
                // Réinitialiser les formulaires
                if (data.type === 'message_sent') {
                    document.getElementById('message').value = '';
                } else if (data.type === 'audio_sent') {
                    resetAudioRecording();
                }
            }
        });
        
        // Connecter au WebSocket
        sunnyLinkWS.connect(userId);
    } else {
        console.error("L'objet sunnyLinkWS n'est pas défini. Vérifiez que le fichier websocket.js est correctement chargé.");
        showStatus("Impossible de se connecter au serveur de messages", 'danger');
    }
    
    // Gestion des types de messages (texte/audio)
    messageTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'text') {
                textMessageSection.style.display = 'block';
                audioMessageSection.style.display = 'none';
            } else if (this.value === 'audio') {
                textMessageSection.style.display = 'none';
                audioMessageSection.style.display = 'block';
            }
        });
    });
    
    // Gestion de l'enregistrement audio
    if (recordButton && stopButton && sendAudioButton) {
        // Vérifier si le navigateur prend en charge l'API MediaRecorder
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            // Demander l'autorisation d'accéder au microphone
            recordButton.addEventListener('click', function() {
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        // Créer un nouvel enregistreur
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];
                        
                        // Collecter les données audio
                        mediaRecorder.addEventListener('dataavailable', event => {
                            audioChunks.push(event.data);
                        });
                        
                        // Quand l'enregistrement est terminé
                        mediaRecorder.addEventListener('stop', () => {
                            // Créer un blob à partir des chunks audio
                            audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                            audioUrl = URL.createObjectURL(audioBlob);
                            
                            // Créer un élément audio pour la prévisualisation
                            const audioElement = document.createElement('audio');
                            audioElement.src = audioUrl;
                            audioElement.controls = true;
                            audioElement.style.width = '100%';
                            
                            // Vider le conteneur de prévisualisation et ajouter le nouvel élément
                            audioPreviewContainer.innerHTML = '';
                            audioPreviewContainer.appendChild(audioElement);
                            
                            // Activer le bouton d'envoi
                            sendAudioButton.disabled = false;
                            
                            // Mettre à jour le statut
                            recordingStatus.textContent = 'Enregistrement terminé';
                            
                            // Arrêter toutes les pistes du stream
                            stream.getTracks().forEach(track => track.stop());
                        });
                        
                        // Démarrer l'enregistrement
                        mediaRecorder.start();
                        recordButton.disabled = true;
                        stopButton.disabled = false;
                        recordingStatus.textContent = 'Enregistrement en cours...';
                    })
                    .catch(error => {
                        console.error('Erreur lors de l\'accès au microphone:', error);
                        showStatus('Impossible d\'accéder au microphone. Vérifiez les permissions.', 'danger');
                    });
            });
            
            // Arrêter l'enregistrement
            stopButton.addEventListener('click', function() {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    recordButton.disabled = false;
                    stopButton.disabled = true;
                }
            });
            
            // Envoyer l'audio
            sendAudioButton.addEventListener('click', function() {
                const receiverId = document.getElementById('receiver')?.value;
                
                if (!receiverId) {
                    showStatus('Veuillez sélectionner un destinataire.', 'warning');
                    return;
                }
                
                if (!audioBlob) {
                    showStatus('Aucun enregistrement audio disponible.', 'warning');
                    return;
                }
                
                // Convertir le Blob en base64 pour l'envoi
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = function() {
                    const base64Audio = reader.result.split(',')[1]; // Enlever le préfixe "data:audio/webm;base64,"
                    
                    // Envoi via WebSocket si disponible
                    if (typeof sunnyLinkWS !== 'undefined' && sunnyLinkWS.connected) {
                        const success = sunnyLinkWS.sendAudioMessage(receiverId, base64Audio);
                        
                        if (success) {
                            showStatus('Envoi du message audio en cours...', 'info');
                        } else {
                            // Fallback à l'API REST si WebSocket échoue
                            sendAudioViaREST(receiverId, base64Audio);
                        }
                    } else {
                        // Utiliser l'API REST si WebSocket n'est pas disponible
                        sendAudioViaREST(receiverId, base64Audio);
                    }
                };
            });
        } else {
            recordButton.disabled = true;
            recordingStatus.textContent = 'Votre navigateur ne prend pas en charge l\'enregistrement audio.';
        }
    }
    
    // Fonction pour réinitialiser l'enregistrement audio
    function resetAudioRecording() {
        audioChunks = [];
        audioBlob = null;
        if (audioUrl) {
            URL.revokeObjectURL(audioUrl);
            audioUrl = null;
        }
        audioPreviewContainer.innerHTML = '';
        sendAudioButton.disabled = true;
        recordingStatus.textContent = '';
    }
    
    // Gestion de l'envoi du message texte
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            const receiverId = document.getElementById('receiver')?.value;
            const messageContent = document.getElementById('message')?.value;
            
            if (!receiverId) {
                showStatus('Veuillez sélectionner un destinataire.', 'warning');
                return;
            }
            
            if (!messageContent?.trim()) {
                showStatus('Veuillez saisir un message.', 'warning');
                return;
            }
            
            // Envoi via WebSocket si disponible
            if (typeof sunnyLinkWS !== 'undefined' && sunnyLinkWS.connected) {
                const success = sunnyLinkWS.sendMessage(receiverId, messageContent);
                
                if (success) {
                    showStatus('Envoi du message en cours...', 'info');
                } else {
                    // Fallback à l'API REST si WebSocket échoue
                    sendViaREST(receiverId, messageContent);
                }
            } else {
                // Utiliser l'API REST si WebSocket n'est pas disponible
                sendViaREST(receiverId, messageContent);
            }
        });
    }
    
    // Fonction pour envoyer le message texte via l'API REST (fallback)
    function sendViaREST(receiverId, messageContent) {
        showStatus('Envoi du message via REST API...', 'info');
        fetch('index.php?controller=message&action=send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                message: messageContent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showStatus('Message envoyé avec succès (via API REST).', 'success');
                document.getElementById('message').value = '';
            } else {
                showStatus('Erreur lors de l\'envoi du message: ' + (data.message || 'Erreur inconnue'), 'danger');
            }
        })
        .catch(error => {
            showStatus('Erreur de connexion: ' + error.message, 'danger');
        });
    }
    
    // Fonction pour envoyer le message audio via l'API REST (fallback)
    function sendAudioMessage(audioBlob, receiverId) {
    // Vérifier si WebSocket est disponible
    if (typeof sunnyLinkWS !== 'undefined' && sunnyLinkWS.readyState === WebSocket.OPEN) {
        showStatus('Envoi du message audio via WebSocket...', 'info');
        var reader = new FileReader();
        reader.onload = function() {
            // On envoie le message audio (base64) via WebSocket
            sunnyLinkWS.send(JSON.stringify({
                type: 'audio',
                receiverId: receiverId,
                audioData: reader.result // base64
            }));
            showStatus('Message audio envoyé avec succès (via WebSocket).', 'success');
            resetAudioRecording();
        };
        reader.readAsDataURL(audioBlob);
    } else {
        // Fallback API REST si WebSocket non dispo
        sendAudioViaREST(receiverId, audioBlob);
    }
}

// Adapter sendAudioViaREST pour accepter un blob
function sendAudioViaREST(receiverId, audioBlob) {
    showStatus('Envoi du message audio via REST API...', 'info');
    var reader = new FileReader();
    reader.onload = function() {
        fetch('index.php?controller=message&action=sendAudio', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                audio_data: reader.result // base64
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showStatus('Message audio envoyé avec succès (via API REST).', 'success');
                resetAudioRecording();
            } else {
                showStatus('Erreur lors de l\'envoi du message audio: ' + (data.message || 'Erreur inconnue'), 'danger');
            }
        })
        .catch(error => {
            showStatus('Erreur de connexion: ' + error.message, 'danger');
        });
    };
    reader.readAsDataURL(audioBlob);
}

    
    // Fonction pour afficher un message de statut
    function showStatus(message, type) {
        if (statusMessage) {
            statusMessage.textContent = message;
            statusMessage.className = 'alert mt-3 alert-' + type
            statusMessage.textContent = message;
            statusMessage.className = 'alert mt-3 alert-' + type;
            statusMessage.style.display = 'block';
            
            // Masquer le message après 5 secondes pour les messages de succès ou d'info
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            }
        }
    }
});
</script>
</body>
</html>
