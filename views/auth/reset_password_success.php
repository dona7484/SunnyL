<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un nouveau mot de passe - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f8fa;
            font-family: 'Arial', sans-serif;
            padding-top: 60px;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            height: 80px;
        }
        .btn-primary {
            background-color: #FFD700;
            border-color: #FFD700;
            color: #333;
        }
        .btn-primary:hover {
            background-color: #e6c200;
            border-color: #e6c200;
            color: #333;
        }
        .password-strength-meter {
            margin-top: 10px;
        }
        .password-strength-text {
            margin-top: 5px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="SunnyLink Logo">
                </a>
                <h2 class="mt-3">Créer un nouveau mot de passe</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <p class="text-muted mb-4">
                Choisissez un nouveau mot de passe fort pour votre compte.
            </p>
            
            <form method="post" action="index.php?controller=auth&action=processResetPassword">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="password-strength-meter mt-2">
                        <div class="progress">
                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div id="password-strength-text" class="password-strength-text text-muted">
                            Force du mot de passe: Trop faible
                        </div>
                    </div>
                    <div class="form-text">
                        Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <div id="password-match-message" class="form-text"></div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="submit-button" disabled>Réinitialiser mon mot de passe</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        const matchMessage = document.getElementById('password-match-message');
        const submitButton = document.getElementById('submit-button');
        
        // Fonction pour valider la force du mot de passe
        function validatePasswordStrength(password) {
            let strength = 0;
            
            // Vérifier la longueur
            if (password.length >= 8) {
                strength += 25;
            }
            
            // Vérifier les majuscules
            if (password.match(/[A-Z]/)) {
                strength += 25;
            }
            
            // Vérifier les minuscules
            if (password.match(/[a-z]/)) {
                strength += 25;
            }
            
            // Vérifier les chiffres
            if (password.match(/[0-9]/)) {
                strength += 25;
            }
            
            return strength;
        }
        
        // Fonction pour mettre à jour l'indicateur de force
        function updateStrengthMeter() {
            const password = newPasswordInput.value;
            const strength = validatePasswordStrength(password);
            
            // Mettre à jour la barre de progression
            strengthBar.style.width = strength + '%';
            strengthBar.setAttribute('aria-valuenow', strength);
            
            // Changer la couleur et le texte en fonction de la force
            if (strength < 50) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Force du mot de passe: Faible';
            } else if (strength < 100) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Force du mot de passe: Moyenne';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Force du mot de passe: Forte';
            }
            
            checkFormValidity();
        }
        
        // Fonction pour vérifier la correspondance des mots de passe
        function checkPasswordMatch() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (!confirmPassword) {
                matchMessage.textContent = '';
                matchMessage.className = 'form-text';
                return;
            }
            
            if (password === confirmPassword) {
                matchMessage.textContent = 'Les mots de passe correspondent.';
                matchMessage.className = 'form-text text-success';
            } else {
                matchMessage.textContent = 'Les mots de passe ne correspondent pas.';
                matchMessage.className = 'form-text text-danger';
            }
            
            checkFormValidity();
        }
        
        // Fonction pour vérifier la validité du formulaire
        function checkFormValidity() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const strength = validatePasswordStrength(password);
            
            // Activer le bouton uniquement si les critères sont remplis
            if (strength === 100 && password === confirmPassword && password.length >= 8) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }
        
        // Ajouter les écouteurs d'événements
        newPasswordInput.addEventListener('input', updateStrengthMeter);
        newPasswordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    });
    </script>
</body>
</html>