<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la temporisation de sortie
ob_start();
?>

<div class="container home-container">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-md-8">
            <h1 class="display-4 fw-bold text-warning mb-3">Bienvenue sur SunnyLink</h1>
            <p class="lead">Restez connecté avec vos proches âgés.</p>
            <p class="mb-5">SunnyLink est dédié à vous aider à maintenir des liens forts avec vos proches âgés grâce à une communication et une interaction faciles. Partagez des photos, passez des appels vidéo, gérez des événements et recevez des rappels pour rester en contact.</p>
            <div class="row mt-5">
        <div class="col-md-12">
            <img src="images/Photointerface.png" alt="SunnyLink Family Connection" class="img-fluid rounded shadow-sm">
        </div>
    </div>
</div>
        </div>
    </div>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'familymember'): ?>
        <div class="row features-section">
            <div class="col-md-3 mb-4">
                <div class="feature-box text-center p-4 rounded shadow-sm">
                    <img src="images/IconePhoto.png" alt="Photos" class="feature-icon mb-3" width="64">
                    <h4>Partage de photos</h4>
                    <p class="text-muted">Partagez des moments spéciaux avec vos proches</p>
                    <a href="index.php?controller=photo&action=form" class="btn btn-warning mt-2">Envoyer des photos</a>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="feature-box text-center p-4 rounded shadow-sm">
                    <img src="images/iconeMessage.png" alt="Messages" class="feature-icon mb-3" width="64">
                    <h4>Messages</h4>
                    <p class="text-muted">Envoyez des messages texte ou audio</p>
                    <a href="index.php?controller=message&action=send" class="btn btn-info mt-2">Envoyer un message</a>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="feature-box text-center p-4 rounded shadow-sm">
                    <img src="images/iconeAgenda.png" alt="Événements" class="feature-icon mb-3" width="64">
                    <h4>Création d'événements</h4>
                    <p class="text-muted">Planifiez et partagez des événements</p>
                    <a href="index.php?controller=event&action=add" class="btn btn-primary mt-2">Gérer les événements</a>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="feature-box text-center p-4 rounded shadow-sm">
                    <img src="images/IconeRappel.png" alt="Notifications" class="feature-icon mb-3" width="64">
                    <h4>Notifications</h4>
                    <p class="text-muted">Suivez les interactions avec vos proches</p>
                    <a href="index.php?controller=notification&action=index" class="btn btn-success mt-2">Voir les notifications</a>
                </div>
            </div>
        </div>
        
        <div class="row mt-5 justify-content-center">
            <div class="col-md-6 text-center">
                <a href="index.php?controller=home&action=family_dashboard" class="btn btn-lg btn-warning px-5 py-3 fw-bold">Accéder au tableau de bord</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 text-center">
                <p class="mb-4">Connectez-vous pour accéder à toutes les fonctionnalités de SunnyLink</p>
                <a href="index.php?controller=auth&action=login" class="btn btn-lg btn-warning px-5 py-3 fw-bold">Commencer</a>
            </div>
        </div>
    <?php endif; ?>
    


<style>
    .home-container {
        padding: 40px 0;
    }
    
    .feature-box {
        background-color: white;
        transition: transform 0.3s ease;
        height: 100%;
    }
    
    .feature-box:hover {
        transform: translateY(-5px);
    }
    
    .feature-icon {
        margin-bottom: 15px;
    }
    
    .features-section {
        margin-bottom: 40px;
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../base.php';
?>
