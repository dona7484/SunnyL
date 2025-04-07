<?php
$title = "Envoyer un message - SunnyLink";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Envoyer un message</h2>
        <form id="sendMessageForm" method="POST" action="index.php?controller=message&action=send">
    <div class="mb-3">
        <label for="receiver_id" class="form-label">Sélectionnez un destinataire :</label>
        <select name="receiver_id" id="receiver_id" class="form-control" required>
            <option value="">-- Choisissez --</option>
            <option value="4">Jean Dupont</option>
            <option value="5">Marie Curie</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">Message écrit :</label>
        <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Envoyer le message</button>
</form>

    </div>

</body>
</html>

<script>
// Connexion au serveur WebSocket
const socket = new WebSocket("ws://localhost:8080");

// Lorsque le WebSocket est ouvert
socket.addEventListener("open", function(event) {
    console.log("Connecté au serveur WebSocket.");
});

// Lorsque le serveur envoie un message
// Dans send.php
socket.addEventListener('message', function(event) {
    const response = JSON.parse(event.data);
    if(response.type === 'delivery_confirmation') {
        console.log('Message délivré avec ID:', response.message_id);
    }
});


// Gestion du formulaire d'envoi de message
document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Récupérer les données du formulaire
    const formData = new FormData();
    formData.append('receiver_id', document.getElementById('receiver_id').value);
    formData.append('message', document.getElementById('message').value);

    // Envoi via fetch API
    fetch('index.php?controller=message&action=send', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Message envoyé avec succès');
            document.getElementById('message').value = '';
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
});

</script>
