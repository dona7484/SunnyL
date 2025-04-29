<?php
$title = "SunnyLink - Tableau de bord familial";
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?controller=auth&action=login");
    exit;
}

// Charger les modèles nécessaires
require_once __DIR__ . '/../../models/NotificationModel.php';
require_once __DIR__ . '/../../models/Activity.php';
require_once __DIR__ . '/../../models/SeniorModel.php';

// Récupérer les informations de l'utilisateur
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'familymember';

// Récupérer les notifications non lues
$notifModel = new NotificationModel();
$notifications = $notifModel->getUnreadNotifications($userId);
$unreadCount = $notifModel->getUnreadCount($userId);

// Récupérer les seniors associés à ce family member
$seniorModel = new SeniorModel();
$seniors = $seniorModel->getSeniorsForFamilyMember($userId);

// Récupérer les activités récentes
$activities = Activity::getRecentActivities($userId, 5);
?>

<div class="container family-dashboard mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-home"></i> Tableau de bord de la famille</h2>
                <div>
                    <a href="index.php?controller=auth&action=logout" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne de gauche pour les notifications et seniors -->
        <div class="col-md-4">
            <!-- Widget de notification -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-bell"></i> Notifications 
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $unreadCount ?></span>
                    <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <p class="text-center text-muted">Aucune notification non lue</p>
                    <?php else: ?>
                        <ul class="list-group notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php 
                                        $icon = 'fas fa-bell';
                                        $badge = 'bg-secondary';
                                        
                                        switch($notification['type']) {
                                            case 'message':
                                                $icon = 'fas fa-envelope';
                                                $badge = 'bg-primary';
                                                break;
                                            case 'audio':
                                                $icon = 'fas fa-microphone';
                                                $badge = 'bg-info';
                                                break;
                                            case 'photo':
                                                $icon = 'fas fa-image';
                                                $badge = 'bg-success';
                                                break;
                                            case 'event':
                                                $icon = 'fas fa-calendar';
                                                $badge = 'bg-warning';
                                                break;
                                            case 'read_confirmation':
                                                $icon = 'fas fa-check-double';
                                                $badge = 'bg-success';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badge ?> me-2">
                                            <i class="<?= $icon ?>"></i>
                                        </span>
                                        <?= htmlspecialchars($notification['content']) ?>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                                            data-notif-id="<?= $notification['id'] ?>"
                                            data-type="<?= $notification['type'] ?>"
                                            data-related-id="<?= $notification['related_id'] ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Liste des seniors connectés -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-users"></i> Mes seniors</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($seniors)): ?>
                        <p class="text-center text-muted">Aucun senior associé à votre compte</p>
                        <div class="text-center">
                            <a href="index.php?controller=relation&action=create" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle"></i> Ajouter un senior
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="senior-list">
                            <?php foreach ($seniors as $senior): ?>
                                <div class="senior-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded">
                                    <div>
                                        <i class="fas fa-user-circle me-2"></i>
                                        <span><?= htmlspecialchars($senior['name']) ?></span>
                                    </div>
                                    <div class="btn-group">
                                        <a href="index.php?controller=message&action=send&receiver_id=<?= $senior['user_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-comment"></i>
                                        </a>
                                
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Activité récente -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Activité récente</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <p class="text-center text-muted">Aucune activité récente</p>
                    <?php else: ?>
                        <ul class="list-group activity-list">
                            <?php foreach ($activities as $activity): ?>
                                <li class="list-group-item">
                                    <?php
                                    $icon = 'fas fa-check-circle';
                                    
                                    switch ($activity['type']) {
                                        case 'message':
                                            $icon = 'fas fa-envelope';
                                            break;
                                        case 'photo':
                                            $icon = 'fas fa-image';
                                            break;
                                        case 'event':
                                            $icon = 'fas fa-calendar';
                                            break;
                                        case 'audio':
                                            $icon = 'fas fa-microphone';
                                            break;
                                    }
                                    ?>
                                    <div class="d-flex align-items-start">
                                        <div class="activity-icon me-3">
                                            <i class="<?= $icon ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="mb-1"><?= htmlspecialchars($activity['content']) ?></p>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Colonne de droite pour les actions rapides -->
        <div class="col-md-8">
            <!-- Actions rapides -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="fas fa-bolt"></i> Actions rapides</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="index.php?controller=message&action=send" class="quick-action-card">
                                <div class="card h-100 text-center p-3">
                                    <div class="card-body">
                                        <i class="fas fa-comment fa-3x mb-3 text-primary"></i>
                                        <h5>Envoyer un message</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="index.php?controller=photo&action=form" class="quick-action-card">
                                <div class="card h-100 text-center p-3">
                                    <div class="card-body">
                                        <i class="fas fa-image fa-3x mb-3 text-success"></i>
                                        <h5>Partager une photo</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="index.php?controller=event&action=add" class="quick-action-card">
                                <div class="card h-100 text-center p-3">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-plus fa-3x mb-3 text-warning"></i>
                                        <h5>Créer un événement</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="index.php?controller=message&action=received" class="quick-action-card">
                                <div class="card h-100 text-center p-3">
                                    <div class="card-body">
                                        <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                        <h5>Messages reçus</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="index.php?controller=photo&action=gallery" class="quick-action-card">
                                <div class="card h-100 text-center p-3">
                                    <div class="card-body">
                                        <i class="fas fa-images fa-3x mb-3 text-danger"></i>
                                        <h5>Galerie photos</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Événements à venir -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar"></i> Événements à venir</h4>
                </div>
                <div class="card-body">
                    <div id="upcoming-events">
                        <!-- Les événements seront chargés via AJAX -->
                        <p class="text-center text-muted">Chargement des événements...</p>
                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php?controller=event&action=index" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-alt"></i> Tous les événements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .family-dashboard {
        padding-bottom: 30px;
    }
    
    .quick-action-card {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.3s ease;
    }
    
    .quick-action-card:hover {
        transform: translateY(-5px);
    }
    
    .quick-action-card .card {
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }
    
    .quick-action-card:hover .card {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #ddd;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border-radius: 50%;
        font-size: 18px;
    }
    
    .notification-list .list-group-item {
        transition: background-color 0.2s;
    }
    
    .notification-list .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .senior-item {
        transition: all 0.3s ease;
    }
    
    .senior-item:hover {
        background-color: #f8f9fa;
    }
    
    .mark-read-btn {
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .list-group-item:hover .mark-read-btn {
        opacity: 1;
    }
</style>

<script>
// Fonction pour marquer une notification comme lue
function markNotificationAsRead(notifId, type, relatedId) {
    fetch('index.php?controller=notification&action=markNotificationAsRead', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notif_id: notifId,
            type: type,
            related_id: relatedId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Retirer la notification de la liste
            const notifItem = document.querySelector(`[data-notif-id="${notifId}"]`).closest('.list-group-item');
            notifItem.classList.add('fade-out');
            setTimeout(() => {
                notifItem.remove();
                
                // Mettre à jour le compteur
                const countBadge = document.querySelector('.card-header .badge');
                if (countBadge) {
                    let count = parseInt(countBadge.textContent) - 1;
                    if (count <= 0) {
                        countBadge.remove();
                    } else {
                        countBadge.textContent = count;
                    }
                }
                
                // Si la liste est vide, afficher un message
                const notifList = document.querySelector('.notification-list');
                if (notifList && notifList.children.length === 0) {
                    notifList.innerHTML = '<p class="text-center text-muted">Aucune notification non lue</p>';
                }
                
                // Rediriger en fonction du type de notification
                redirectBasedOnType(type, relatedId);
            }, 300);
        } else {
            console.error('Erreur lors du marquage comme lu:', data.error);
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error);
    });
}

// Fonction pour rediriger en fonction du type de notification
function redirectBasedOnType(type, relatedId) {
    let redirectUrl = null;
    
    switch(type) {
        case 'message':
        case 'audio':
            redirectUrl = 'index.php?controller=message&action=received';
            break;
        case 'photo':
            redirectUrl = 'index.php?controller=photo&action=gallery';
            break;
        case 'event':
            if (relatedId) {
                redirectUrl = `index.php?controller=event&action=show&id=${relatedId}`;
            } else {
                redirectUrl = 'index.php?controller=event&action=index';
            }
            break;
        case 'read_confirmation':
            redirectUrl = 'index.php?controller=message&action=sent';
            break;
    }
    
    if (redirectUrl) {
        window.location.href = redirectUrl;
    }
}

// Charger les événements à venir
function loadUpcomingEvents() {
    const eventsContainer = document.getElementById('upcoming-events');
    
    fetch('index.php?controller=event&action=getUpcoming')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.length === 0) {
                eventsContainer.innerHTML = '<p class="text-center text-muted">Aucun événement à venir</p>';
                return;
            }
            
            let eventsHtml = '<div class="list-group">';
            
            data.forEach(event => {
                const eventDate = new Date(event.date);
                const formattedDate = eventDate.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                eventsHtml += `
                    <a href="index.php?controller=event&action=show&id=${event.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">${event.title}</h5>
                            <small>${formattedDate}</small>
                        </div>
                        <p class="mb-1">${event.description}</p>
                        <small>${event.lieu || 'Aucun lieu spécifié'}</small>
                    </a>
                `;
            });
            
            eventsHtml += '</div>';
            eventsContainer.innerHTML = eventsHtml;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des événements:', error);
            eventsContainer.innerHTML = '<p class="text-center text-danger">Erreur lors du chargement des événements</p>';
        });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les gestionnaires d'événements pour les boutons "Marquer comme lu"
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const notifId = this.dataset.notifId;
            const type = this.dataset.type;
            const relatedId = this.dataset.relatedId;
            
            markNotificationAsRead(notifId, type, relatedId);
        });
    });
    
    // Charger les événements à venir
    loadUpcomingEvents();
    
    // Vérifier régulièrement les nouvelles notifications
    setInterval(function() {
        if (typeof checkForNewNotifications === 'function') {
            checkForNewNotifications();
        }
    }, 30000); // Toutes les 30 secondes
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../base.php';
?>