<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réception de l'Événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Événement Reçu</h2>
        
        <!-- Vérification si l'événement est disponible -->
        <?php if (isset($events) && count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <!-- Affichage des détails de l'événement -->
                <p><strong>Titre :</strong> <?= htmlspecialchars($event['title'] ?? 'Titre non disponible') ?></p>
                <p><strong>Description :</strong> <?= htmlspecialchars($event['description'] ?? 'Description non disponible') ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($event['date'] ?? 'Date non disponible') ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($event['lieu'] ?? 'Lieu non disponible') ?></p>

                <!-- Participants -->
                <h4>Participants</h4>
                <?php if (isset($participants) && count($participants) > 0): ?>
                    <ul>
                        <?php foreach ($participants as $participant): ?>
                            <li><?= htmlspecialchars($participant['participant_name'] ?? 'Nom non disponible') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun participant disponible.</p>
                <?php endif; ?>

                <!-- Notifications -->
                <h4>Notifications</h4>
                <?php if (isset($notifications) && count($notifications) > 0): ?>
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li><?= htmlspecialchars($notification['message'] ?? 'Aucun message de notification') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucune notification disponible.</p>
                <?php endif; ?>

                <!-- Bouton de confirmation -->
                <a href="index.php?controller=event&action=index" class="btn btn-primary">Retour à la liste des événements</a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun événement trouvé.</p>
            <a href="index.php?controller=event&action=index" class="btn btn-primary">Retour à la liste des événements</a>
        <?php endif; ?>
    </div>
</body>
</html>
