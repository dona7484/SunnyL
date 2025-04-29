<?php
$title = "Musique - SunnyLink";
?>

<div class="spotify-player-container">
    <h1 class="senior-title">Ma Musique</h1>
    
    <div class="senior-music-controls">
        <!-- Interface simplifiée pour les seniors -->
        <div class="music-categories">
            <div class="category-button" id="playFavorites">
                <i class="fas fa-heart"></i>
                <span>Mes Favoris</span>
            </div>
            <div class="category-button" id="playClassical">
                <i class="fas fa-music"></i>
                <span>Musique Classique</span>
            </div>
            <div class="category-button" id="playJazz">
                <i class="fas fa-saxophone"></i>
                <span>Jazz</span>
            </div>
            <div class="category-button" id="playFrenchSongs">
                <i class="fas fa-flag"></i>
                <span>Chansons Françaises</span>
            </div>
        </div>
        
        <div class="now-playing-container">
            <h2>En cours de lecture</h2>
            <div id="currentTrackInfo">
                <img id="albumCover" src="/SunnyLink/public/img/default-album.png" alt="Pochette d'album">
                <div class="track-details">
                    <p id="trackName">Aucune musique en cours</p>
                    <p id="artistName"></p>
                </div>
            </div>
            
            <div class="player-controls">
                <button id="previousButton" class="control-button">
                    <i class="fas fa-step-backward fa-2x"></i>
                </button>
                <button id="playPauseButton" class="control-button">
                    <i class="fas fa-play fa-3x"></i>
                </button>
                <button id="nextButton" class="control-button">
                    <i class="fas fa-step-forward fa-2x"></i>
                </button>
            </div>
            
            <div class="volume-control">
                <i class="fas fa-volume-down"></i>
                <input type="range" id="volumeSlider" min="0" max="100" value="50">
                <i class="fas fa-volume-up"></i>
            </div>
        </div>
    </div>
    
    <!-- Lecteur Spotify caché -->
    <div id="spotify-player" style="display: none;"></div>
</div>

<style>
    .spotify-player-container {
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    
    .senior-title {
        font-size: 36px;
        margin-bottom: 30px;
        color: #1DB954; /* Couleur Spotify */
        text-align: center;
    }
    
    .senior-music-controls {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .music-categories {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .category-button {
        width: 200px;
        height: 200px;
        background-color: #f8f9fa;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .category-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .category-button i {
        font-size: 64px;
        margin-bottom: 15px;
        color: #1DB954;
    }
    
    .category-button span {
        font-size: 24px;
        font-weight: bold;
    }
    
    .now-playing-container {
        width: 100%;
        max-width: 600px;
        background-color: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .now-playing-container h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 28px;
    }
    
    #currentTrackInfo {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }
    
    #albumCover {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        margin-right: 20px;
        object-fit: cover;
    }
    
    .track-details {
        flex-grow: 1;
    }
    
    .track-details p {
        margin: 5px 0;
    }
    
    #trackName {
        font-size: 24px;
        font-weight: bold;
    }
    
    #artistName {
        font-size: 20px;
        color: #666;
    }
    
    .player-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .control-button {
        background: none;
        border: none;
        cursor: pointer;
        color: #333;
        transition: color 0.3s;
    }
    
    .control-button:hover {
        color: #1DB954;
    }
    
    #playPauseButton {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #1DB954;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .volume-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    #volumeSlider {
        flex-grow: 1;
        height: 10px;
    }
</style>

<script src="https://sdk.scdn.co/spotify-player.js"></script>
<script>
    window.onSpotifyWebPlaybackSDKReady = () => {
        const token = '<?= $token ?>';
        const player = new Spotify.Player({
            name: 'SunnyLink Player',
            getOAuthToken: cb => { cb(token); },
            volume: 0.5
        });
        
        // Erreurs
        player.addListener('initialization_error', ({ message }) => { console.error(message); });
        player.addListener('authentication_error', ({ message }) => { console.error(message); });
        player.addListener('account_error', ({ message }) => { console.error(message); });
        player.addListener('playback_error', ({ message }) => { console.error(message); });
        
        // Prêt
        player.addListener('ready', ({ device_id }) => {
            console.log('Ready with Device ID', device_id);
            window.deviceId = device_id;
        });
        
        // Non prêt
        player.addListener('not_ready', ({ device_id }) => {
            console.log('Device ID has gone offline', device_id);
        });
        
        // État du lecteur
        player.addListener('player_state_changed', state => {
            if (!state) return;
            
            const currentTrack = state.track_window.current_track;
            document.getElementById('trackName').textContent = currentTrack.name;
            document.getElementById('artistName').textContent = currentTrack.artists.map(artist => artist.name).join(', ');
            document.getElementById('albumCover').src = currentTrack.album.images[0].url;
            
            // Mettre à jour le bouton play/pause
            const playPauseButton = document.getElementById('playPauseButton');
            if (state.paused) {
                playPauseButton.innerHTML = '<i class="fas fa-play fa-3x"></i>';
            } else {
                playPauseButton.innerHTML = '<i class="fas fa-pause fa-3x"></i>';
            }
        });
        
        // Connecter le lecteur
        player.connect();
        
        // Contrôles
        document.getElementById('playPauseButton').addEventListener('click', () => {
            player.togglePlay();
        });
        
        document.getElementById('previousButton').addEventListener('click', () => {
            player.previousTrack();
        });
        
        document.getElementById('nextButton').addEventListener('click', () => {
            player.nextTrack();
        });
        
        document.getElementById('volumeSlider').addEventListener('input', (e) => {
            player.setVolume(e.target.value / 100);
        });
        
        // Playlists prédéfinies pour les seniors
        const playlists = {
            favorites: 'spotify:playlist:37i9dQZF1DXcBWIGoYBM5M', // Remplacer par une vraie playlist
            classical: 'spotify:playlist:37i9dQZF1DWWEJlAGA9gs0',
            jazz: 'spotify:playlist:37i9dQZF1DXbITWG1ZJKYt',
            frenchSongs: 'spotify:playlist:37i9dQZF1DX5W4wuxak2hE'
        };
        
        function playPlaylist(playlistUri) {
            fetch(`https://api.spotify.com/v1/me/player/play?device_id=${window.deviceId}`, {
                method: 'PUT',
                body: JSON.stringify({ context_uri: playlistUri }),
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });
        }
        
        document.getElementById('playFavorites').addEventListener('click', () => {
            playPlaylist(playlists.favorites);
        });
        
        document.getElementById('playClassical').addEventListener('click', () => {
            playPlaylist(playlists.classical);
        });
        
        document.getElementById('playJazz').addEventListener('click', () => {
            playPlaylist(playlists.jazz);
        });
        
        document.getElementById('playFrenchSongs').addEventListener('click', () => {
            playPlaylist(playlists.frenchSongs);
        });
    };
</script>
