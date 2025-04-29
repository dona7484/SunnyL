// public/js/browser-compatibility.js
/**
 * Utilitaire pour vérifier la compatibilité du navigateur avec les appels vidéo
 */
class BrowserCompatibilityChecker {
    /**
     * Vérifie si le navigateur est compatible avec les appels vidéo Twilio
     * @returns {Object} Résultat de la vérification avec statut et messages
     */
    static checkVideoSupport() {
        const result = {
            compatible: true,
            warnings: [],
            errors: []
        };
        
        // Vérifier si WebRTC est supporté
        if (!this.hasWebRTCSupport()) {
            result.compatible = false;
            result.errors.push("Votre navigateur ne supporte pas WebRTC, nécessaire pour les appels vidéo.");
        }
        
        // Vérifier l'accès à la caméra et au microphone
        if (!this.hasMediaDevicesSupport()) {
            result.compatible = false;
            result.errors.push("Votre navigateur ne permet pas l'accès à la caméra et au microphone.");
        }
        
        // Vérifier si le navigateur est obsolète
        const browserInfo = this.getBrowserInfo();
        if (browserInfo.outdated) {
            result.warnings.push(`Votre navigateur ${browserInfo.name} ${browserInfo.version} est obsolète. Une mise à jour pourrait améliorer la qualité des appels.`);
        }
        
        // Vérifier si nous sommes sur HTTPS (obligatoire pour getUserMedia)
        if (!this.isSecureContext()) {
            result.warnings.push("Vous n'êtes pas sur une connexion sécurisée (HTTPS). Certaines fonctionnalités peuvent être limitées.");
        }
        
        return result;
    }
    
    /**
     * Vérifie si WebRTC est supporté par le navigateur
     * @returns {boolean} True si WebRTC est supporté
     */
    static hasWebRTCSupport() {
        return !!(
            window.RTCPeerConnection &&
            window.RTCSessionDescription &&
            window.RTCIceCandidate
        );
    }
    
    /**
     * Vérifie si l'API MediaDevices est supportée
     * @returns {boolean} True si l'API est supportée
     */
    static hasMediaDevicesSupport() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }
    
    /**
     * Vérifie si nous sommes dans un contexte sécurisé (HTTPS)
     * @returns {boolean} True si le contexte est sécurisé
     */
    static isSecureContext() {
        return window.isSecureContext === true;
    }
    
    /**
     * Obtient des informations sur le navigateur actuel
     * @returns {Object} Informations sur le navigateur
     */
    static getBrowserInfo() {
        const userAgent = navigator.userAgent;
        let browserName = "Inconnu";
        let version = "Inconnue";
        let outdated = false;
        
        // Chrome
        if (/Chrome/.test(userAgent) && !/Chromium|Edge|Edg/.test(userAgent)) {
            browserName = "Chrome";
            version = userAgent.match(/Chrome\/(\d+)/)[1];
            outdated = parseInt(version) < 80;
        }
        // Firefox
        else if (/Firefox/.test(userAgent)) {
            browserName = "Firefox";
            version = userAgent.match(/Firefox\/(\d+)/)[1];
            outdated = parseInt(version) < 75;
        }
        // Safari
        else if (/Safari/.test(userAgent) && !/Chrome|Chromium|Edge|Edg/.test(userAgent)) {
            browserName = "Safari";
            version = userAgent.match(/Version\/(\d+)/)[1];
            outdated = parseInt(version) < 13;
        }
        // Edge (Chromium)
        else if (/Edg|Edge/.test(userAgent)) {
            browserName = "Edge";
            version = userAgent.match(/Edg(?:e)?\/(\d+)/)[1];
            outdated = parseInt(version) < 80;
        }
        // IE
        else if (/MSIE|Trident/.test(userAgent)) {
            browserName = "Internet Explorer";
            outdated = true;
            
            if (/MSIE (\d+\.\d+);/.test(userAgent)) {
                version = userAgent.match(/MSIE (\d+\.\d+);/)[1];
            } else if (/Trident.*rv:(\d+)/.test(userAgent)) {
                version = userAgent.match(/Trident.*rv:(\d+)/)[1];
            }
        }
        
        return {
            name: browserName,
            version: version,
            outdated: outdated
        };
    }
    
    /**
     * Teste les capacités d'appel vidéo du navigateur
     * @returns {Promise<Object>} Résultat du test
     */
    static async testVideoCall() {
        const result = {
            success: true,
            camera: false,
            microphone: false,
            messages: []
        };
        
        try {
            // Tester l'accès à la caméra
            try {
                const videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
                result.camera = true;
                videoStream.getTracks().forEach(track => track.stop());
            } catch (videoError) {
                result.success = false;
                result.messages.push(`Erreur d'accès à la caméra: ${videoError.message}`);
            }
            
            // Tester l'accès au microphone
            try {
                const audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                result.microphone = true;
                audioStream.getTracks().forEach(track => track.stop());
            } catch (audioError) {
                result.success = false;
                result.messages.push(`Erreur d'accès au microphone: ${audioError.message}`);
            }
            
            // Si nous avons accès aux périphériques mais que le navigateur est obsolète
            const browserInfo = this.getBrowserInfo();
            if (result.camera && result.microphone && browserInfo.outdated) {
                result.messages.push(`Avertissement: Votre navigateur ${browserInfo.name} ${browserInfo.version} est obsolète. La qualité de l'appel peut être réduite.`);
            }
            
            return result;
        } catch (error) {
            result.success = false;
            result.messages.push(`Erreur lors du test des périphériques: ${error.message}`);
            return result;
        }
    }
    
    /**
     * Affiche un message de compatibilité dans un élément DOM
     * @param {HTMLElement} container - Élément dans lequel afficher le message
     * @param {Object} checkResult - Résultat de la vérification de compatibilité
     */
    static displayCompatibilityMessage(container, checkResult) {
        if (!container) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'browser-compatibility-message';
        
        if (!checkResult.compatible) {
            // Navigateur incompatible
            messageDiv.className += ' browser-error';
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-triangle"></i> Problème de compatibilité</h4>
                    <p>Votre navigateur n'est pas compatible avec les appels vidéo.</p>
                    <ul>
                        ${checkResult.errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                    <p>Nous vous recommandons d'utiliser un navigateur moderne comme 
                    <a href="https://www.google.com/chrome/" target="_blank">Chrome</a>, 
                    <a href="https://www.mozilla.org/firefox/" target="_blank">Firefox</a> ou 
                    <a href="https://www.apple.com/safari/" target="_blank">Safari</a>.</p>
                </div>
            `;
        } else if (checkResult.warnings.length > 0) {
            // Compatible mais avec des avertissements
            messageDiv.className += ' browser-warning';
            messageDiv.innerHTML = `
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-circle"></i> Attention</h4>
                    <ul>
                        ${checkResult.warnings.map(warning => `<li>${warning}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        // Ajouter le message au conteneur
        container.appendChild(messageDiv);
    }
}

// Exporter l'utilitaire si nous sommes dans un environnement module
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BrowserCompatibilityChecker;
}