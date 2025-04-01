<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - SunnyLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Styles pour l'affichage en tuiles */
    body {
      background: #f7f7f7;
      margin: 0;
      padding: 0;
    }
    #header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #FFD700;
      padding: 10px 20px;
    }
    #header img {
      width: 40px;
      height: 40px;
      cursor: pointer;
      margin-right: 10px;
    }
    #header .logo {
      display: flex;
      align-items: center;
      font-size: 1.8rem;
      font-weight: bold;
    }
    #dashboardContainer {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 2rem;
      margin-top: 2rem;
    }
    .menuItem {
      width: 140px;
      height: 140px;
      background: white;
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      cursor: pointer;
      text-align: center;
      transition: transform 0.2s;
    }
    .menuItem:hover {
      transform: scale(1.05);
    }
    .menuItem img {
      width: 60px;
      height: 60px;
    }
    .menuItem span {
      margin-top: 0.5rem;
      font-size: 1.1rem;
    }
    /* Modal pour le diaporama en plein écran */
    #slideshowModal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    #slideshowModal img {
      max-width: 80%;
      max-height: 80%;
      border: 5px solid #fff;
      border-radius: 10px;
    }
  </style>
</head>
<body>
<!-- Barre supérieure -->
<div id="header">
  <div class="logo">
    <img src="images/IconeSourdine.png" alt="Volume" onclick="toggleVolume()">
    SunnyLink
  </div>
  <button id="monCompteBtn" class="btn btn-outline-dark">Mon compte</button>
</div>

<!-- Partie spécifique selon le rôle -->
<div class="container mt-4">
  <?php if ($role === 'senior'): ?>
    <h2>Dashboard Senior</h2>
    <p>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?>.</p>
    <h4>Vos proches :</h4>
    <?php if (!empty($familyMembers)): ?>
      <ul>
        <?php foreach ($familyMembers as $fm): ?>
          <li><?= htmlspecialchars($fm['name']) ?> (<?= htmlspecialchars($fm['email']) ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aucun proche associé pour l'instant.</p>
    <?php endif; ?>
  <?php else: ?>
    <h2>Dashboard Family Member</h2>
    <p>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?>.</p>
  <?php endif; ?>
</div>

<!-- Tuiles principales du dashboard -->
<div id="dashboardContainer">
  <div class="menuItem" onclick="openPhotos()">
    <img src="images/IconePhoto.png" alt="Photos">
    <span>Photos</span>
  </div>
  <div class="menuItem" onclick="openMusic()">
    <img src="images/iconeMusic.png" alt="Musique">
    <span>Musique</span>
  </div>
  <div class="menuItem" onclick="openMessages()">
    <img src="images/iconeMessage.png" alt="Messages">
    <span>Messages</span>
  </div>
  <div class="menuItem" onclick="openCalls()">
    <img src="images/IconeAppel.png" alt="Appels">
    <span>Appels</span>
  </div>
  <div class="menuItem" onclick="openAgenda()">
    <img src="images/iconeAgenda.png" alt="Agenda">
    <span>Agenda</span>
  </div>
  <div class="menuItem" onclick="openRappel()">
    <img src="images/IconeRappel.png" alt="Rappel">
    <span>Rappel</span>
  </div>
</div>

<!-- Modal Slideshow (défilement de photos après inactivité)
<div id="slideshowModal" class="d-flex">
  <img id="slideshowImage" src="" alt="Slideshow">
</div> -->

<script>
// ===========================
// 1) GESTION DE L'INACTIVITÉ
// ===========================
let inactivityTimer;
function resetInactivityTimer() {
  clearTimeout(inactivityTimer);
  // Par exemple, 60 secondes avant le lancement du diaporama
  inactivityTimer = setTimeout(startSlideshow, 60000);
}
['mousemove','mousedown','touchstart','keydown'].forEach(evt => document.addEventListener(evt, resetInactivityTimer));
resetInactivityTimer();

// ===========================
// 2) LANCER LE DIAPORAMA
// ===========================
function startSlideshow() {
  fetch('index.php?controller=photo&action=getAllForSlideshow')
    .then(res => res.json())
    .then(photos => {
      if (photos.length > 0) {
        showSlideshow(photos);
      }
    })
    .catch(err => console.error(err));
}

let slideshowIndex = 0;
let slideshowPhotos = [];
function showSlideshow(photos) {
  slideshowPhotos = photos;
  slideshowIndex = 0;
  document.getElementById('slideshowModal').style.display = 'flex';
  displaySlideshowPhoto();
}

function displaySlideshowPhoto() {
  if (slideshowIndex >= slideshowPhotos.length) {
    slideshowIndex = 0;
  }
  const photo = slideshowPhotos[slideshowIndex];
  const slideshowImage = document.getElementById('slideshowImage');
  slideshowImage.src = photo.url;
  slideshowIndex++;
  setTimeout(displaySlideshowPhoto, 5000);
}

document.getElementById('slideshowModal').addEventListener('click', () => {
  hideSlideshow();
  resetInactivityTimer();
});
function hideSlideshow() {
  document.getElementById('slideshowModal').style.display = 'none';
}

// ===========================
// 3) DÉTECTION NOUVELLE PHOTO
// ===========================
setInterval(checkNewPhoto, 15000);
function checkNewPhoto() {
  fetch('index.php?controller=photo&action=getLastPhoto')
    .then(res => res.json())
    .then(photo => {
      if (photo && photo.is_viewed == 0) {
        showNewPhotoModal(photo);
      }
    })
    .catch(err => console.error(err));
}

function showNewPhotoModal(photo) {
  const modal = document.createElement('div');
  modal.style = `
    position: fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.8);
    display: flex; align-items:center; justify-content:center;
    z-index:9999;
  `;
  modal.innerHTML = `
    <div style="background:#fff; padding:20px; text-align:center; max-width:90%; max-height:90%; overflow:auto; border-radius:10px;">
      <img src="${photo.url}" alt="Nouvelle photo" style="max-width:100%; max-height:50vh;">
      <p style="margin-top:1rem;">${photo.message}</p>
      <button id="playMessageBtn" class="btn btn-primary">🔊 Écouter le message</button>
      <button id="closeModalBtn" class="btn btn-secondary">Fermer</button>
    </div>
  `;
  document.body.appendChild(modal);
  document.getElementById('playMessageBtn').addEventListener('click', () => {
    speak(photo.message);
    markPhotoAsViewed(photo.id);
  });
  document.getElementById('closeModalBtn').addEventListener('click', () => {
    modal.remove();
  });
}

function markPhotoAsViewed(photoId) {
  fetch('index.php?controller=photo&action=markViewed', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json'},
    body: JSON.stringify({ photoId })
  })
  .then(res => res.json())
  .then(data => console.log('Photo marquée comme vue:', data))
  .catch(err => console.error(err));
}

// ===========================
// 4) SYNTHÈSE VOCALE
// ===========================
function speak(text) {
  const msg = new SpeechSynthesisUtterance(text);
  msg.lang = 'fr-FR';
  window.speechSynthesis.speak(msg);
}
function toggleVolume() {
  window.speechSynthesis.cancel();
  alert("Fonction mute/unmute à implémenter si besoin.");
}

// ===========================
// 5) BOUTONS DE NAVIGATION
// ===========================
function openPhotos() {
  window.location.href = 'index.php?controller=photo&action=gallery';
}
function openMusic() {
  window.location.href = 'index.php?controller=music&action=index';
}
function openMessages() {
  window.location.href = 'index.php?controller=message&action=received';
}
function openCalls() {
  window.location.href = 'index.php?controller=call&action=index';
}
function openAgenda() {
  window.location.href = 'index.php?controller=event&action=index';
}
function openRappel() {
  window.location.href = 'index.php?controller=rappel&action=index';
}
</script>
</body>
</html>
