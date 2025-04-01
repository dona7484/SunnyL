<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Démarrer la session seulement si elle n'est pas déjà active
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SunnyLink' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/44a16ebfd9.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <header class="text-center">
            <h1>SunnyLink</h1>
            <?php if (isset($_SESSION['name'])): ?>
                <p>Bienvenue, <?= htmlspecialchars($_SESSION['name'], ENT_QUOTES); ?>!</p>
            <?php endif; ?>
        </header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">SunnyLink</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
    <a class="nav-link" href="index.php?controller=event&action=index">Événements</a>
</li>
<li class="nav-item">
<a class="nav-link" href="index.php?controller=photo&action=form">Envoyer une photo</a>
<a class="nav-link" href="index.php?controller=photo&action=gallery&id=<?= $_SESSION['user_id'] ?? 1 ?>">Galerie</a>
            </li>
 <li class="nav-item">
                            <?php if (isset($_SESSION['name'])): ?>
                                <a class="nav-link" href="index.php?controller=auth&action=logout">Se déconnecter</a>
                            <?php else: ?>
                                <a class="nav-link" href="index.php?controller=auth&action=login">Se connecter</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <main>
            <?= $content ?>
        </main>
        <footer class="text-center">
            <p>&copy; 2025 - SunnyLink</p>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
