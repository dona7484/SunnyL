<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une notification</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        form {
            display: flex;
            flex-direction: column;
            width: 300px;
            margin: auto;
        }
        label {
            margin-bottom: 5px;
        }
        input {
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Créer une notification</h2>
    <form action="index.php?controller=notification&action=create" method="POST" id="notificationForm">
        <label for="userId">User ID:</label>
        <input type="text" name="userId" id="userId" required>

        <label for="message">Message:</label>
        <input type="text" name="message" id="message" required>

        <button type="submit">Envoyer Notification</button>
    </form>

    <script>
        $(document).ready(function() {
            // Form submission via AJAX
            $('#notificationForm').on('submit', function(e) {
                e.preventDefault(); // Prevent form submission

                var userId = $('#userId').val();
                var message = $('#message').val();

                // Envoi de la notification via fetch
                fetch('index.php?controller=notification&action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'userId': userId,
                        'message': message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Notification envoyée avec succès!");
                    } else {
                        alert("Erreur: " + data.error);
                    }
                })
                .catch(error => console.error('Erreur:', error));
            });
        });
    </script>
</body>
</html>
