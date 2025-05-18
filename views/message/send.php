<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?controller=auth&action=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un message - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .back-dashboard-btn {
            display: inline-flex;
            align-items: center;
            background-color: #4a4a4a;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            border: none;
            margin-bottom: 20px;
        }

        .back-dashboard-btn:hover {
            background-color: #333333;
            color: white;
            text-decoration: none;
        }

        .back-dashboard-btn i {
            margin-right: 8px;
        }
        
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
        
        #status-message {
            display: none;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container message-container mt-4">
             <a href="index.php?controller=home&action=<?= ($_SESSION['role'] === 'senior') ? 'dashboard' : 'family_dashboard' ?>" class="back-button">
    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
</a>
        
        <h1 class="mb-4">Envoyer un message</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <div class="message-form">
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
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
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
                        
                        <div class="audio-status alert alert-info mb-3" style="display:none;">
                            <span id="recordingStatus">En attente de l'enregistrement...</span>
                            <div id="audioWaveform" class="mt-2"></div>
                            <div id="recordingTimer" class="mt-1">00:00</div>
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
            
            <div id="status-message" class="alert"></div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/audio-recorder.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments DOM pour l'enregistrement audio
        const startButton = document.getElementById('startRecording');
        const stopButton = document.getElementById('stopRecording');
        const sendButton = document.getElementById('sendAudio');
        const statusElement = document.getElementById('recordingStatus');
        const timerElement = document.getElementById('recordingTimer');
        const previewContainer = document.querySelector('.audio-preview');
        const audioPreview = document.getElementById('audioPreview');
        const statusContainer = document.querySelector('.audio-status');
        const statusMessage = document.getElementById('status-message');
        
        // Vérifier si les éléments existent avant de continuer
        if (!startButton || !stopButton || !sendButton) {
            console.error("Éléments du DOM non trouvés");
            return;
        }
        
        // Variables pour l'enregistrement
        let audioRecorder = null;
        let audioBlob = null;
        let recordingInterval = null;
        let recordingStartTime = 0;
        
        // Vérifier si l'enregistrement audio est supporté
        if (!AudioRecorder || !AudioRecorder.isSupported()) {
            startButton.disabled = true;
            startButton.textContent = "Enregistrement audio non supporté par votre navigateur";
            console.error("L'enregistrement audio n'est pas pris en charge par ce navigateur");
            return;
        }
        
        // Fonction pour afficher un message de statut
        function showStatus(message, type) {
            if (statusMessage) {
                statusMessage.textContent = message;
                statusMessage.className = 'alert alert-' + type;
                statusMessage.style.display = 'block';
                
                // Masquer le message après 5 secondes pour les messages de succès ou d'info
                if (type === 'success' || type === 'info') {
                    setTimeout(() => {
                        statusMessage.style.display = 'none';
                    }, 5000);
                }
            }
        }
        
        // Initialiser l'enregistreur audio
        audioRecorder = new AudioRecorder({
            onStart: function() {
                console.log("Enregistrement démarré");
                // Mettre à jour l'UI
                startButton.disabled = true;
                stopButton.disabled = false;
                statusElement.textContent = "Enregistrement en cours...";
                statusContainer.style.display = 'block';
                previewContainer.style.display = 'none';
                sendButton.disabled = true;
                
                // Démarrer le timer
                recordingStartTime = Date.now();
                recordingInterval = setInterval(updateRecordingTime, 1000);
            },
            onStop: function(blob) {
                console.log("Enregistrement terminé, taille:", blob.size);
                audioBlob = blob;
                
                // Arrêter le timer
                clearInterval(recordingInterval);
                
                // Mettre à jour l'UI
                startButton.disabled = false;
                stopButton.disabled = true;
                statusElement.textContent = "Enregistrement terminé";
                
                // Créer l'URL pour la prévisualisation
                const audioURL = URL.createObjectURL(blob);
                audioPreview.src = audioURL;
                previewContainer.style.display = 'block';
                
                // Activer le bouton d'envoi
                sendButton.disabled = false;
            },
            onError: function(error) {
                console.error("Erreur lors de l'enregistrement:", error);
                statusElement.textContent = "Erreur : " + (error.message || "Problème d'accès au microphone");
                statusElement.style.color = "red";
                statusContainer.style.display = 'block';
                
                // Réinitialiser l'UI
                startButton.disabled = false;
                stopButton.disabled = true;
                
                // Arrêter le timer si en cours
                if (recordingInterval) {
                    clearInterval(recordingInterval);
                }
            }
        });
        
        // Fonction pour mettre à jour le timer d'enregistrement
        function updateRecordingTime() {
            const elapsedTime = Math.floor((Date.now() - recordingStartTime) / 1000);
            const minutes = Math.floor(elapsedTime / 60).toString().padStart(2, '0');
            const seconds = (elapsedTime % 60).toString().padStart(2, '0');
            timerElement.textContent = `${minutes}:${seconds}`;
        }
        
        // Gestionnaires d'événements
        startButton.addEventListener('click', function() {
            audioRecorder.start();
        });
        
        stopButton.addEventListener('click', function() {
            audioRecorder.stop();
        });
        
        sendButton.addEventListener('click', function() {
            if (!audioBlob) {
                showStatus("Aucun enregistrement disponible", "warning");
                return;
            }
            
            const receiverId = document.getElementById('audio_receiver_id').value;
            if (!receiverId) {
                showStatus("Veuillez sélectionner un destinataire", "warning");
                return;
            }
            
            // Convertir le Blob en base64 pour l'envoi
            const reader = new FileReader();
            reader.readAsDataURL(audioBlob);
            reader.onloadend = function() {
                const base64Audio = reader.result.split(',')[1]; // Enlever le préfixe "data:audio/webm;base64,"
                
                // Afficher un message d'envoi en cours
                sendButton.disabled = true;
                sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                showStatus("Envoi du message audio en cours...", "info");
                
                // Envoyer via fetch API
                fetch('index.php?controller=message&action=sendAudio', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: receiverId,
                        audio_data: base64Audio
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Réinitialiser l'interface
                        previewContainer.style.display = 'none';
                        statusContainer.style.display = 'none';
                        sendButton.disabled = false;
                        sendButton.innerHTML = 'Envoyer le message audio';
                        
                        // Afficher un message de succès
                        showStatus("Message audio envoyé avec succès !", "success");
                        
                        // Rediriger après un court délai
                        setTimeout(() => {
                            window.location.href = 'index.php?controller=message&action=sent';
                        }, 1500);
                    } else {
                        throw new Error(data.message || "Erreur lors de l'envoi du message audio");
                    }
                })
                .catch(error => {
                    console.error("Erreur:", error);
                    showStatus("Erreur lors de l'envoi du message audio: " + error.message, "danger");
                    sendButton.disabled = false;
                    sendButton.innerHTML = 'Envoyer le message audio';
                });
            };
        });
        
        // Activer les onglets Bootstrap
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Désactiver tous les onglets
                tabLinks.forEach(item => {
                    item.classList.remove('active');
                });
                
                // Masquer tous les contenus d'onglet
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Activer l'onglet cliqué
                this.classList.add('active');
                
                // Afficher le contenu correspondant
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.classList.add('show', 'active');
                }
            });
        });
    });
    </script>
</body>
</html>