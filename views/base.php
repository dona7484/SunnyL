<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('VAPID_PUBLIC_KEY')) {
  define('VAPID_PUBLIC_KEY', 'BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SunnyLink' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="text-center">
            <h1>SunnyLink</h1>
            <?php if (isset($_SESSION['name'])): ?>
                <p>Bienvenue, <?= htmlspecialchars($_SESSION['name'], ENT_QUOTES); ?>!</p>
            <?php endif; ?>
        </header>

        <!-- Afficher la barre d'avertissement si non connecté -->
        <?php if (!isset($_SESSION['role'])): ?>
            <nav class="navbar navbar-light bg-warning">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">SunnyLink</a>
                    <span class="navbar-text">Veuillez vous connecter pour accéder à toutes les fonctionnalités.</span>
                </div>
            </nav>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
    <div class="text-center mt-3">
        <a href="index.php?controller=auth&action=logout" class="btn btn-danger">Se déconnecter</a>
    </div>
<?php endif; ?>

        <!-- Afficher la barre de navigation uniquement pour les family members -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'famille'): ?>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">SunnyLink</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNavDropdown">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="index.php">Accueil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=event&action=index">Événements</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=photo&action=form">Envoyer une photo</a>
                                <a class="nav-link" href="index.php?controller=photo&action=gallery&id=<?= $_SESSION['user_id'] ?? 1 ?>">Galerie</a>
                            </li>
                            <a href="index.php?controller=message&action=send" class="btn btn-primary">Envoyer un message</a>
                            <a href="index.php?controller=message&action=received" class="btn btn-info">Voir mes messages</a>

                            <li class="nav-item">
                                <?php if (isset($_SESSION['name'])): ?>
                                    <a class="nav-link" href="index.php?controller=auth&action=logout">Se déconnecter</a>
                                <?php else: ?>
                                    <a class="nav-link" href="index.php?controller=auth&action=login">Se connecter</a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        <?php endif; ?>

        <!-- Contenu principal -->
        <main>
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="text-center">
            <p>&copy; 2025 - SunnyLink</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
// Fonction pour convertir la clé publique VAPID
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');
  
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}


if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/SunnyLink/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker enregistré avec succès:', registration);
            })
            .catch(function(error) {
                console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
            });
    });
}

// activation des notifications
// activation des notifications
document.addEventListener('DOMContentLoaded', function() {
  const enableNotificationsBtn = document.getElementById('enable-notifications');
  if (enableNotificationsBtn) {
    enableNotificationsBtn.addEventListener('click', function() {
      if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.ready
          .then(function(registration) {
            // Vérifier si une souscription existe déjà
            return registration.pushManager.getSubscription()
              .then(function(subscription) {
                // Si une souscription existe, la désabonner d'abord
                if (subscription) {
                  return subscription.unsubscribe().then(function() {
                    return registration; // Retourner l'enregistrement pour continuer
                  });
                }
                return registration;
              });
          })
          .then(function(registration) {
            // Créer une nouvelle souscription avec la nouvelle clé
            return registration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array('BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI')
            });
          })
          .then(function(subscription) {
            console.log("Abonnement réussi:", subscription);
            
            // Envoyer la souscription au serveur
            return fetch('index.php?controller=notification&action=subscribe', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                subscription: subscription.toJSON(),
                user_id: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>
              }),
            });
          })
          .then(function(response) {
            return response.json();
          })
          .then(function(data) {
            if (data.success) {
              alert('Notifications activées avec succès !');
            } else {
              alert('Erreur lors de l\'activation des notifications: ' + (data.error || ''));
            }
          })
          .catch(function(err) {
            console.error('Erreur d\'abonnement:', err);
            alert('Erreur lors de l\'abonnement aux notifications: ' + err.message);
          });
      } else {
        alert('Votre navigateur ne supporte pas les notifications push.');
      }
    });
  }
});

</script>

<!-- bouton d'activation des notifications -->
<div class="text-center mt-4 mb-4">
  <button id="enable-notifications" class="btn btn-primary">Activer les notifications</button>
</div>