<?php
// Définir des valeurs par défaut pour éviter les erreurs
$eventId = isset($events) && method_exists($events, 'getId') ? $events->getId() : 0;
$title = isset($events) && method_exists($events, 'getTitle') ? htmlspecialchars($events->getTitle(), ENT_QUOTES) : 'Titre non disponible';
$description = isset($events) && method_exists($events, 'getDescription') ? htmlspecialchars($events->getDescription(), ENT_QUOTES) : 'Description non disponible';
$lieu = isset($events) && method_exists($events, 'getLieu') ? htmlspecialchars($events->getLieu(), ENT_QUOTES) : 'Lieu non spécifié';
$date = isset($events) && method_exists($events, 'getDate') ? $events->getDate() : date('Y-m-d H:i:s');
$recurrence = isset($events) && method_exists($events, 'getRecurrence') ? htmlspecialchars($events->getRecurrence(), ENT_QUOTES) : '';
$alertTime = isset($events) && method_exists($events, 'getAlertTime') ? htmlspecialchars($events->getAlertTime(), ENT_QUOTES) : '';
$notificationMessage = isset($events) && method_exists($events, 'getNotificationMessage') ? htmlspecialchars($events->getNotificationMessage(), ENT_QUOTES) : '';
$isRead = isset($events) && method_exists($events, 'isRead') ? $events->isRead() : false;
$isTriggered = isset($events) && method_exists($events, 'isTriggered') ? $events->isTriggered() : false;

// Formater la récurrence pour l'affichage
$recurrenceDisplay = 'Événement unique';
if ($recurrence === 'daily') {
    $recurrenceDisplay = 'Tous les jours';
} elseif ($recurrence === 'weekly') {
    $recurrenceDisplay = 'Toutes les semaines';
} elseif ($recurrence === 'monthly') {
    $recurrenceDisplay = 'Tous les mois';
}

// Récupérer les participants
$participants = isset($participants) ? $participants : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'événement</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclure Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f8fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .event-header {
            background: linear-gradient(135deg, #4e95ff, #3a7bd5);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .event-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        .event-date {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .back-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: white;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 1.2rem 1.5rem;
            font-weight: 600;
            color: #333;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 1.2rem;
            display: flex;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-icon {
            width: 32px;
            color: #4e95ff;
            margin-right: 10px;
            text-align: center;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #555;
        }
        
        .info-value {
            color: #666;
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .badge-read {
            background-color: #28a745;
            color: white;
        }
        
        .badge-alerted {
            background-color: #ffc107;
            color: #212529;
        }
        
        .action-btn {
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            margin-right: 0.8rem;
            margin-bottom: 0.8rem;
            display: inline-flex;
            align-items: center;
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        .participant-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .participant-item {
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .participant-item:last-child {
            border-bottom: none;
        }
        
        .participant-avatar {
            width: 36px;
            height: 36px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête de l'événement -->
        <div class="event-header">
            <h1 class="event-title"><?php echo $title; ?></h1>
            <p class="event-date">
                <i class="far fa-calendar-alt me-2"></i>
                <?php echo date("d/m/Y à H:i", strtotime($date)); ?>
            </p>
            <a href="index.php?controller=event&action=index" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour à la liste des événements
            </a>
        </div>
        
        <div class="row">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        Informations sur l'événement
                    </div>
                    <div class="card-body">
                        <!-- Description -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-align-left"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Description</div>
                                <div class="info-value"><?php echo $description; ?></div>
                            </div>
                        </div>
                        
                        <!-- Lieu -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Lieu</div>
                                <div class="info-value"><?php echo $lieu; ?></div>
                            </div>
                        </div>
                        
                        <!-- Récurrence -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Récurrence</div>
                                <div class="info-value"><?php echo $recurrenceDisplay; ?></div>
                            </div>
                        </div>
                        
                        <!-- Alerte -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Alerte programmée</div>
                                <div class="info-value">
                                    <?php if (!empty($alertTime)): ?>
                                        <?php echo date("d/m/Y à H:i", strtotime($alertTime)); ?>
                                    <?php else: ?>
                                        Aucune alerte programmée
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Message de notification -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-comment-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Message de notification</div>
                                <div class="info-value"><?php echo $notificationMessage; ?></div>
                            </div>
                        </div>
                        
                        <!-- Statut -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Statut</div>
                                <div class="info-value">
                                    <?php if ($isRead): ?>
                                        <span class="badge badge-read">Lu</span>
                                    <?php elseif ($isTriggered): ?>
                                        <span class="badge badge-alerted">Alerté</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerted">Non alerté</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Participants et actions -->
            <div class="col-lg-4">
                <!-- Participants -->
                <div class="card">
                    <div class="card-header">
                        Participants
                    </div>
                    <div class="card-body">
                        <?php if (isset($participants) && count($participants) > 0): ?>
                            <ul class="participant-list">
                                <?php foreach ($participants as $participant): ?>
                                    <li class="participant-item">
                                        <div class="participant-avatar">
                                            <?php echo substr(htmlspecialchars($participant['participant_name'] ?? '', ENT_QUOTES), 0, 1); ?>
                                        </div>
                                        <div class="participant-name">
                                            <?php echo htmlspecialchars($participant['participant_name'] ?? '', ENT_QUOTES); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-users d-block mb-2" style="font-size: 2rem;"></i>
                                Aucun participant ajouté
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        Actions
                    </div>
                    <div class="card-body">
                        <a href="index.php?controller=event&action=update&id=<?php echo $eventId; ?>" class="btn btn-primary action-btn">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="index.php?controller=event&action=delete&id=<?php echo $eventId; ?>" class="btn btn-danger action-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>