<?php $title = "Connexion"; ?>
<h2>Connexion</h2>
<?php if (isset($erreur)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($erreur, ENT_QUOTES); ?>
    </div>
<?php endif; ?>
<form action="index.php?controller=auth&action=login" method="POST">
    <div class="form-group">
        <label for="name">Nom d'utilisateur :</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>