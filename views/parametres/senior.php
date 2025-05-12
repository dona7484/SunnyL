
<div style="background:#ffe600;padding:0 0 10px 0;">
    <div style="display:flex;align-items:center;gap:20px;">
    <a href="index.php?controller=home&action=dashboard" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        <div style="flex:1;text-align:center;">
            <span style="font-size:28px;font-weight:bold;">Mon compte</span><br>
            <span style="font-size:18px;">Nom : <?= htmlspecialchars($user['name']) ?></span><br>
            <span style="font-size:16px;">Contact d'urgence : <?= htmlspecialchars($user['emergency_contact'] ?? '') ?></span>
        </div>
        <img src="/SunnyLink/public/images/volume.png" alt="Son" style="width:40px;margin:10px 10px 0 0;">
    </div>
</div>
    <!-- Contenu principal -->
    <div style="flex:1;padding:30px 0 30px 0;max-width:600px;margin:auto;">
        <!-- Paramètres personnalisables -->
        <h2 style="text-align:center;font-size:22px;margin-bottom:18px;">Paramètres personnalisables</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <button class="btn-green">Activer/Désactiver le son</button>
            <!-- <button class="btn-green">Mode sombre/clair</button>  // SUPPRIMÉ -->
            <button class="btn-green">Ajuster la taille du texte</button>
            <button class="btn-green">Activer/Désactiver messages audio</button>
            <button class="btn-green">Activer/Désactiver appels vidéos</button>
            <button class="btn-green">Activer/Désactiver Alerte Rappel</button>
            <button class="btn-green">Activer/Désactiver Diaporama</button>
        </div>

        <!-- Gestion des contacts -->
        <h2 style="text-align:center;font-size:22px;margin:30px 0 10px 0;">Gestion des contacts</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <button class="btn-orange">Ajouter un contact</button>
            <button class="btn-red">Supprimer un contact</button>
        </div>

        <!-- Sécurité et assistance -->
        <h2 style="text-align:center;font-size:22px;margin:30px 0 10px 0;">Sécurité et assistance</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="index.php?controller=message&action=send" class="btn-red" style="text-decoration:none;text-align:center;">Écrire à mon proche</a>
            <a href="index.php?controller=support&action=faq" class="btn-green" style="text-decoration:none;text-align:center;">Aide & Support</a>
            <form method="post" action="index.php?controller=auth&action=logout" style="margin:0;">
                <button type="submit" class="btn-red">Déconnexion</button>
            </form>
        </div>
    </div>
</div>

<!-- Styles boutons -->
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
.btn-green {
    background: #39b54a;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px 0;
    font-size: 18px;
    font-weight: bold;
    width: 100%;
    cursor: pointer;
}
.btn-orange {
    background: #ffb300;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px 0;
    font-size: 18px;
    font-weight: bold;
    width: 100%;
    cursor: pointer;
}
.btn-red {
    background: #e53935;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px 0;
    font-size: 18px;
    font-weight: bold;
    width: 100%;
    cursor: pointer;
    margin-bottom: 0;
}
</style>
