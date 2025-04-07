<?php
$title = "SunnyLink - Accueil";
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!-- Contenu de la page d'accueil -->
<h2>Bienvenue sur SunnyLink</h2>
<p>Restez connecté avec vos proches âgés.</p>
<p>SunnyLink est dédié à vous aider à maintenir des liens forts avec vos proches âgés grâce à une communication facile et des interactions simples. Partagez des photos, passez des appels vidéo, gérez des événements et bien plus encore.</p>

<!-- Liens dynamiques pour familymember -->
<?php if ($_SESSION['role'] === 'familymember'): ?>
    <div class="row">
        <div class="col-6">
            <a href="index.php?controller=photo&action=form" class="btn btn-warning btn-lg">Partage de photos</a>
        </div>
        <div class="col-6">
            <a href="index.php?controller=call&action=make" class="btn btn-info btn-lg">Passer un appel vidéo</a>
        </div>
        <div class="col-6">
            <a href="index.php?controller=event&action=create" class="btn btn-primary btn-lg">Créer un événement</a>
        </div>
        <div class="col-6">
            <a href="index.php?controller=notification&action=create" class="btn btn-success btn-lg">Envoyer une notification</a>
        </div>
    </div>
<?php endif; ?>
