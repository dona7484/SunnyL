<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie Photos - SunnyLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
          .back-dashboard-btn {
    display: inline-flex;
    align-items: center;
    background-color: #4a4a4a;
    color: white;
    padding: 12px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease;
    border: none;
    margin-bottom: 20px;
}

.back-dashboard-btn:hover {
    background-color: #333333;
    color: white;
    text-decoration: none;
}

.back-dashboard-btn i {
    margin-right: 8px;
}
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .photo-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .photo-card:hover {
            transform: scale(1.03);
        }
        
        .photo-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .photo-info {
            padding: 10px;
            background-color: white;
        }
        
        .photo-date {
            font-size: 0.8rem;
            color: #666;
        }
        
        .photo-message {
            margin-top: 5px;
            font-weight: bold;
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-read {
            background-color: #28a745;
            color: white;
        }
        
        .status-alerted {
            background-color: #ffc107;
            color: black;
        }
        
        .status-pending {
            background-color: #dc3545;
            color: white;
        }
        
        /* Styles pour le modal */
        .modal-photo {
            max-width: 100%;
            max-height: 70vh;
            display: block;
            margin: 0 auto;
            border-radius: 8px;
        }
        
        .modal-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 18px;
        }
        
        .modal-date {
            font-size: 14px;
            color: #6c757d;
            margin-top: 10px;
            text-align: right;
        }
        
        /* Styles adaptés aux seniors */
        .modal-content {
            border-radius: 15px;
        }
        
        .modal-header {
            background-color: #f8f9fa;
        }
        
        .btn-close-modal {
            font-size: 20px;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .delete-button {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 30px;
    height: 30px;
    background-color: rgba(220, 53, 69, 0.8);
    color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s ease;
}

.delete-button:hover {
    background-color: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.delete-icon {
    font-size: 20px;
    font-weight: bold;
}

/* Style pour la boîte de dialogue de confirmation */
.confirmation-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    max-width: 400px;
    width: 90%;
}

.confirmation-dialog h3 {
    margin-top: 0;
    color: #dc3545;
}

.confirmation-dialog p {
    margin-bottom: 20px;
}

.confirmation-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

    </style>
</head>
<body>
<a href="index.php?controller=home&action=<?= ($_SESSION['role'] === 'senior') ? 'dashboard' : 'family_dashboard' ?>" class="back-dashboard-btn">
    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
</a>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Galerie Photos</h1>
        
        <?php if (empty($photos)): ?>
            <div class="alert alert-info">
                Aucune photo n'a été partagée pour le moment.
            </div>
        <?php else: ?>
            <div class="gallery-container">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-card" onclick="openPhotoModal('<?= htmlspecialchars($photo['url']) ?>', '<?= htmlspecialchars($photo['message'] ?? '') ?>', '<?= htmlspecialchars($photo['created_at']) ?>', <?= $photo['id'] ?>)">
    <img src="<?= htmlspecialchars($photo['url']) ?>" alt="Photo" class="photo-img">
                        <!-- Bouton de suppression -->
    <div class="delete-button" onclick="event.stopPropagation(); confirmDelete(<?= $photo['id'] ?>)">
        <i class="delete-icon">×</i>
    </div>
                        <?php
                        $statusClass = '';
                        $statusText = $photo['status'] ?? 'Non alerté';
                        
                        switch ($statusText) {
                            case 'Lu':
                                $statusClass = 'status-read';
                                break;
                            case 'Alerté':
                                $statusClass = 'status-alerted';
                                break;
                            default:
                                $statusClass = 'status-pending';
                                $statusText = 'Non alerté';
                        }
                        ?>
                        
                        <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span>
                        
                        <div class="photo-info">
                            <div class="photo-date">
                                Envoyée le <?= date('d/m/Y à H:i', strtotime($photo['created_at'])) ?>
                            </div>
                            <?php if (!empty($photo['message'])): ?>
                                <div class="photo-message">
                                    <?= htmlspecialchars($photo['message']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="index.php?controller=photo&action=form" class="btn btn-primary">Envoyer une nouvelle photo</a>
        </div>
    </div>
    
    <!-- Modal pour afficher la photo agrandie -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Photo partagée</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <img src="" id="modalPhoto" class="modal-photo" alt="Photo agrandie">
                    <div id="modalMessage" class="modal-message"></div>
                    <div id="modalDate" class="modal-date"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-close-modal" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour ouvrir le modal avec la photo agrandie
        function openPhotoModal(url, message, date, photoId) {
            // Définir les contenus du modal
            document.getElementById('modalPhoto').src = url;
            document.getElementById('modalMessage').textContent = message || 'Aucun message associé à cette photo';
            document.getElementById('modalDate').textContent = 'Envoyée le ' + formatDate(date);
            
            // Marquer la photo comme vue
            markPhotoAsViewed(photoId);
            
            // Ouvrir le modal
            var photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
            photoModal.show();
        }
        
        // Fonction pour formater la date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR') + ' à ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
        }
        // Fonction pour afficher la boîte de dialogue de confirmation
function confirmDelete(photoId) {
    // Créer l'overlay
    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);
    
    // Créer la boîte de dialogue
    const dialog = document.createElement('div');
    dialog.className = 'confirmation-dialog';
    dialog.innerHTML = `
        <h3>Confirmer la suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer cette photo ? Cette action est irréversible.</p>
        <div class="confirmation-buttons">
            <button class="btn btn-secondary" onclick="closeConfirmDialog()">Annuler</button>
            <button class="btn btn-danger" onclick="deletePhoto(${photoId})">Supprimer</button>
        </div>
    `;
    document.body.appendChild(dialog);
}

// Fonction pour fermer la boîte de dialogue
function closeConfirmDialog() {
    const overlay = document.querySelector('.overlay');
    const dialog = document.querySelector('.confirmation-dialog');
    
    if (overlay) overlay.remove();
    if (dialog) dialog.remove();
}

// Fonction pour supprimer la photo
function deletePhoto(photoId) {
    fetch('index.php?controller=photo&action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            photoId: photoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Fermer la boîte de dialogue
            closeConfirmDialog();
            
            // Supprimer la photo de l'affichage
            const photoCard = document.querySelector(`.photo-card[onclick*="photoId: ${photoId}"]`);
            if (photoCard) {
                photoCard.remove();
            } else {
                // Recharger la page si on ne trouve pas l'élément
                window.location.reload();
            }
            
            // Afficher un message de succès
            showNotification('Photo supprimée avec succès', 'success');
        } else {
            showNotification('Erreur lors de la suppression : ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur lors de la suppression :', error);
        showNotification('Erreur lors de la suppression', 'error');
    });
}

// Fonction pour afficher une notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} notification-alert`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

        // Fonction pour marquer la photo comme vue
        function markPhotoAsViewed(photoId) {
            fetch('index.php?controller=photo&action=markViewed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    photoId: photoId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Photo marquée comme vue:', data);
                // Mettre à jour visuellement le statut si nécessaire
                const statusBadges = document.querySelectorAll('.status-badge');
                statusBadges.forEach(badge => {
                    const card = badge.closest('.photo-card');
                    if (card && card.getAttribute('onclick').includes('photoId: ' + photoId)) {
                        badge.textContent = 'Lu';
                        badge.classList.remove('status-pending', 'status-alerted');
                        badge.classList.add('status-read');
                    }
                });
            })
            .catch(error => {
                console.error('Erreur lors du marquage de la photo comme vue:', error);
            });
        }
    </script>
</body>
</html>
