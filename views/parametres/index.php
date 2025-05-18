<!-- views/parametres/index.php -->
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white">
            <h2 class="mb-0 text-center">
                <?php echo ($_SESSION['role'] === 'senior') ? 'Mes Paramètres' : 'Mon Compte'; ?>
            </h2>
        </div>
        <div class="card-body" style="background-color: #f9f9f9;">
            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message'] ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <!-- Section commune : Photo de profil et informations personnelles -->
            <div class="row">
                <!-- Photo de profil -->
                <div class="col-md-4 text-center mb-4">
                    <div class="profile-img-container">
                        <img id="profile-preview" 
                             src="<?= !empty($user['avatar']) ? '/SunnyLink/public/images/' . htmlspecialchars($user['avatar']) : '/SunnyLink/public/images/default-profile.png' ?>"
                             alt="Photo de profil"
                             class="img-fluid rounded-circle mb-3" 
                             style="width: 170px; height: 170px; object-fit: cover; border: 3px solid #ffc107;">
                    </div>
                    
                    <form id="avatar-form" action="index.php?controller=parametres&action=updateProfile" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="avatar-upload" class="btn btn-outline-warning <?= ($_SESSION['role'] === 'senior') ? 'btn-lg' : ''; ?>">
                                <i class="fas fa-camera"></i> Changer <?= ($_SESSION['role'] === 'senior') ? 'ma' : 'la'; ?> photo
                            </label>
                            <input id="avatar-upload" type="file" name="avatar" accept="image/*" class="d-none" onchange="previewImage(this)">
                            <button type="submit" id="save-avatar" class="btn btn-warning <?= ($_SESSION['role'] === 'senior') ? 'btn-lg' : ''; ?> d-none">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Informations personnelles -->
                <div class="col-md-8">
                    <form action="index.php?controller=parametres&action=updateProfile" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold <?= ($_SESSION['role'] === 'senior') ? 'fs-5' : ''; ?>">
                                <?= ($_SESSION['role'] === 'senior') ? 'Mon nom' : 'Nom'; ?>
                            </label>
                            <input type="text" class="form-control <?= ($_SESSION['role'] === 'senior') ? 'form-control-lg' : ''; ?>" 
                                  id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold <?= ($_SESSION['role'] === 'senior') ? 'fs-5' : ''; ?>">
                                <?= ($_SESSION['role'] === 'senior') ? 'Mon email' : 'Email'; ?>
                            </label>
                            <input type="email" class="form-control <?= ($_SESSION['role'] === 'senior') ? 'form-control-lg' : ''; ?>" 
                                  id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning <?= ($_SESSION['role'] === 'senior') ? 'btn-lg' : ''; ?>">
                            <i class="fas fa-check"></i> Enregistrer <?= ($_SESSION['role'] === 'senior') ? '' : 'les modifications'; ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <hr class="my-4">
            
            <?php if($_SESSION['role'] === 'senior'): ?>
            <!-- ====================== DÉBUT SECTION SENIOR ====================== -->
            
            <!-- Accessibilité pour senior -->
            <h4 class="mb-3 fs-3"><i class="fas fa-universal-access"></i> Accessibilité</h4>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="text-size" class="form-label fw-bold fs-5">Taille du texte</label>
                        <div class="d-flex align-items-center">
                            <span class="me-2">A</span>
                            <input type="range" class="form-range" min="1" max="5" step="1" id="text-size" value="3">
                            <span class="ms-2">A</span>
                            <span class="ms-2 fs-3">A</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold fs-5">Contraste</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-dark active" data-theme="normal">Normal</button>
                            <button class="btn btn-outline-dark" data-theme="high-contrast">Contraste élevé</button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voice-reading" checked>
                            <label class="form-check-label fs-5" for="voice-reading">Lecture vocale des notifications</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4 class="mb-3 fs-3"><i class="fas fa-volume-up"></i> Sons et alertes</h4>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="volume-level" class="form-label fw-bold fs-5">Volume des notifications</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-volume-down me-2"></i>
                            <input type="range" class="form-range" min="0" max="100" step="10" id="volume-level" value="80">
                            <i class="fas fa-volume-up ms-2"></i>
                        </div>
                        <button class="btn btn-primary mt-2" onclick="playTestSound()">
                            <i class="fas fa-play"></i> Tester le son
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable-reminders" checked>
                            <label class="form-check-label fs-5" for="enable-reminders">Alertes pour les événements</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ====================== FIN SECTION SENIOR ====================== -->
            <?php else: ?>
            <!-- ====================== DÉBUT SECTION FAMILLE ====================== -->
            
            <!-- Parents âgés liés -->
            <h4 class="mb-3"><i class="fas fa-users"></i> Parents âgés liés</h4>
            
            <?php if (empty($linkedParents)): ?>
                <div class="alert alert-info">
                    Vous n'avez pas encore de parent lié à votre compte.
                </div>
            <?php else: ?>
                <div class="list-group mb-3">
                    <?php foreach ($linkedParents as $parent): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-circle me-2"></i>
                                <?= htmlspecialchars($parent['name']) ?>
                            </div>
                            <form method="post" action="index.php?controller=parametres&action=removeParent" class="m-0">
                                <input type="hidden" name="parent_id" value="<?= $parent['user_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lien?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <a href="index.php?controller=relation&action=create" class="btn btn-primary mb-4">
                <i class="fas fa-plus-circle"></i> Ajouter un parent
            </a>
            
            <!-- ====================== FIN SECTION FAMILLE ====================== -->
            <?php endif; ?>
            
            <hr class="my-4">
            
            <!-- Section commune : Sécurité -->
            <h4 class="mb-3 <?= ($_SESSION['role'] === 'senior') ? 'fs-3' : ''; ?>">
                <i class="fas fa-lock"></i> Sécurité
            </h4>
            
            <form method="post" action="index.php?controller=parametres&action=updatePassword" class="mb-4">
                <?php if($_SESSION['role'] === 'senior'): ?>
                <!-- Version senior (une colonne) -->
                <div class="mb-3">
                    <label for="new_password" class="form-label fw-bold fs-5">Nouveau mot de passe</label>
                    <input type="password" class="form-control form-control-lg" id="new_password" name="new_password" 
                           placeholder="Nouveau mot de passe" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-bold fs-5">Confirmer le mot de passe</label>
                    <input type="password" class="form-control form-control-lg" id="confirm_password" 
                           placeholder="Confirmer le mot de passe" required oninput="checkPasswordMatch()">
                    <div id="password-match-message" class="form-text text-danger d-none">
                        Les mots de passe ne correspondent pas
                    </div>
                </div>
                <?php else: ?>
                <!-- Version famille (deux colonnes) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Nouveau mot de passe" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-bold">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   placeholder="Confirmer le mot de passe" required oninput="checkPasswordMatch()">
                            <div id="password-match-message" class="form-text text-danger d-none">
                                Les mots de passe ne correspondent pas
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-warning <?= ($_SESSION['role'] === 'senior') ? 'btn-lg' : ''; ?>" id="change-password-btn">
                    <i class="fas fa-key"></i> Modifier le mot de passe
                </button>
            </form>
            
            <hr class="my-4">
            
            <!-- Déconnexion -->
            <div class="text-center mt-5">
                <a href="index.php?controller=auth&action=logout" class="btn btn-danger btn-lg <?= ($_SESSION['role'] === 'senior') ? 'fs-4' : ''; ?>">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-img-container {
    position: relative;
    display: inline-block;
}

.profile-img-container:hover::after {
    content: "Changer";
    position: absolute;
    bottom: 3px;
    left: 0;
    right: 0;
    background-color: rgba(255, 193, 7, 0.7);
    color: white;
    padding: 5px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script>
// Prévisualiser l'image avant upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
            document.getElementById('save-avatar').classList.remove('d-none');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Vérifier que les mots de passe correspondent
function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const message = document.getElementById('password-match-message');
    const button = document.getElementById('change-password-btn');
    
    if (password !== confirmPassword) {
        message.classList.remove('d-none');
        button.disabled = true;
    } else {
        message.classList.add('d-none');
        button.disabled = false;
    }
}

// Pour la version senior uniquement
<?php if($_SESSION['role'] === 'senior'): ?>
function playTestSound() {
    const audio = new Audio('/SunnyLink/public/audio/notif-sound.mp3');
    audio.volume = document.getElementById('volume-level').value / 100;
    audio.play().catch(e => {
        console.warn("Impossible de jouer le son test:", e);
        alert("Votre navigateur a bloqué la lecture du son. Veuillez autoriser les sons sur ce site.");
    });
}

// Gestion de la taille du texte
document.getElementById('text-size').addEventListener('change', function() {
    const size = this.value;
    const sizes = {
        1: '0.9rem',
        2: '1rem',
        3: '1.1rem',
        4: '1.2rem',
        5: '1.3rem'
    };
    
    document.documentElement.style.fontSize = sizes[size];
    localStorage.setItem('text-size', size);
});

// Charger la taille de texte sauvegardée
if (localStorage.getItem('text-size')) {
    document.getElementById('text-size').value = localStorage.getItem('text-size');
    const event = new Event('change');
    document.getElementById('text-size').dispatchEvent(event);
}
<?php endif; ?>
</script>