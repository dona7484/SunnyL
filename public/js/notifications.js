
// Gestion du bouton de validation des notifications
document.addEventListener('DOMContentLoaded', function() {
    const markAsReadButton = document.getElementById('mark-as-read-button');
    if (markAsReadButton) {
        markAsReadButton.addEventListener('click', function() {
            const notifId = this.getAttribute('data-notif-id');
            const notifType = this.getAttribute('data-type');
            const relatedId = this.getAttribute('data-related-id');
            
            if (notifId) {
                fetch('index.php?controller=notification&action=markNotificationAsRead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        notif_id: notifId,
                        type: notifType,
                        related_id: relatedId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cacher la bulle de notification
                        const notifBubble = document.getElementById('notif-bubble');
                        if (notifBubble) {
                            notifBubble.classList.add('notification-hide');
                            setTimeout(() => {
                                notifBubble.style.display = 'none';
                                notifBubble.classList.remove('notification-hide');
                            }, 500);
                        }
                        
                        // Rediriger vers la page appropriée selon le type de notification
                        if (notifType === 'photo') {
                            window.location.href = 'index.php?controller=photo&action=gallery';
                        } else if (notifType === 'message' || notifType === 'audio') {
                            window.location.href = 'index.php?controller=message&action=received';
                        } else if (notifType === 'event') {
                            window.location.href = 'index.php?controller=event&action=index';
                        }
                    } else {
                        console.error('Erreur lors du marquage de la notification comme lue:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la requête:', error);
                });
            }
        });
    }
});


// Fonction pour vérifier les nouvelles notifications

function checkForNewNotifications() {
    console.log('Vérification des nouvelles notifications...');
    fetch("index.php?controller=notification&action=getUserNotifications")
    .then(response => {
        // Vérifier si la réponse est JSON
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            // Si ce n'est pas du JSON, lire comme texte pour le débogage
            return response.text().then(text => {
                console.error("Réponse non-JSON reçue:", text);
                throw new Error("La réponse du serveur n'est pas au format JSON");
            });
        }
    })
    .then(data => {
        console.log('Données de notifications reçues:', data);
        
        // Vérifier si la réponse contient une erreur
        if (data.error) {
            console.error("Erreur du serveur:", data.error);
            return;
        }
        
        // Traiter les notifications
        updateNotificationUI(data);
    })
    .catch(error => {
        console.error("Erreur lors de la vérification des notifications:", error);
    });
}

// Fonction pour mettre à jour l'interface utilisateur avec les notifications
function updateNotificationUI(notifications) {
    console.log('Mise à jour de l\'UI avec les notifications:', notifications);
    const bubble = document.getElementById("notif-bubble");
    const bubbleText = document.getElementById("notif-bubble-text");
    const markAsReadBtn = document.getElementById("mark-as-read-button");
    
    if (!bubble || !bubbleText) {
        console.error("Éléments d'UI de notification non trouvés:", {
            bubble: !!bubble,
            bubbleText: !!bubbleText
        });
        return;
    }
    
    if (notifications && notifications.length > 0) {
        console.log('Affichage de la notification:', notifications[0]);
        // Afficher la première notification non lue
        const notif = notifications[0];
        bubbleText.textContent = notif.content;
        
        // Mettre à jour le bouton avec l'ID de la notification
        if (markAsReadBtn) {
            markAsReadBtn.dataset.notifId = notif.id;
            markAsReadBtn.dataset.type = notif.type;
            markAsReadBtn.dataset.relatedId = notif.related_id || '';
        }
        
        // Mettre à jour l'icône en fonction du type
        const bubbleIcon = bubble.querySelector('.notif-bubble-icon');
        if (bubbleIcon) {
            if (notif.type === 'message') {
                bubbleIcon.src = 'images/iconeMessage.png';
            } else if (notif.type === 'audio') {
                bubbleIcon.src = 'images/iconeMusic.png';
            } else if (notif.type === 'photo') {
                bubbleIcon.src = 'images/IconePhoto.png';
            } else {
                bubbleIcon.src = 'images/IconeRappel.png';
            }
        }
        
        // Mettre à jour le type de notification
        const typeLabel = bubble.querySelector('.notif-type-label');
        if (typeLabel) {
            if (notif.type === 'message') {
                typeLabel.textContent = 'Nouveau message';
            } else if (notif.type === 'audio') {
                typeLabel.textContent = 'Nouveau message audio';
            } else if (notif.type === 'photo') {
                typeLabel.textContent = 'Nouvelle photo';
            } else {
                typeLabel.textContent = 'Nouvelle notification';
            }
        }
        
        // Afficher la bulle
        bubble.style.display = "flex";
        
        // Jouer le son de notification
        playNotificationSound();
        
        // Lire le message à voix haute si la fonction est disponible
        if (typeof speakMessage === 'function') {
            speakMessage(notif.content);
        }
    } else {
        console.log('Aucune notification à afficher');
        // Cacher la bulle s'il n'y a pas de notifications
        bubble.style.display = "none";
    }
}

// Fonction pour marquer une notification comme lue
function markNotificationAsRead(notifId, type, relatedId) {
    if (!notifId) return;
    
    console.log('Marquage de la notification comme lue:', {notifId, type, relatedId});
    
    fetch("index.php?controller=notification&action=markNotificationAsRead", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ notif_id: notifId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse du serveur:', data);
        if (data.success) {
            // Cacher la bulle de notification
            const bubble = document.getElementById("notif-bubble");
            if (bubble) bubble.style.display = "none";
            
            // Rediriger en fonction du type de notification
            redirectBasedOnType(type, relatedId);
        }
    })
    .catch(error => {
        console.error("Erreur lors du marquage de la notification comme lue:", error);
    });
}

// Fonction pour rediriger en fonction du type de notification
function redirectBasedOnType(type, relatedId) {
    console.log('Redirection basée sur le type:', {type, relatedId});
    switch(type) {
        case 'message':
            window.location.href = "index.php?controller=message&action=received";
            break;
        case 'audio':
            window.location.href = "index.php?controller=message&action=received";
            break;
        case 'photo':
            window.location.href = "index.php?controller=photo&action=gallery";
            break;
        case 'event':
            window.location.href = "index.php?controller=event&action=index";
            break;
        default:
            // Ne pas rediriger pour les autres types
            break;
    }
}

// Fonction pour jouer le son de notification
function playNotificationSound() {
    const audio = document.getElementById("notification-sound");
    if (audio) {
        audio.volume = 0.5; // Volume à 50%
        audio.play().catch(e => {
            console.warn("Impossible de jouer le son de notification:", e);
        });
    }
}

// Fonction pour lire le message à voix haute
function speakMessage(message) {
    if ('speechSynthesis' in window) {
        // Annuler toute synthèse vocale en cours
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'fr-FR';
        utterance.rate = 0.9; // Un peu plus lent pour la clarté
        utterance.volume = 1;
        
        window.speechSynthesis.speak(utterance);
        console.log("Lecture vocale du message: " + message);
    } else {
        console.warn("La synthèse vocale n'est pas prise en charge par ce navigateur");
    }
}

// Fonction pour initialiser les notifications
function initNotifications() {
    console.log('Initialisation des notifications...');
    // Vérifier les notifications au chargement
    checkForNewNotifications();
    
    // Configurer la vérification périodique des notifications
    setInterval(checkForNewNotifications, 30000); // Vérifier toutes les 30 secondes
}

// Exporter les fonctions pour les rendre disponibles globalement
window.checkForNewNotifications = checkForNewNotifications;
window.markNotificationAsRead = markNotificationAsRead;
window.speakMessage = speakMessage;
window.initNotifications = initNotifications;
window.playNotificationSound = playNotificationSound;
