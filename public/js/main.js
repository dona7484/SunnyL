// main.js
document.addEventListener('DOMContentLoaded', function() {
    // Enregistrement du Service Worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sunnylink/service-worker.js', {scope: '/sunnylink/'})
        .then(registration => {
          console.log('Service Worker enregistré avec succès:', registration);
        })
        .catch(error => {
          console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
        });
    }
    
    
    // Fonction pour s'abonner aux notifications push
    function subscribeUserToPush() {
      const vapidPublicKey = 'BFnoZsHNOnO5jG0XncDui6EyziGdamtD6rXxQ37tPGmsutyV2ZtRXtwedlaEMFqLG0dBD7AzPToapQmM0srRiJI';
      const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);
      
      navigator.serviceWorker.ready.then(registration => {
        return registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: convertedVapidKey
        });
      }).then(subscription => {
        console.log('Utilisateur abonné aux notifications push');
        
        // Envoyer l'abonnement au serveur
        return fetch('index.php?controller=notification&action=subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            user_id: getUserId(),
            subscription: subscription
          })
        });
      }).then(response => response.json())
        .then(data => {
          console.log('Abonnement enregistré sur le serveur:', data);
        })
        .catch(error => {
          console.error('Erreur lors de l\'abonnement aux notifications push:', error);
        });
    }
    
    // Fonction utilitaire pour convertir la clé VAPID
    function urlBase64ToUint8Array(base64String) {
      const padding = '='.repeat((4 - base64String.length % 4) % 4);
      const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
      
      const rawData = window.atob(base64);
      const outputArray = new Uint8Array(rawData.length);
      
      for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
      }
      return outputArray;
    }
    
    // Fonction pour récupérer l'ID utilisateur
    function getUserId() {
      // À adapter selon votre logique d'authentification
      return document.body.dataset.userId || null;
    }
    
    // Fonctions pour les boutons du dashboard
    window.openPhotos = function() {
      window.location.href = 'index.php?controller=photo&action=gallery';
    };
    
    window.openMessages = function() {
      window.location.href = 'index.php?controller=message&action=received';
    };
    
    window.toggleVolume = function() {
      // Logique pour activer/désactiver le son
      console.log('Toggle volume clicked');
    };
    
    // Activation du son pour les notifications
    const enableSoundBtn = document.getElementById('enable-sound');
    if (enableSoundBtn) {
      enableSoundBtn.addEventListener('click', function() {
        const audio = new Audio('audio/notif-sound.mp3');
        audio.play().then(() => {
          console.log('Son activé');
          this.textContent = 'Son activé';
          this.classList.remove('btn-primary');
          this.classList.add('btn-success');
        }).catch(error => {
          console.error('Erreur lors de l\'activation du son:', error);
        });
      });
    }
  });
  