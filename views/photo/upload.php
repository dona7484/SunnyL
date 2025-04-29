<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer une photo - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .preview-container {
            margin-top: 20px;
            text-align: center;
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
            display: none;
            margin: 0 auto;
            border-radius: 5px;
        }
        
        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="upload-container">
            <h2 class="text-center mb-4">Envoyer une photo au senior</h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            <form id="photoUploadForm" action="index.php?controller=photo&action=uploadPhoto" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="senior_id" class="form-label">Destinataire</label>
                    <select class="form-select" id="senior_id" name="senior_id" required>
                        <option value="">Choisir un senior...</option>
                        <?php
                        // Récupérer la liste des seniors associés à ce family member
                        $familyMemberId = $_SESSION['user_id'];
                        $seniorModel = new SeniorModel();
                        $seniors = $seniorModel->getSeniorsForFamilyMember($familyMemberId);
                        
                        foreach ($seniors as $senior) {
                            echo '<option value="' . $senior['user_id'] . '">' . htmlspecialchars($senior['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="photo" class="form-label">Sélectionner une photo</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required onchange="previewImage(this)">
                </div>
                
                <div class="preview-container">
                    <img id="imagePreview" src="#" alt="Aperçu de l'image">
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Message (optionnel)</label>
                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Envoyer la photo</button>
                    <a href="index.php?controller=photo&action=gallery" class="btn btn-outline-secondary">Voir la galerie</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Fonction pour prévisualiser l'image avant l'envoi
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
