<?php
$title = "Galerie de vos souvenirs";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Quelques styles pour la galerie */
    .card {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <h2><?= htmlspecialchars($title) ?></h2>
  <!-- Conteneur vide qui sera rempli via AJAX -->
  <div id="gallery" class="row"></div>
</div>

<script>
// On récupère l'ID utilisateur depuis la variable globale définie par le contrôleur
const userId = <?= json_encode($GLOBALS['userId'] ?? null) ?>;

function speak(text) {
  const msg = new SpeechSynthesisUtterance(text);
  msg.lang = 'fr-FR';
  window.speechSynthesis.speak(msg);
}

function renderGallery(photos) {
  const gallery = document.getElementById('gallery');
  gallery.innerHTML = ""; // On vide le conteneur avant insertion
  photos.forEach(photo => {
    // Création d'un conteneur pour chaque photo
    const col = document.createElement('div');
    col.className = "col-md-4 mb-4";
    
    const card = document.createElement('div');
    card.className = "card";
    
    const img = document.createElement('img');
    img.src = photo.url;
    img.className = "card-img-top";
    img.alt = "Photo";
    
    const body = document.createElement('div');
    body.className = "card-body";
    
    const message = document.createElement('p');
    message.className = "card-text";
    message.innerText = "📝 " + photo.message;
    
    const date = document.createElement('p');
    date.className = "text-muted";
    date.innerText = "📅 Envoyée le : " + new Date(photo.created_at).toLocaleString();
    
    const playBtn = document.createElement('button');
    playBtn.className = "btn btn-outline-primary";
    playBtn.innerText = "🔊 Lire le message";
    playBtn.onclick = () => speak(photo.message);
    
    // On assemble les éléments
    body.appendChild(message);
    body.appendChild(date);
    body.appendChild(playBtn);
    card.appendChild(img);
    card.appendChild(body);
    col.appendChild(card);
    gallery.appendChild(col);
    
    // Lecture automatique (optionnelle) à l'arrivée de chaque photo
    // speak(photo.message);
  });
}

function loadGallery() {
  // Vérifie que l'ID utilisateur est défini
  if (!userId) {
    console.error("ID utilisateur non spécifié.");
    return;
  }
  
  fetch(`index.php?controller=photo&action=getPhotos&id=${userId}`)
    .then(response => response.json())
    .then(data => {
      renderGallery(data);
    })
    .catch(error => console.error("Erreur lors du fetch:", error));
}

// Chargement initial de la galerie via AJAX
loadGallery();

// Vous pouvez également rafraîchir périodiquement la galerie si besoin
// setInterval(loadGallery, 30000); // toutes les 30 secondes par exemple
</script>
</body>
</html>
