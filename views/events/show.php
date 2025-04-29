<?php
// Assurez-vous que les variables ne sont pas nulles avant de les passer à htmlspecialchars()
$title = isset($events) && $events->getTitle() ? htmlspecialchars($events->getTitle(), ENT_QUOTES) : 'Titre non disponible';
$description = isset($events) && $events->getDescription() ? htmlspecialchars($events->getDescription(), ENT_QUOTES) : 'Description non disponible';
$lieu = isset($events) && $events->getLieu() ? htmlspecialchars($events->getLieu(), ENT_QUOTES) : 'Lieu non spécifié';
$recurrence = isset($events) && $events->getRecurrence() ? htmlspecialchars($events->getRecurrence(), ENT_QUOTES) : 'Pas de récurrence';
$alertTime = isset($events) && $events->getAlertTime() ? htmlspecialchars($events->getAlertTime(), ENT_QUOTES) : 'Pas d\'alerte';
$notificationMessage = isset($events) && $events->getNotificationMessage() ? htmlspecialchars($events->getNotificationMessage(), ENT_QUOTES) : 'Aucun message de notification';

// Récupérer les participants
$participants = isset($participants) ? $participants : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e0f7fa;
            font-family: 'Arial', sans-serif;
        }


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations sur l'événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style personnalisé */
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center"><?php echo $title; ?></h1>

    <div class="event-card">
        <h3>Informations sur l'événement</h3>
        <p>Date de l'événement : <?php echo date("d/m/Y H:i", strtotime($events->getDate())); ?></p>
        <p>Description : <?php echo $description; ?></p>
        <p>Lieu : <?php echo $lieu ? $lieu : 'Lieu non spécifié'; ?></p>
        <p>Fréquence : <?php echo $recurrence ? $recurrence : 'Pas de récurrence'; ?></p>
        <p>Alerte avant : <?php echo $alertTime ? $alertTime : 'Pas d\'alerte définie'; ?></p>
        <p>Message de notification : <?php echo $notificationMessage ? $notificationMessage : 'Aucun message personnalisé'; ?></p>
        <p><strong>Statut :</strong> 
    <?php if ($events->isRead()): ?>
        <span class="badge bg-success">Lu</span>
    <?php elseif ($events->isTriggered()): ?>
        <span class="badge bg-warning">Alerté</span>
    <?php else: ?>
        <span class="badge bg-warning">Non alerté</span>
    <?php endif; ?>
</p>


    </div>

    <div class="participants-card">
        <h3>Participants</h3>
        <?php if (isset($participants) && count($participants) > 0): ?>
    <ul>
        <?php foreach ($participants as $participant): ?>
            <li><?php echo htmlspecialchars($participant['participant_name'], ENT_QUOTES); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun participant ajouté.</p>
<?php endif; ?>
    </div>
    
    <div class="buttons">
        <button class="btn btn-primary">Modifier l'événement</button>
        <button class="btn btn-danger">Supprimer l'événement</button>
    </div>
</div>

</body>
</html>
