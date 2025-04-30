<div style="padding:40px;max-width:500px;margin:auto;">
    <h2>Ajouter un parent âgé</h2>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background-color:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <form action="index.php?controller=relation&action=store" method="post">
        <label for="senior_id">Sélectionner un senior:</label>
        <select name="senior_id" id="senior_id" required style="width:100%;padding:8px;margin:10px 0;">
            <option value="">-- Choisir un senior --</option>
            <?php if (isset($seniors) && !empty($seniors)): ?>
                <?php foreach ($seniors as $senior): ?>
                    <option value="<?= $senior['id'] ?>"><?= htmlspecialchars($senior['name']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <button type="submit" style="background:#ffc107;color:#fff;padding:10px 30px;border:none;border-radius:8px;">Ajouter</button>
    </form>
</div>