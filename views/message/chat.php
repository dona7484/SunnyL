<!-- <?php
$title = "Messagerie Instantanée";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    #chatContainer {
      border: 1px solid #ddd;
      padding: 15px;
      height: 400px;
      overflow-y: auto;
      background: #f9f9f9;
    }
    #chatInput {
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container mt-4">
    <h2><?= htmlspecialchars($title) ?></h2>
    <!-- Zone d'affichage des messages -->
    <div id="chatContainer"></div>
    <!-- Formulaire pour envoyer un message -->
    <div id="chatInput" class="input-group">
      <input type="text" id="messageText" class="form-control" placeholder="Tapez votre message...">
      <button id="sendBtn" class="btn btn-primary">Envoyer</button>
    </div>
  </div>

  <script>
    // Connexion au serveur WebSocket
    // const socket = new WebSocket("ws://localhost:8081");
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
const host = window.location.hostname;
const socket = new WebSocket(`${protocol}//${host}:8080/`);

    socket.addEventListener("open", () => {
      console.log("Connecté au serveur WebSocket.");
    });

    // Lorsque le serveur envoie un message, on l'affiche dans le chat
    socket.addEventListener("message", (event) => {
      console.log("Message reçu:", event.data);
      appendMessage(event.data);
    });

    // Fonction d'ajout de message dans le chat
    function appendMessage(message) {
      const chatContainer = document.getElementById("chatContainer");
      const messageElement = document.createElement("p");
      messageElement.textContent = message;
      chatContainer.appendChild(messageElement);
      chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Fonction pour envoyer un message via WebSocket
    function sendMessage() {
      const messageTextInput = document.getElementById("messageText");
      const messageText = messageTextInput.value.trim();
      if (messageText === "") return;

      // Ici, on définit manuellement l'ID de l'expéditeur et du destinataire pour l'exemple.
      // Dans une intégration réelle, vous récupéreriez ces valeurs depuis la session ou une variable JavaScript.
      const messageObj = {
        sender: 1,      // Remplacez par l'ID réel de l'expéditeur
        receiver: 4,    // Remplacez par l'ID réel du destinataire
        message: messageText
      };

      socket.send(JSON.stringify(messageObj));
      messageTextInput.value = "";
    }

    // Gestion de l'événement clic sur le bouton "Envoyer"
    document.getElementById("sendBtn").addEventListener("click", sendMessage);

    // Permet d'envoyer le message en appuyant sur "Entrée"
    document.getElementById("messageText").addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        sendMessage();
      }
    });
  </script>
</body>
</html> -->
