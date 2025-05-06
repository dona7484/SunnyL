// Classe pour gérer l'enregistrement audio
class AudioRecorder {
    constructor(options = {}) {
        // Options par défaut
        this.options = {
            onStart: () => {},
            onStop: () => {},
            onDataAvailable: () => {},
            onError: () => {},
            mimeType: 'audio/webm',
            ...options
        };
        
        // État de l'enregistreur
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.stream = null;
        this.isRecording = false;
        
        // Lier les méthodes au contexte actuel
        this.start = this.start.bind(this);
        this.stop = this.stop.bind(this);
        this.handleDataAvailable = this.handleDataAvailable.bind(this);
    }
    
    // Démarrer l'enregistrement
    async start() {
        console.log("Tentative de démarrage de l'enregistrement audio...");
        
        try {
            // Demander l'accès au microphone
            this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            
            // Créer l'enregistreur
            this.mediaRecorder = new MediaRecorder(this.stream, { 
                mimeType: this.options.mimeType
            });
            
            // Configurer les gestionnaires d'événements
            this.mediaRecorder.addEventListener('dataavailable', this.handleDataAvailable);
            
            this.mediaRecorder.addEventListener('start', () => {
                console.log("Enregistrement audio démarré");
                this.isRecording = true;
                this.audioChunks = [];
                this.options.onStart();
            });
            
            this.mediaRecorder.addEventListener('stop', () => {
                console.log("Enregistrement audio terminé");
                this.isRecording = false;
                
                // Créer le blob audio à partir des chunks
                const audioBlob = new Blob(this.audioChunks, { type: this.options.mimeType });
                
                // Arrêter tous les tracks
                this.stream.getTracks().forEach(track => track.stop());
                
                // Appeler le callback avec le blob
                this.options.onStop(audioBlob);
            });
            
            // Démarrer l'enregistrement
            this.mediaRecorder.start();
            console.log("MediaRecorder démarré avec succès");
            
            return true;
        } catch (error) {
            console.error("Erreur lors du démarrage de l'enregistrement:", error);
            this.options.onError(error);
            return false;
        }
    }
    
    // Arrêter l'enregistrement
    stop() {
        if (this.mediaRecorder && this.isRecording) {
            console.log("Arrêt de l'enregistrement audio...");
            this.mediaRecorder.stop();
            return true;
        } else {
            console.warn("Impossible d'arrêter l'enregistrement: aucun enregistrement en cours");
            return false;
        }
    }
    
    // Gérer les données disponibles
    handleDataAvailable(event) {
        console.log("Données audio disponibles, taille:", event.data.size);
        if (event.data.size > 0) {
            this.audioChunks.push(event.data);
            this.options.onDataAvailable(event.data);
        }
    }
    
    // Vérifier si le navigateur prend en charge l'enregistrement audio
    static isSupported() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }
}