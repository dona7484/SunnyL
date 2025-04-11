// Fonction pour lire un message à voix haute
function speakMessage(text) {
    if (window.speechSynthesis) {
      // Annuler toute synthèse vocale en cours
      window.speechSynthesis.cancel();
      
      // Créer un nouveau message vocal
      const msg = new SpeechSynthesisUtterance(text);
      msg.lang = 'fr-FR';
      msg.rate = 0.9; // Légèrement plus lent pour une meilleure compréhension
      msg.volume = 1.0; // Volume maximum
      
      // Lancer la synthèse vocale
      window.speechSynthesis.speak(msg);
    }
  }
  
  // Fonction pour afficher une notification
  function showNotif(message, notifId, type, relatedId) {
    console.log("Affichage notification:", {message, notifId, type, relatedId});
    
    // Récupérer les éléments DOM
    const bubble = document.getElementById("notif-bubble");
    const text = document.getElementById("notif-bubble-text");
    const button = document.getElementById("mark-as-read-button");
    const icon = document.querySelector(".notif-bubble-icon");
    
    // Vérifier si les éléments existent
    if (!bubble || !text) {
      console.error("Éléments de notification non trouvés dans le DOM");
      return;
    }
    
    // Définir les couleurs et styles selon le type de notification
    let borderColor, buttonColor, iconBgColor;
    
    switch(type) {
      case 'message':
        borderColor = '#4285F4'; // Bleu Google
        buttonColor = '#4285F4';
        iconBgColor = 'rgba(66, 133, 244, 0.1)';
        break;
      case 'photo':
        borderColor = '#EA4335'; // Rouge Google
        buttonColor = '#EA4335';
        iconBgColor = 'rgba(234, 67, 53, 0.1)';
        break;
      case 'event':
        borderColor = '#FBBC05'; // Jaune Google
        buttonColor = '#FBBC05';
        iconBgColor = 'rgba(251, 188, 5, 0.1)';
        break;
      default:
        borderColor = '#34A853'; // Vert Google
        buttonColor = '#34A853';
        iconBgColor = 'rgba(52, 168, 83, 0.1)';
    }
    
    // Appliquer les styles
    bubble.style.borderLeft = `5px solid ${borderColor}`;
    if (button) button.style.backgroundColor = buttonColor;
    if (icon) icon.style.backgroundColor = iconBgColor;
    
    // Mettre à jour le texte
    text.textContent = message;
    
    // Changer l'icône selon le type de notification
    if (icon) {
      if (type === 'message') {
        icon.src = 'images/iconeMessage.png';
      } else if (type === 'photo') {
        icon.src = 'images/IconePhoto.png';
      } else if (type === 'event') {
        icon.src = 'images/iconeAgenda.png';
      } else {
        icon.src = 'images/IconeRappel.png';
      }
    }
    
    // Mettre à jour les attributs du bouton si présent
    if (button) {
      button.dataset.notifId = notifId;
      button.dataset.type = type;
      button.dataset.relatedId = relatedId;
    }
    
    // Ajouter une classe pour l'animation d'entrée
    bubble.classList.add('notification-show');
    
    // Afficher la bulle
    bubble.style.display = "flex";
    
    // Jouer un son de notification
    try {
      const audio = document.getElementById('notification-sound') || new Audio('audio/notif-sound.mp3');
      audio.currentTime = 0;
      audio.play().catch(e => {
        console.warn("🔇 Son bloqué :", e);
      });
      
      // Lecture vocale du message
      speakMessage(message);
    } catch (error) {
      console.warn("Erreur lors de la lecture audio:", error);
    }
  }
  
  
  // Fonction pour marquer une notification comme lue
  function markNotificationAsRead(notifId, type, relatedId) {
    fetch('index.php?controller=notification&action=markNotificationAsRead', {
        method: 'POST',
        body: JSON.stringify({ notif_id: notifId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let redirectUrl = 'index.php?controller=home&action=dashboard';
            
            // Gestion explicite des messages audio
            if (type === 'audio' || data.type === 'audio') {
                redirectUrl = 'index.php?controller=message&action=received';
            }
            else if (type === 'message') {
                redirectUrl = 'index.php?controller=message&action=received';
            }
            
            window.location.href = redirectUrl;
        }
    });
}
  // Fonction pour vérifier les nouvelles notifications
  function checkForNewNotifications() {
    fetch('index.php?controller=notification&action=getUserNotifications')
      .then(res => res.json())
      .then(data => {
        console.log("Données de notifications reçues:", data);
        
        const bubble = document.getElementById("notif-bubble");
        const text = document.getElementById("notif-bubble-text");
        const button = document.getElementById("mark-as-read-button");
        const icon = document.querySelector(".notif-bubble-icon");
        
        if (data && data.length > 0) {
          const currentNotifId = button?.dataset?.notifId;
          
          // Ne montrer la notification que si elle est nouvelle ou différente
          if (!currentNotifId || currentNotifId != data[0].id) {
            showNotif(
              data[0].content,
              data[0].id,
              data[0].type,
              data[0].related_id
            );
          }
        } else {
          // S'il n'y a pas de notifications, masquer la bulle
          if (bubble) bubble.style.display = "none";
        }
      })
      .catch(err => console.error('Erreur:', err));
  }
  // Bouton pour activer les sons
  document.getElementById('enable-sound').addEventListener('click', function() {
    const audio = document.getElementById('notification-sound');
    audio.volume = 0.1; // Volume bas pour ne pas surprendre
    
    audio.play().then(() => {
      // Mémoriser que les notifications sont activées
      localStorage.setItem('notificationsEnabled', 'true');
      
      alert('Sons de notification activés !');
      // Activer ici la vérification périodique des notifications
      initNotifications();
    }).catch(e => {
      console.warn("Activation du son échouée :", e);
      alert('Erreur lors de l\'activation du son. Veuillez réessayer.');
    });
  });
  
  // Fonction d'initialisation des notifications
  function initNotifications() {
    // Vérifier les notifications immédiatement
    checkForNewNotifications();
    
    // Puis vérifier périodiquement
    setInterval(checkForNewNotifications, 5000);
    
    // Ajouter un gestionnaire d'événement pour le bouton de validation
    const markAsReadBtn = document.getElementById('mark-as-read-button');
    if (markAsReadBtn) {
      markAsReadBtn.addEventListener('click', function() {
        const notifId = this.dataset.notifId;
        const type = this.dataset.type;
        const relatedId = this.dataset.relatedId;
        
        if (notifId) {
          markNotificationAsRead(notifId, type, relatedId);
        }
      });
    }
  }
  
  // Initialiser quand le DOM est chargé
  document.addEventListener('DOMContentLoaded', initNotifications);
  