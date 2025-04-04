<form id="sendMessageForm" method="POST" action="index.php?controller=message&action=send">
    <!-- Champ pour choisir le destinataire -->
    <div class="mb-3">
        <label for="receiver_id" class="form-label">Sélectionnez un destinataire :</label>
        <select name="receiver_id" id="receiver_id" class="form-control" required>
            <option value="">-- Choisissez --</option>
            <option value="4">Jean Dupont</option>
            <option value="5">Autre Destinataire</option>
        </select>
    </div>

    <!-- Champ pour le message écrit -->
    <div class="mb-3">
        <label for="message" class="form-label">Message :</label>
        <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Envoyer le message</button>
</form>
