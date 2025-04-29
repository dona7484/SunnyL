<div style="padding:40px;max-width:500px;margin:auto;">
    <h2>Ajouter un parent âgé</h2>
    <form action="index.php?controller=relation&action=store" method="post">
        <label for="parent_name">Nom du parent :</label>
        <input type="text" name="parent_name" id="parent_name" required style="width:100%;padding:8px;margin:10px 0;">
        <label for="parent_email">Email du parent :</label>
        <input type="email" name="parent_email" id="parent_email" required style="width:100%;padding:8px;margin:10px 0;">
        <button type="submit" style="background:#ffc107;color:#fff;padding:10px 30px;border:none;border-radius:8px;">Ajouter</button>
    </form>
</div>