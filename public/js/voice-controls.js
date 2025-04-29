const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    
    recognition.onresult = (event) => {
        const command = event.results[0][0].transcript.toLowerCase();
        if(command.includes('suivant')) control('next');
        if(command.includes('précédent')) control('previous');
        if(command.includes('jouer') || command.includes('pause')) control('play_pause');
    };
    
    document.getElementById('voice-control').addEventListener('click', () => {
        recognition.start();
    });
}
