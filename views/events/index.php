<?php
// Inclure et enregistrer l'autoloader
require_once '../Autoloader.php';
Autoloader::register();
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Maintenant, vous pouvez utiliser vos classes sans require_once
$eventModel = new EventModel();
$event = new Event();

// Exemple d'utilisation de la classe EventModel
$list = $eventModel->findAll();

// Définir le titre de la page
$title = "Liste des Événements";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
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
    <a href="index.php?controller=home&action=dashboard" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        <h2>Liste des Événements</h2>
        <a href="index.php?controller=event&action=add" class="btn btn-primary mb-3">Ajouter un événement</a>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Titre</th>
                    <th scope="col">Description</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($list)): ?>
    <div class="alert alert-warning">Aucun événement trouvé. <a href="index.php?controller=event&action=add">Créer le premier événement</a></div>
<?php else: ?>
    <!-- Afficher le tableau existant -->
<?php endif; ?>
                <?php foreach ($list as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
    <?= htmlspecialchars($event->getTitle(), ENT_QUOTES) ?>
</td>
                        <td><?= htmlspecialchars($event->getDescription(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($event->getDate(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="index.php?controller=event&action=show&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>">Détails</a>
                            <a href="index.php?controller=event&action=update&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                            <a href="index.php?controller=event&action=delete&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>">Supprimer</a>
                        </td>
                        <td>
    <?= htmlspecialchars($event->getTitle(), ENT_QUOTES) ?>
    <?php if ($event->isRead()): ?>
        <span class="badge bg-success">Lu</span>
    <?php elseif ($event->isTriggered()): ?>
        <span class="badge bg-warning">Alerté</span>
    <?php else: ?>
        <span class="badge bg-warning">Non alerté</span>
    <?php endif; ?>
</td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
