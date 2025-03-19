<?php $title = htmlspecialchars("Modification de l'événement - " . $events->getTitle(), ENT_QUOTES); ?>
<article class="row justify-content-center text-center">
    <h1 class="col-12"><?php echo htmlspecialchars($events->getTitle(), ENT_QUOTES); ?></h1>
    <p>Date de publication : <?php echo date("d/m/Y", strtotime($events->getDate())); ?></p>
    <p><?php echo htmlspecialchars($events->getDescription(), ENT_QUOTES); ?></p>
</article>

<form action="index.php?controller=event&action=update&id=<?php echo htmlspecialchars($events->getId(), ENT_QUOTES); ?>" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label">Titre de l'événement</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($events->getTitle(), ENT_QUOTES); ?>" required>
    </div>
    <div class="mb-3">
        <label for="date" class="form-label">Date et heure</label>
        <input type="datetime-local" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($events->getDate(), ENT_QUOTES); ?>" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($events->getDescription(), ENT_QUOTES); ?></textarea>
    </div>
    <div class="mb-3">
        <label for="lieu" class="form-label">Lieu</label>
        <input type="text" class="form-control" id="lieu" name="lieu" value="<?php echo htmlspecialchars($events->getLieu() ?? '', ENT_QUOTES); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Mettre à jour l'événement</button>
</form>
