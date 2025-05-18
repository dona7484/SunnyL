/**
 * SunnyLink - Système de notifications
 * Ce fichier contient toutes les fonctionnalités liées aux notifications.
 */

// Variables globales
let notificationCheckTimer = null;
const NOTIFICATION_CHECK_INTERVAL = 30000; // 30 secondes

/**
 * Fonction d'initialisation du système de notification
 * Cette fonction doit être appelée au chargement de la page
 */
function initNotifications() {
    console.log('Initialisation du système de notifications...');
    
    // Vérifier les notifications immédiatement
    checkForNewNotifications();
    
    // Configurer la vérification périodique des notifications
    notificationCheckTimer = setInterval(checkForNewNotifications, NOTIFICATION_CHECK_INTERVAL);
    
    // Configurer le bouton de lecture des notifications si présent
    setupMarkAsReadButton();
    
    console.log('Système de notifications initialisé avec succès');
}

/**
 * Configure le bouton "Marquer comme lu" pour les notifications
 */
function setupMarkAsReadButton() {
    const markAsReadButton = document.getElementById('mark-as-read-button');
    if (markAsReadButton) {
        markAsReadButton.addEventListener('click', function() {
            const notifId = this.getAttribute('data-notif-id');
            const notifType = this.getAttribute('data-type');
            const relatedId = this.getAttribute('data-related-id');
            
            if (notifId) {
                markNotificationAsRead(notifId, notifType, relatedId);
            }
        });
    }
}

/**
 * Vérifie s'il y a de nouvelles notifications
 */
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

/**
 * Met à jour l'interface utilisateur avec les notifications
 */
function updateNotificationUI(notifications) {
    console.log('Mise à jour de l\'UI avec les notifications:', notifications);
    const bubble = document.getElementById("notif-bubble");
    const bubbleText = document.getElementById("notif-bubble-text");
    const markAsReadBtn = document.getElementById("mark-as-read-button");
    
    if (!bubble || !bubbleText) {
        console.error("Éléments d'UI de notification non trouvés");
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
        
        // Lire le message à voix haute
        speakMessage(notif.content);
    } else {
        console.log('Aucune notification à afficher');
        // Cacher la bulle s'il n'y a pas de notifications
        bubble.style.display = "none";
    }
}

/**
 * Marque une notification comme lue
 */
function markNotificationAsRead(notifId, type, relatedId) {
    if (!notifId) return;
    
    console.log('Marquage de la notification comme lue:', {notifId, type, relatedId});
    
    fetch("index.php?controller=notification&action=markNotificationAsRead", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ 
            notif_id: notifId,
            type: type,
            related_id: relatedId
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse du serveur:', data);
        if (data.success) {
            // Cacher la bulle de notification
            const bubble = document.getElementById("notif-bubble");
            if (bubble) {
                bubble.classList.add('notification-hide');
                setTimeout(() => {
                    bubble.style.display = "none";
                    bubble.classList.remove('notification-hide');
                }, 500);
            }
            
            // Rediriger en fonction du type de notification
            redirectBasedOnType(type, relatedId);
        }
    })
    .catch(error => {
        console.error("Erreur lors du marquage de la notification comme lue:", error);
    });
}

/**
 * Redirige l'utilisateur en fonction du type de notification
 */
function redirectBasedOnType(type, relatedId) {
    console.log('Redirection basée sur le type:', {type, relatedId});
    switch(type) {
        case 'message':
        case 'audio':
            window.location.href = "index.php?controller=message&action=received";
            break;
        case 'photo':
            window.location.href = "index.php?controller=photo&action=gallery";
            break;
        case 'event':
            if (relatedId) {
                window.location.href = "index.php?controller=event&action=show&id=" + relatedId;
            } else {
                window.location.href = "index.php?controller=event&action=index";
            }
            break;
        default:
            // Recharger la page pour mettre à jour la liste des notifications
            window.location.reload();
    }
}

/**
 * Joue le son de notification
 */
function playNotificationSound() {
    const audio = document.getElementById("notification-sound");
    if (audio) {
        audio.volume = 0.5; // Volume à 50%
        audio.play().catch(e => {
            console.warn("Impossible de jouer le son de notification:", e);
        });
    }
}

/**
 * Lit le message à voix haute
 */
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

/**
 * Affiche une notification avec les éléments donnés
 */
function showNotification(message, notifId, type, relatedId) {
    console.log('Affichage de la notification:', { message, notifId, type, relatedId });
    
    // Récupérer les éléments
    const bubble = document.getElementById('notif-bubble');
    const bubbleText = document.getElementById('notif-bubble-text');
    const typeLabel = document.querySelector('.notif-type-label');
    const iconElement = document.querySelector('.notif-bubble-icon');
    const button = document.getElementById('mark-as-read-button');
    
    if (!bubble || !bubbleText) {
        console.error("Éléments de notification non trouvés dans le DOM");
        return;
    }
    
    // Mettre à jour le contenu de la notification
    bubbleText.textContent = message;
    
    // Mettre à jour le type de notification
    if (typeLabel) {
        switch (type) {
            case 'message':
                typeLabel.textContent = 'Nouveau message';
                break;
            case 'audio':
                typeLabel.textContent = 'Nouveau message audio';
                break;
            case 'photo':
                typeLabel.textContent = 'Nouvelle photo';
                break;
            case 'event':
                typeLabel.textContent = 'Nouvel événement';
                break;
            default:
                typeLabel.textContent = 'Nouvelle notification';
        }
    }
    
    // Mettre à jour l'icône en fonction du type
    if (iconElement) {
        switch (type) {
            case 'message':
                iconElement.src = 'images/iconeMessage.png';
                break;
            case 'audio':
                iconElement.src = 'images/iconeMusic.png';
                break;
            case 'photo':
                iconElement.src = 'images/IconePhoto.png';
                break;
            case 'event':
                iconElement.src = 'images/iconeAgenda.png';
                break;
            default:
                iconElement.src = 'images/IconeRappel.png';
        }
    }
    
    // Mettre à jour les attributs du bouton
    if (button) {
        button.setAttribute('data-notif-id', notifId);
        button.setAttribute('data-type', type || '');
        button.setAttribute('data-related-id', relatedId || '');
    }
    
    // Afficher la bulle
    bubble.style.display = 'flex';
    
    // Jouer le son de notification
    playNotificationSound();
    
    // Lire le message à voix haute
    speakMessage(message);
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Exporter les fonctions globalement pour qu'elles soient accessibles partout
    window.initNotifications = initNotifications;
    window.checkForNewNotifications = checkForNewNotifications;
    window.markNotificationAsRead = markNotificationAsRead;
    window.speakMessage = speakMessage;
    window.playNotificationSound = playNotificationSound;
    window.showNotification = showNotification;
    
    // Appeler initNotifications() uniquement si elle n'a pas déjà été appelée
    if (typeof window.notificationsInitialized === 'undefined') {
        window.notificationsInitialized = true;
        initNotifications();
    }
});