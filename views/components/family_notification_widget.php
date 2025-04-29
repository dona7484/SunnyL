<?php
/**
 * Widget de notification pour les family members
 * À inclure dans le dashboard des membres de la famille
 */
if (!isset($_SESSION)) {
    session_start();
}

// Vérifier que l'utilisateur est connecté et est un family member
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'familymember') {
    return; // Ne pas afficher le widget si ce n'est pas un family member
}

// Récupérer les notifications actives
require_once __DIR__ . '/../../models/Notification.php';
$notifications = Notification::getUnreadByUserId($_SESSION['user_id']);

// Compter les notifications par type
$messageCount = 0;
$readConfirmationCount = 0;
$photoCount = 0;
$eventCount = 0;

foreach ($notifications as $notification) {
    switch ($notification['type']) {
        case 'message':
        case 'audio':
            $messageCount++;
            break;
        case 'read_confirmation':
            $readConfirmationCount++;
            break;
        case 'photo':
            $photoCount++;
            break;
        case 'event':
            $eventCount++;
            break;
    }
}

$totalCount = count($notifications);
?>

<!-- Widget de notification -->
<div class="family-notification-widget">
    <h4 class="widget-title">
        <i class="fas fa-bell"></i> Notifications
        <?php if ($totalCount > 0): ?>
        <span class="badge bg-danger"><?= $totalCount ?></span>
        <?php endif; ?>
    </h4>
    
    <div class="notification-categories">
        <?php if ($messageCount > 0): ?>
        <div class="notif-category notif-messages">
            <div class="notif-icon"><i class="fas fa-envelope"></i></div>
            <div class="notif-details">
                <div class="notif-title">Messages</div>
                <div class="notif-count"><?= $messageCount ?> non lu<?= $messageCount > 1 ? 's' : '' ?></div>
            </div>
            <a href="/SunnyLink/public/index.php?controller=message&action=received" class="notif-action">Voir</a>
        </div>
        <?php endif; ?>
        
        <?php if ($readConfirmationCount > 0): ?>
        <div class="notif-category notif-confirmations">
            <div class="notif-icon"><i class="fas fa-check-double"></i></div>
            <div class="notif-details">
                <div class="notif-title">Confirmations de lecture</div>
                <div class="notif-count"><?= $readConfirmationCount ?> nouvelle<?= $readConfirmationCount > 1 ? 's' : '' ?></div>
            </div>
            <a href="/SunnyLink/public/index.php?controller=message&action=sent" class="notif-action">Voir</a>
        </div>
        <?php endif; ?>
        
        <?php if ($photoCount > 0): ?>
        <div class="notif-category notif-photos">
            <div class="notif-icon"><i class="fas fa-image"></i></div>
            <div class="notif-details">
                <div class="notif-title">Photos</div>
                <div class="notif-count"><?= $photoCount ?> nouvelle<?= $photoCount > 1 ? 's' : '' ?></div>
            </div>
            <a href="/SunnyLink/public/index.php?controller=photo&action=gallery" class="notif-action">Voir</a>
        </div>
        <?php endif; ?>
        
        <?php if ($eventCount > 0): ?>
        <div class="notif-category notif-events">
            <div class="notif-icon"><i class="fas fa-calendar"></i></div>
            <div class="notif-details">
                <div class="notif-title">Événements</div>
                <div class="notif-count"><?= $eventCount ?> nouveau<?= $eventCount > 1 ? 'x' : '' ?></div>
            </div>
            <a href="/SunnyLink/public/index.php?controller=event&action=index" class="notif-action">Voir</a>
        </div>
        <?php endif; ?>
        
        <?php if ($totalCount === 0): ?>
        <div class="no-notifications">
            <i class="fas fa-check-circle"></i>
            <p>Vous n'avez pas de nouvelles notifications</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .family-notification-widget {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .widget-title {
        margin-top: 0;
        margin-bottom: 15px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .widget-title i {
        color: #FFD700;
    }
    
    .notification-categories {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .notif-category {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 8px;
        transition: background-color 0.2s;
    }
    
    .notif-category:hover {
        background-color: #f8f9fa;
    }
    
    .notif-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
    }
    
    .notif-messages .notif-icon {
        background-color: rgba(66, 133, 244, 0.1);
        color: #4285F4;
    }
    
    .notif-confirmations .notif-icon {
        background-color: rgba(52, 168, 83, 0.1);
        color: #34A853;
    }
    
    .notif-photos .notif-icon {
        background-color: rgba(234, 67, 53, 0.1);
        color: #EA4335;
    }
    
    .notif-events .notif-icon {
        background-color: rgba(251, 188, 5, 0.1);
        color: #FBBC05;
    }
    
    .notif-details {
        flex-grow: 1;
    }
    
    .notif-title {
        font-weight: 600;
        color: #333;
    }
    
    .notif-count {
        font-size: 14px;
        color: #666;
    }
    
    .notif-action {
        background-color: #f1f3f4;
        color: #333;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 14px;
        transition: background-color 0.2s;
    }
    
    .notif-action:hover {
        background-color: #e2e6ea;
        color: #333;
    }
    
    .no-notifications {
        padding: 20px;
        text-align: center;
        color: #666;
    }
    
    .no-notifications i {
        font-size: 40px;
        color: #34A853;
        margin-bottom: 10px;
    }
</style>