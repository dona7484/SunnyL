<?php
$title = "Envoyer une photo à la tablette";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?= htmlspecialchars($title) ?></h2>
        <form method="POST" action="index.php?controller=photo&action=uploadPhoto" enctype="multipart/form-data">
  <!-- Si vous avez une liste de seniors associés, vous pouvez utiliser un select -->
  <div class="mb-3">
    <label for="senior_id" class="form-label">Sélectionnez le senior destinataire :</label>
    <select name="senior_id" id="senior_id" class="form-control" required>
      <!-- Remplacez les options par les seniors associés à ce family member -->
      <option value="">-- Choisissez --</option>
      <option value="4">Jean Dupont</option>
      <!-- Vous pouvez ajouter d'autres options ici -->
    </select>
  </div>
  
  <!-- Champ pour le message -->
  <div class="mb-3">
    <label for="message" class="form-label">Message associé (facultatif) :</label>
    <input type="text" class="form-control" id="message" name="message">
  </div>

  <!-- Champ pour le fichier photo -->
  <div class="mb-3">
    <label for="photo" class="form-label">Choisissez une photo :</label>
    <input type="file" class="form-control" id="photo" name="photo" required>
  </div>

  <button type="submit" class="btn btn-primary">Envoyer la photo</button>
</form>

    </div>
</body>
</html>
