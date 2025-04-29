<!-- views/parametres/index.php -->
<div class="container-parametres" style="max-width:900px;margin:auto;padding:30px 0;">
    <div style="background:#ffc107;padding:15px 0 10px 0;border-radius:10px 10px 0 0;text-align:center;">
        <h2 style="margin:0;color:#fff;">Mon Compte</h2>
    </div>
    <div style="background:#f3fcd7;padding:30px;border-radius:0 0 10px 10px;">
        <div style="text-align:center;">
            <img src="<?= !empty($user['avatar']) ? '/SunnyLink/public/images/' . htmlspecialchars($user['avatar']) : '/SunnyLink/public/images/default-profile.png' ?>"
                 alt="Photo de profil"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #ffc107;">
            <form action="index.php?controller=parametres&action=updateProfile" method="post" enctype="multipart/form-data" style="margin-top:10px;">
                <input type="file" name="avatar" accept="image/*" style="display:inline;">
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Nom" required style="margin-left:10px;">
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required style="margin-left:10px;">
                <button type="submit" class="btn btn-primary" style="background:#ffb300;color:#fff;border:none;padding:6px 18px;border-radius:8px;margin-left:10px;">Modifier le profil</button>
            </form>
        </div>

        <h3 style="margin-top:40px;">Parents âgés liés</h3>
        <?php foreach ($linkedParents as $parent): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;background:#fff;padding:10px 18px;border-radius:8px;margin-bottom:10px;">
                <span><?= htmlspecialchars($parent['name']) ?></span>
                <form method="post" action="index.php?controller=parametres&action=removeParent" style="margin:0;">
                    <input type="hidden" name="parent_id" value="<?= $parent['user_id'] ?>">
                    <button type="submit" class="btn btn-danger" style="background:#e53935;color:#fff;border:none;padding:5px 18px;border-radius:8px;">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
        <a href="index.php?controller=parametres&action=addParent" class="btn btn-secondary" style="background:#90caf9;color:#fff;border:none;padding:7px 18px;border-radius:8px;margin-top:10px;display:inline-block;">Ajouter un parent</a>

        <h3 style="margin-top:40px;">Sécurité</h3>
        <form method="post" action="index.php?controller=parametres&action=updatePassword" style="display:flex;align-items:center;gap:10px;">
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required style="padding:7px;border-radius:6px;border:1px solid #ddd;">
            <button type="submit" class="btn btn-warning" style="background:#ffb300;color:#fff;border:none;padding:7px 18px;border-radius:8px;">Modifier le mot de passe</button>
        </form>

        <h3 style="margin-top:40px;">Aide & Support</h3>
        <a href="index.php?controller=support&action=faq" class="btn btn-info" style="background:#e0f2f1;color:#333;padding:7px 16px;border-radius:8px;margin-right:10px;">FAQ</a>
        <a href="index.php?controller=support&action=contact" class="btn btn-info" style="background:#e0f2f1;color:#333;padding:7px 16px;border-radius:8px;">Contacter le support</a>

        <form method="post" action="index.php?controller=auth&action=logout" style="margin-top:40px;text-align:center;">
            <button type="submit" class="btn btn-danger" style="background:#e53935;color:#fff;padding:10px 50px;border-radius:8px;font-size:18px;">Déconnexion</button>
        </form>
    </div>
</div>
