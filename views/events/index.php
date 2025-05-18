<?php
// Inclure et enregistrer l'autoloader
require_once '../Autoloader.php';
Autoloader::register();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Maintenant, vous pouvez utiliser vos classes sans require_once
$eventModel = new EventModel();
$event = new Event();

// Exemple d'utilisation de la classe EventModel
$list = $eventModel->findAll();

// Définir le titre de la page
$title = "Mes Événements";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclure Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e95ff;
            --secondary-color: #ff7e5f;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f8fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #3a7bd5);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-weight: 700;
            font-size: 2.5rem;
            margin: 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
            border: none;
        }
        
        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .section-title {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .add-event-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .add-event-btn:hover {
            background-color: #3a7bd5;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .add-event-btn i {
            margin-right: 8px;
        }
        
        .event-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
            border: none;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .event-header {
            background-color: #f8f9fa;
            padding: 1.25rem;
            border-bottom: 1px solid #eee;
        }
        
        .event-title {
            font-size: 1.25rem;
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .event-body {
            padding: 1.25rem;
        }
        
        .event-info {
            margin-bottom: 1rem;
        }
        
        .event-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .event-info-icon {
            color: var(--primary-color);
            font-size: 1rem;
            width: 24px;
            text-align: center;
            margin-right: 0.75rem;
        }
        
        .event-description {
            color: #666;
            margin-bottom: 1.25rem;
        }
        
        .event-actions {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        
        .event-action-btn {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-details {
            background-color: #f8f9fa;
            color: #333;
        }
        
        .btn-details:hover {
            background-color: #e9ecef;
        }
        
        .btn-edit {
            background-color: #e9ecef;
            color: #333;
        }
        
        .btn-edit:hover {
            background-color: #dee2e6;
        }
        
        .btn-delete {
            background-color: #f8d7da;
            color: #dc3545;
        }
        
        .btn-delete:hover {
            background-color: #f5c2c7;
        }
        
        .event-action-btn i {
            margin-right: 6px;
        }
        
        .event-status {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .badge-read {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-alerted {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .empty-state-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .empty-state-text {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .event-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .event-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="content-container text-center">
            <h1 class="page-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            <a href="index.php?controller=home&action=<?= ($_SESSION['role'] === 'senior') ? 'dashboard' : 'family_dashboard' ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>
    
    <div class="content-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Liste des Événements</h2>
            <a href="index.php?controller=event&action=add" class="add-event-btn">
                <i class="fas fa-plus"></i> Ajouter un événement
            </a>
        </div>
        
        <?php if (empty($list)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="far fa-calendar"></i>
                </div>
                <h3 class="empty-state-title">Aucun événement trouvé</h3>
                <p class="empty-state-text">Vous n'avez pas encore créé d'événements.</p>
                <a href="index.php?controller=event&action=add" class="add-event-btn">
                    <i class="fas fa-plus"></i> Créer le premier événement
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($list as $event): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="event-card">
                            <div class="event-header position-relative">
                                <h3 class="event-title"><?= htmlspecialchars($event->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="event-status">
                                    <?php if ($event->isRead()): ?>
                                        <span class="badge badge-read">Lu</span>
                                    <?php elseif ($event->isTriggered()): ?>
                                        <span class="badge badge-alerted">Alerté</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerted">Non alerté</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="event-body">
                                <div class="event-info">
                                    <div class="event-info-item">
                                        <i class="fas fa-calendar-day event-info-icon"></i>
                                        <span><?= date('d/m/Y', strtotime($event->getDate())) ?></span>
                                    </div>
                                    <div class="event-info-item">
                                        <i class="fas fa-clock event-info-icon"></i>
                                        <span><?= date('H:i', strtotime($event->getDate())) ?></span>
                                    </div>
                                </div>
                                <div class="event-description">
                                    <?= htmlspecialchars($event->getDescription(), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="event-actions">
                                    <a href="index.php?controller=event&action=show&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>" class="event-action-btn btn-details">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                    <a href="index.php?controller=event&action=update&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>" class="event-action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="index.php?controller=event&action=delete&id=<?= htmlspecialchars($event->getId(), ENT_QUOTES, 'UTF-8') ?>" class="event-action-btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>