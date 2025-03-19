<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e0f7fa;
        }
        .container {
            background-color: #dcedc8;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center my-4">Ajout d'un événement</h1>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($erreur, ENT_QUOTES); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($addForm)): ?>
            <?php echo $addForm->render(); ?>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form action="index.php?controller=event&action=add" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de l'événement</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date et heure</label>
                            <input type="datetime-local" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="lieu" class="form-label">Lieu</label>
                            <input type="text" class="form-control" id="lieu" name="lieu" required>
                        </div>
                        <div class="mb-3">
                            <label for="participants" class="form-label">Participants</label>
                            <input type="text" class="form-control" id="participants" name="participants" placeholder="Rechercher des contacts">
                        </div>
                        <div class="mb-3">
                            <h5 class="mb-3">Notifications</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notificationEmail" name="notificationEmail">
                                <label class="form-check-label" for="notificationEmail">
                                    Email
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notificationSMS" name="notificationSMS">
                                <label class="form-check-label" for="notificationSMS">
                                    SMS
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h5 class="mb-3">Options d'enregistrement et d'envoi</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="alertFloat" name="alertFloat">
                                <label class="form-check-label" for="alertFloat">
                                    Activer alerte flottante
                                </label>
                            </div>
                            <div class="mb-3">
                                <label for="alertTime" class="form-label">Temps avant alerte :</label>
                                <select class="form-select" id="alertTime" name="alertTime">
                                    <option value="1h">1 heure avant</option>
                                    <option value="30m">30 minutes avant</option>
                                    <option value="15m">15 minutes avant</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Créer l'événement</button>
                        <button type="button" class="btn btn-secondary">Annuler</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
