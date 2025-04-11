<div class="container mt-4">
    <h2>Messagerie Instantanée</h2>

    <!-- Affichage des messages reçus -->
    <div id="messages">
        <?php if (empty($messages)): ?>
            <p>Aucun message trouvé.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <strong>De :</strong> <?= htmlspecialchars($message['sender_id']) ?><br>
                    <strong>Message :</strong> <?= nl2br(htmlspecialchars($message['message'])) ?><br>
                    <strong>Date :</strong> <?= $message['created_at'] ?>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- Nouvelle section pour les messages audio -->
<div class="senior-audio-messages">
    <h2>Messages Audio</h2>
    <div class="new-message-alert" id="newAudioAlert" style="display: none;">
        <i class="fas fa-bell"></i> Vous avez un nouveau message audio!
    </div>
    <div class="audio-message-list" id="audioMessageList">
        <!-- Les messages audio seront ajoutés ici dynamiquement -->
        <?php if (isset($audioMessages) && !empty($audioMessages)): ?>
            <?php foreach ($audioMessages as $audioMessage): ?>
                <div class="audio-message-item">
                    <p>Message de: <?= htmlspecialchars($audioMessage['sender_name'] ?? 'Utilisateur') ?></p>
                    <audio controls>
                        <source src="data:audio/wav;base64,<?= $audioMessage['audio_data'] ?>" type="audio/wav">
                        Votre navigateur ne supporte pas la lecture audio.
                    </audio>
                    <p>Reçu le: <?= date('d/m/Y à H:i', strtotime($audioMessage['created_at'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="audio-recorder-senior">
        <button id="seniorRecordButton" class="btn btn-lg btn-primary">
            <i class="fas fa-microphone"></i> Appuyez pour enregistrer un message
        </button>
        <button id="seniorStopButton" class="btn btn-lg btn-danger" disabled>
            <i class="fas fa-stop"></i> Arrêter l'enregistrement
        </button>
        <button id="seniorSendButton" class="btn btn-lg btn-success" disabled>
            <i class="fas fa-paper-plane"></i> Envoyer le message
        </button>
    </div>
</div>
<script>
// Connexion WebSocket pour afficher les messages en temps réel
const socket = new WebSocket("ws://localhost:8080");

socket.addEventListener("message", function(event) {
    const message = JSON.parse(event.data);
    console.log("Message reçu : ", message);

    // Affichage du message dans l'interface utilisateur
    const messageElement = document.createElement('div');
    messageElement.textContent = message.message;
    document.getElementById("messages").appendChild(messageElement);
});


document.addEventListener('DOMContentLoaded', function() {
    let seniorMediaRecorder;
    let seniorAudioChunks = [];
    let seniorAudioBlob;
    let seniorWebsocket = new WebSocket('ws://<?= $_SERVER['HTTP_HOST'] ?>:8080');
    
    const seniorRecordButton = document.getElementById('seniorRecordButton');
    const seniorStopButton = document.getElementById('seniorStopButton');
    const seniorSendButton = document.getElementById('seniorSendButton');
    const audioMessageList = document.getElementById('audioMessageList');
    const newAudioAlert = document.getElementById('newAudioAlert');
    
    // Cacher l'alerte au démarrage
    if (newAudioAlert) {
        newAudioAlert.style.display = 'none';
    }
    
    seniorWebsocket.onopen = function(e) {
        console.log('Connexion WebSocket établie pour le senior');
        
        // Identifier le senior
        seniorWebsocket.send(JSON.stringify({
            type: 'identify',
            userId: '<?= $_SESSION['user_id'] ?>'
        }));
    };
    
    seniorWebsocket.onmessage = function(e) {
        const data = JSON.parse(e.data);
        
        if (data.type === 'audio') {
            // Jouer un son de notification
            const notificationSound = new Audio('/SunnyLink/public/audio/notif-sound.mp3');
            notificationSound.play();
            
            // Afficher l'alerte
            if (newAudioAlert) {
                newAudioAlert.style.display = 'block';
            }
            
            // Créer un élément pour le message audio
            const messageElement = document.createElement('div');
            messageElement.className = 'audio-message-item';
            
            const senderInfo = document.createElement('p');
            senderInfo.textContent = `Message de: ${data.sender_name || 'Utilisateur'}`;
            
            const audioElement = document.createElement('audio');
            audioElement.controls = true;
            audioElement.src = `data:audio/wav;base64,${data.audioData}`;
            
            const timestamp = document.createElement('p');
            const date = new Date(data.timestamp * 1000);
            timestamp.textContent = `Reçu le: ${date.toLocaleDateString()} à ${date.toLocaleTimeString()}`;
            
            messageElement.appendChild(senderInfo);
            messageElement.appendChild(audioElement);
            messageElement.appendChild(timestamp);
            
            if (audioMessageList) {
                audioMessageList.prepend(messageElement);
            }
            
            // Masquer l'alerte quand on clique dessus
            if (newAudioAlert) {
                newAudioAlert.addEventListener('click', function() {
                    newAudioAlert.style.display = 'none';
                });
            }
        }
    };
    
    if (seniorRecordButton) {
        seniorRecordButton.addEventListener('click', async () => {
            seniorAudioChunks = [];
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                seniorMediaRecorder = new MediaRecorder(stream);
                
                seniorMediaRecorder.ondataavailable = (event) => {
                    seniorAudioChunks.push(event.data);
                };
                
                seniorMediaRecorder.onstop = () => {
                    seniorAudioBlob = new Blob(seniorAudioChunks, { type: 'audio/wav' });
                    seniorSendButton.disabled = false;
                    
                    // Créer un élément audio pour prévisualiser
                    const audioPreview = document.createElement('audio');
                    audioPreview.src = URL.createObjectURL(seniorAudioBlob);
                    audioPreview.controls = true;
                    document.querySelector('.audio-recorder-senior').appendChild(audioPreview);
                };
                
                seniorMediaRecorder.start();
                seniorRecordButton.disabled = true;
                seniorStopButton.disabled = false;
            } catch (err) {
                console.error('Erreur lors de l\'accès au microphone:', err);
                alert('Erreur: Impossible d\'accéder au microphone');
            }
        });
    }
    
    if (seniorStopButton) {
        seniorStopButton.addEventListener('click', () => {
            if (seniorMediaRecorder && seniorMediaRecorder.state !== 'inactive') {
                seniorMediaRecorder.stop();
                seniorRecordButton.disabled = false;
                seniorStopButton.disabled = true;
            }
        });
    }
    
    if (seniorSendButton) {
        seniorSendButton.addEventListener('click', () => {
            if (seniorAudioBlob) {
                const reader = new FileReader();
                reader.readAsDataURL(seniorAudioBlob);
                reader.onloadend = function() {
                    const base64data = reader.result.split(',')[1];
                    
                    // Récupérer l'ID du destinataire (family member)
                    // Note: Vous devrez adapter cette partie pour récupérer le bon destinataire
                    const familyMemberId = prompt("Entrez l'ID du membre de famille destinataire:");
                    
                    if (familyMemberId) {
                        seniorWebsocket.send(JSON.stringify({
                            type: 'audio',
                            sender: '<?= $_SESSION['user_id'] ?>',
                            sender_name: '<?= $_SESSION['name'] ?>',
                            recipient: familyMemberId,
                            audioData: base64data
                        }));
                        
                        // Enregistrer le message audio via AJAX
                        fetch('index.php?controller=message&action=sendAudio', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                receiver_id: familyMemberId,
                                audio_data: base64data
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('Message audio envoyé avec succès!');
                                seniorSendButton.disabled = true;
                                
                                // Supprimer l'aperçu audio
                                const audioPreview = document.querySelector('.audio-recorder-senior audio');
                                if (audioPreview) {
                                    audioPreview.remove();
                                }
                            } else {
                                alert('Erreur lors de l\'envoi du message audio: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            alert('Erreur lors de l\'envoi du message audio');
                        });
                    }
                };
            }
        });
    }
});


</script>
