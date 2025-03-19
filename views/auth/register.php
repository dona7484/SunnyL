
<form action="index.php?controller=auth&action=register" method="POST">
    <label for="name">Nom :</label>
    <input type="text" name="name" required><br>

    <label for="email">Email :</label>
    <input type="email" name="email" required><br>

    <label for="password">Mot de passe :</label>
    <input type="password" name="password" required><br>

    <button type="submit">S'inscrire</button>
</form>

<p>Déjà inscrit ? <a href="index.php?controller=auth&action=login">Se connecter</a></p>