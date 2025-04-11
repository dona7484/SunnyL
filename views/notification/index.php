<?php
// Assurez-vous que l'utilisateur est connecté et est un senior
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'senior') {
    header('Location: index.php?controller=auth&action=login');
    exit;
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Vos notifications</h2>
    
    <?php if (empty($notifications)): ?>
        <div class="alert alert-info text-center">
            Vous n'avez aucune notification non lue.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($notifications as $notification): ?>
                <div class="col-md-12 mb-3">
                    <div class="card notification-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">
                                    <?php 
                                    switch($notification['type']) {
                                        case 'message':
                                            echo '<i class="fas fa-envelope"></i> Nouveau message';
                                            break;
                                        case 'audio':
                                            echo '<i class="fas fa-microphone"></i> Nouveau message audio';
                                            break;
                                        case 'photo':
                                            echo '<i class="fas fa-image"></i> Nouvelle photo';
                                            break;
                                        case 'event':
                                            echo '<i class="fas fa-calendar"></i> Nouvel événement';
                                            break;
                                        default:
                                            echo '<i class="fas fa-bell"></i> Notification';
                                    }
                                    ?>
                                </h5>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></small>
                            </div>
                            <p class="card-text"><?= htmlspecialchars($notification['content']) ?></p>
                            <div class="text-center">
                                <button class="btn btn-success view-notification-btn" 
                                   data-id="<?= $notification['id'] ?>"
                                   data-type="<?= $notification['type'] ?>"
                                   data-related-id="<?= $notification['related_id'] ?>">
                                    <i class="fas fa-check"></i> Consulter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.notification-card {
    transition: all 0.3s ease;
    border-left: 5px solid #007bff;
}

.notification-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.notification-card .btn-success {
    font-size: 1.2rem;
    padding: 10px 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des gestionnaires d'événements pour les boutons "Consulter"
    const viewNotificationButtons = document.querySelectorAll('.view-notification-btn');
    
    viewNotificationButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notifId = this.dataset.id;
            const notifType = this.dataset.type;
            const relatedId = this.dataset.relatedId;
            switch($notification['type']) {
    case 'message':
        echo '<i class="fas fa-envelope"></i> Nouveau message';
        break;
    case 'audio':
        echo '<i class="fas fa-microphone"></i> Nouveau message audio';
        break;
    case 'photo':
        echo '<i class="fas fa-image"></i> Nouvelle photo';
        break;
    // ...
}

            // Marquer la notification comme lue
            fetch('index.php?controller=notification&action=markNotificationAsRead', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notif_id: notifId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rediriger vers la page appropriée selon le type
                    let redirectUrl = 'index.php?controller=home&action=dashboard';
                    
                    if (notifType === 'message' || notifType === 'audio') {
                        redirectUrl = 'index.php?controller=message&action=received';
                    } else if (notifType === 'photo' && relatedId) {
                        redirectUrl = 'index.php?controller=photo&action=view&id=' + relatedId;
                    } else if (notifType === 'event' && relatedId) {
                        redirectUrl = 'index.php?controller=event&action=view&id=' + relatedId;
                    }
                    
                    console.log("Redirection vers:", redirectUrl, "Type:", notifType);
                    window.location.href = redirectUrl;
                }
            })
            .catch(error => {
                console.error('Erreur lors du marquage comme lu:', error);
            });
        });
    });
});
</script>
