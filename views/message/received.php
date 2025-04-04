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
