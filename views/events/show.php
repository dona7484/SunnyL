<?php $title = htmlspecialchars("Evenements - " . $events->getTitle(), ENT_QUOTES); ?>
<article class="row justify-content-center text-center">
    <h1 class="col-12"><?php echo htmlspecialchars($events->getTitle(), ENT_QUOTES); ?></h1>
    <p>Date de publication : <?php echo date("d/m/Y", strtotime($events->getDate())); ?></p>
    <p><?php echo htmlspecialchars($events->getDescription(), ENT_QUOTES); ?></p>
</article>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'événement</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e0f7fa;
        }
        .event-card, .participants-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .participants-card img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }
        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .buttons .delete-button {
            background-color: #f44336;
            color: white;
        }
        .buttons .modify-button {
            background-color: #f9a825;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Détails de l'événement</h1>
        <div class="event-card">
            <h2>Réunion de famille</h2>
            <p><strong>Date:</strong> 25 décembre 2023</p>
            <p><strong>Heure:</strong> 15:00 - 16:00</p>
            <h3>Description</h3>
            <p>Rejoignez-nous pour une réunion de famille festive afin de célébrer les fêtes. Profitez de la nourriture, de la musique et de la bonne compagnie en créant des souvenirs inoubliables ensemble.</p>
        </div>
        <div class="participants-card">
            <h2>Participants</h2>
            <div class="participant d-flex align-items-center mb-2">
                <img src="https://via.placeholder.com/50" alt="Marc">
                <span>Marc</span>
            </div>
            <div class="participant d-flex align-items-center">
                <img src="https://via.placeholder.com/50" alt="Marguerite Dupond">
                <span>Marguerite Dupond</span>
            </div>
        </div>
        <div class="buttons">
            <button class="delete-button">Supprimer l'événement</button>
            <button class="modify-button">Modifier Événement</button>
        </div>
    </div>
    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
