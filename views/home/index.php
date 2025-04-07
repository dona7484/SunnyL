<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<h2>Bonjour, vous êtes sur la page d'accueil</h2>
<p>Bienvenue sur mon site web SunnyLink.</p>

<?php if ($_SESSION['role'] === 'familymember'): ?>
    <div class="row mt-5">
        <!-- Envoyer des photos -->
        <div class="col-12 col-md-6 mb-3">
            <a href="index.php?controller=photo&action=form" class="btn btn-warning btn-lg w-100">
                Envoyer des photos
            </a>
        </div>

        <!-- Envoyer des messages -->
        <div class="col-12 col-md-6 mb-3">
            <a href="index.php?controller=message&action=send" class="btn btn-info btn-lg w-100">
                Envoyer un message
            </a>
        </div>

        <!-- Gérer les événements -->
        <div class="col-12 col-md-6 mb-3">
            <a href="index.php?controller=event&action=create" class="btn btn-primary btn-lg w-100">
                Gérer les événements
            </a>
        </div>

        <!-- Voir les notifications -->
        <div class="col-12 col-md-6 mb-3">
            <a href="index.php?controller=notification&action=index" class="btn btn-success btn-lg w-100">
                Voir les notifications
            </a>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../base.php';
?>
