<?php
$title = htmlspecialchars("Modification de l'événement - " . $events->getTitle(), ENT_QUOTES);
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

        .container {
            max-width: 900px;
            margin-top: 50px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .form-label {
            font-weight: bold;
        }

        .btn {
            font-weight: bold;
            border-radius: 50px;
            padding: 10px 20px;
        }

        .btn-submit {
            background-color: #f9a825;
            color: white;
            border: none;
            margin-top: 20px;
        }

        .btn-cancel {
            background-color: #f44336;
            color: white;
            border: none;
            margin-top: 20px;
        }

        .event-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .event-description {
            font-size: 1.1rem;
            color: #555;
        }

        .event-info {
            margin-top: 20px;
        }

        .date {
            font-size: 1.2rem;
            color: #00796b;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .form-control {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="text-center event-title">Modifier l'événement : <?php echo htmlspecialchars($events->getTitle(), ENT_QUOTES); ?></h2>
        <form action="index.php?controller=event&action=update&id=<?php echo htmlspecialchars($events->getId(), ENT_QUOTES); ?>" method="POST">
            
            <!-- Titre de l'événement -->
            <div class="mb-3">
                <label for="title" class="form-label">Titre de l'événement</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($events->getTitle(), ENT_QUOTES); ?>" required>
            </div>

            <!-- Date et heure -->
            <div class="mb-3">
                <label for="date" class="form-label">Date et heure</label>
                <input type="datetime-local" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($events->getDate(), ENT_QUOTES); ?>" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($events->getDescription(), ENT_QUOTES); ?></textarea>
            </div>

            <!-- Lieu -->
            <div class="mb-3">
                <label for="lieu" class="form-label">Lieu</label>
                <input type="text" class="form-control" id="lieu" name="lieu" value="<?php echo htmlspecialchars($events->getLieu() ?? '', ENT_QUOTES); ?>" required>
            </div>

            <!-- Fréquence de l'événement -->
            <div class="mb-3">
                <label for="recurrence" class="form-label">Fréquence de l'événement</label>
                <select class="form-select" id="recurrence" name="recurrence">
                    <option value="none" <?php echo $events->getRecurrence() == 'none' ? 'selected' : ''; ?>>Pas de récurrence</option>
                    <option value="daily" <?php echo $events->getRecurrence() == 'daily' ? 'selected' : ''; ?>>Quotidien</option>
                    <option value="weekly" <?php echo $events->getRecurrence() == 'weekly' ? 'selected' : ''; ?>>Hebdomadaire</option>
                    <option value="monthly" <?php echo $events->getRecurrence() == 'monthly' ? 'selected' : ''; ?>>Mensuel</option>
                </select>
            </div>

            <!-- Temps avant alerte -->
            <div class="mb-3">
                <label for="alertTime" class="form-label">Temps avant alerte</label>
                <select class="form-select" id="alertTime" name="alertTime">
                    <option value="1h" <?php echo $events->getAlertTime() == '1h' ? 'selected' : ''; ?>>1 heure avant</option>
                    <option value="30m" <?php echo $events->getAlertTime() == '30m' ? 'selected' : ''; ?>>30 minutes avant</option>
                    <option value="15m" <?php echo $events->getAlertTime() == '15m' ? 'selected' : ''; ?>>15 minutes avant</option>
                </select>
            </div>

            <!-- Message de notification personnalisé -->
            <div class="mb-3">
                <label for="notificationMessage" class="form-label">Message de notification personnalisé</label>
                <input type="text" class="form-control" id="notificationMessage" name="notificationMessage" value="<?php echo htmlspecialchars($events->getNotificationMessage() ?? '', ENT_QUOTES); ?>">
            </div>

            <!-- Boutons de soumission et annulation -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-submit">Mettre à jour l'événement</button>
                <a href="index.php?controller=event&action=index" class="btn btn-cancel">Annuler</a>
            </div>
        </form>
    </div>
</div>

<!-- Inclure Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
