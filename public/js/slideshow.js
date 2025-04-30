// slideshow.js - Script pour le diaporama automatique sur le dashboard senior
// Ce script détecte l'inactivité et lance un diaporama des photos envoyées au senior

class SlideshowManager {
    constructor(options = {}) {
        // Options par défaut
        this.options = {
            inactivityTime: 60000, // Temps d'inactivité avant le lancement du diaporama (1 minute par défaut)
            slideDuration: 5000, // Durée d'affichage de chaque image (5 secondes par défaut)
            containerId: 'slideshow-container', // ID du conteneur pour le diaporama
            fetchUrl: 'index.php?controller=photo&action=getAllForSlideshow', // URL pour récupérer les photos
            ...options // Fusion avec les options fournies
        };

        // État interne
        this.inactivityTimer = null;
        this.slideshowTimer = null;
        this.isActive = false;
        this.photos = [];
        this.currentPhotoIndex = 0;
        
        // Créer le conteneur de diaporama s'il n'existe pas
        this.createSlideshowContainer();
        
        // Lier les méthodes au contexte actuel
        this.resetInactivityTimer = this.resetInactivityTimer.bind(this);
        this.startSlideshow = this.startSlideshow.bind(this);
        this.stopSlideshow = this.stopSlideshow.bind(this);
        this.showNextPhoto = this.showNextPhoto.bind(this);
        this.loadPhotos = this.loadPhotos.bind(this);
    }

    // Initialiser le système de diaporama
    init() {
        console.log('Initialisation du système de diaporama...');
        
        // Événements pour détecter l'activité de l'utilisateur
        document.addEventListener('mousemove', this.resetInactivityTimer);
        document.addEventListener('mousedown', this.resetInactivityTimer);
        document.addEventListener('keypress', this.resetInactivityTimer);
        document.addEventListener('touchstart', this.resetInactivityTimer);
        document.addEventListener('scroll', this.resetInactivityTimer);
        
        // Événement pour stopper le diaporama lors d'une interaction
        document.getElementById(this.options.containerId).addEventListener('click', this.stopSlideshow);
        
        // Démarrer le timer d'inactivité
        this.resetInactivityTimer();
        
        // Charger les photos initialement
        this.loadPhotos();
        
        console.log('Système de diaporama initialisé');
    }

    // Créer le conteneur pour le diaporama
    createSlideshowContainer() {
        if (!document.getElementById(this.options.containerId)) {
            console.log('Création du conteneur de diaporama');
            const container = document.createElement('div');
            container.id = this.options.containerId;
            container.className = 'slideshow-container';
            container.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.9);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.5s ease;
            `;
            
            // Ajouter un élément pour afficher l'image
            const imgElement = document.createElement('img');
            imgElement.id = 'slideshow-image';
            imgElement.style.cssText = `
                max-width: 90%;
                max-height: 80%;
                object-fit: contain;
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
                border-radius: 8px;
                transition: opacity 0.3s ease;
            `;
            container.appendChild(imgElement);
            
            // Ajouter un bouton de fermeture
            const closeButton = document.createElement('button');
            closeButton.id = 'slideshow-close';
            closeButton.innerHTML = '&times;';
            closeButton.style.cssText = `
                position: absolute;
                top: 20px;
                right: 30px;
                font-size: 40px;
                color: white;
                background: none;
                border: none;
                cursor: pointer;
            `;
            closeButton.addEventListener('click', this.stopSlideshow);
            container.appendChild(closeButton);
            
            // Ajouter un élément pour le message/titre
            const captionElement = document.createElement('div');
            captionElement.id = 'slideshow-caption';
            captionElement.style.cssText = `
                position: absolute;
                bottom: 50px;
                left: 0;
                width: 100%;
                text-align: center;
                color: white;
                font-size: 24px;
                padding: 10px;
                background-color: rgba(0, 0, 0, 0.5);
            `;
            container.appendChild(captionElement);
            
            // Ajouter au body
            document.body.appendChild(container);
        }
    }

    // Réinitialiser le timer d'inactivité
    resetInactivityTimer() {
        // Si le diaporama est déjà actif, ne rien faire
        if (this.isActive) return;
        
        // Effacer le timer existant
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
        }
        
        // Définir un nouveau timer
        this.inactivityTimer = setTimeout(() => {
            console.log(`Inactivité détectée après ${this.options.inactivityTime / 1000} secondes`);
            this.startSlideshow();
        }, this.options.inactivityTime);
    }

    // Charger les photos depuis l'API
    loadPhotos() {
        console.log('Chargement des photos pour le diaporama...');
        
        fetch(this.options.fetchUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    this.photos = data;
                    console.log(`${this.photos.length} photos chargées pour le diaporama`);
                    
                    // Si aucune photo n'est chargée, on réessaye après un délai
                    if (this.photos.length === 0) {
                        setTimeout(this.loadPhotos, 60000); // Réessayer dans 1 minute
                    }
                } else {
                    console.log('Aucune photo disponible pour le diaporama');
                    setTimeout(this.loadPhotos, 60000); // Réessayer dans 1 minute
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des photos:', error);
                setTimeout(this.loadPhotos, 60000); // Réessayer dans 1 minute en cas d'erreur
            });
    }

    // Démarrer le diaporama
    startSlideshow() {
        console.log('Démarrage du diaporama...');
        
        // Si pas de photos, essayer de les charger à nouveau
        if (this.photos.length === 0) {
            this.loadPhotos();
            console.log('Aucune photo disponible, le diaporama ne démarre pas');
            return;
        }
        
        this.isActive = true;
        
        // Afficher le conteneur du diaporama
        const container = document.getElementById(this.options.containerId);
        container.style.display = 'flex';
        
        // Animation d'entrée
        setTimeout(() => {
            container.style.opacity = '1';
        }, 10);
        
        // Réinitialiser l'index et afficher la première photo
        this.currentPhotoIndex = 0;
        this.showNextPhoto();
        
        // Démarrer le timer pour faire défiler les photos
        this.slideshowTimer = setInterval(this.showNextPhoto, this.options.slideDuration);
        
        console.log('Diaporama démarré');
    }

    // Arrêter le diaporama
    stopSlideshow() {
        console.log('Arrêt du diaporama...');
        
        if (this.slideshowTimer) {
            clearInterval(this.slideshowTimer);
            this.slideshowTimer = null;
        }
        
        this.isActive = false;
        
        // Animation de sortie
        const container = document.getElementById(this.options.containerId);
        container.style.opacity = '0';
        
        // Cacher le conteneur après l'animation
        setTimeout(() => {
            container.style.display = 'none';
        }, 500);
        
        // Réinitialiser le timer d'inactivité
        this.resetInactivityTimer();
        
        console.log('Diaporama arrêté');
    }

    // Afficher la photo suivante
    showNextPhoto() {
        if (this.photos.length === 0) {
            this.stopSlideshow();
            return;
        }
        
        const photo = this.photos[this.currentPhotoIndex];
        const imgElement = document.getElementById('slideshow-image');
        const captionElement = document.getElementById('slideshow-caption');
        
        // Animation de transition
        imgElement.style.opacity = '0';
        
        // Changer l'image après un court délai
        setTimeout(() => {
            // Mettre à jour l'image
            imgElement.src = photo.url;
            
            // Mettre à jour la légende
            captionElement.textContent = photo.message || '';
            
            // Rendre l'image visible
            imgElement.style.opacity = '1';
        }, 300);
        
        // Passer à l'image suivante
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.photos.length;
    }
}

// Fonction d'initialisation à appeler quand le DOM est chargé
function initSlideshow() {
    // Créer et initialiser le gestionnaire de diaporama
    const slideshow = new SlideshowManager({
        inactivityTime: 60000, // 1 minute d'inactivité avant démarrage
        slideDuration: 7000, // 7 secondes par photo
        fetchUrl: 'index.php?controller=photo&action=getAllForSlideshow' // URL pour récupérer les photos
    });
    
    slideshow.init();
    
    // Rendre accessible globalement pour le débogage
    window.slideshowManager = slideshow;
}

// Initialiser le diaporama quand le DOM est chargé
document.addEventListener('DOMContentLoaded', initSlideshow);