<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'un événement</title>
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

        .participants-list {
            margin-top: 10px;
            padding-left: 0;
        }

        .participants-list li {
            list-style-type: none;
            background-color: #f1f1f1;
            margin-bottom: 5px;
            padding: 8px;
            border-radius: 5px;
        }

        .btn-remove-participant {
            color: #f44336;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="text-center mb-4">Ajout d'un événement</h2>
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
                <label for="location" class="form-label">Lieu</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>

            <div class="mb-3">
                <label for="recurrence" class="form-label">Fréquence de l'événement</label>
                <select class="form-select" id="recurrence" name="recurrence">
                    <option value="none">Pas de récurrence</option>
                    <option value="daily">Quotidien</option>
                    <option value="weekly">Hebdomadaire</option>
                    <option value="monthly">Mensuel</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="alertTime" class="form-label">Temps avant alerte</label>
                <select class="form-select" id="alertTime" name="alertTime">
                    <option value="1h">1 heure avant</option>
                    <option value="30m">30 minutes avant</option>
                    <option value="15m">15 minutes avant</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="notificationMessage" class="form-label">Message de notification personnalisé</label>
                <input type="text" class="form-control" id="notificationMessage" name="notificationMessage">
            </div>

            <!-- Participants -->
            <div class="mb-3">
    <label for="participants" class="form-label">Participants (facultatif)</label>
    <input type="text" class="form-control" id="participants" name="participants[]" placeholder="Ajouter un participant">
    <ul class="participants-list" id="participantsList"></ul>
    <button type="button" class="btn btn-outline-primary mt-2" id="addParticipant">Ajouter un participant</button>
</div>


            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-submit">Créer l'événement</button>
                <button type="button" class="btn btn-cancel">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Inclure Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script pour ajouter un participant
    document.getElementById('addParticipant').addEventListener('click', function() {
        var participantInput = document.getElementById('participants');
        var participantName = participantInput.value.trim();

        if (participantName !== '') {
            var listItem = document.createElement('li');
            listItem.textContent = participantName;

            // Ajouter un bouton pour supprimer le participant
            var removeBtn = document.createElement('span');
            removeBtn.textContent = '❌';
            removeBtn.classList.add('btn-remove-participant');
            removeBtn.onclick = function() {
                listItem.remove();
            };

            listItem.appendChild(removeBtn);
            document.getElementById('participantsList').appendChild(listItem);

            // Effacer le champ de texte après ajout
            participantInput.value = '';
        }
    });
</script>
</body>
</html>
