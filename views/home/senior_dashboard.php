// Vue pour le tableau de bord du senior
<h2>Tableau de bord Senior</h2>
<p>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?>.</p>
<h4>Vos proches :</h4>
<?php if (!empty($familyMembers)): ?>
    <ul>
        <?php foreach ($familyMembers as $fm): ?>
            <li><?= htmlspecialchars($fm['name']) ?> (<?= htmlspecialchars($fm['email']) ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun proche associé pour l'instant.</p>
<?php endif; ?>
