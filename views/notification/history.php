<h2>Historique des notifications</h2>
<ul>
<?php foreach ($notifications as $notif): ?>
    <li>
        <?= htmlspecialchars($notif['content']) ?>
        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></small>
        <?php if (!$notif['is_read']): ?>
            <span class="badge bg-warning">Non lue</span>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
<a href="index.php?controller=home&action=dashboard" class="btn btn-secondary mt-3">Retour au tableau de bord</a>
