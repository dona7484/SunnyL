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
</script>
