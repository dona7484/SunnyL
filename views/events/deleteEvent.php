<?php $title = "Mon portfolio - Suppression d'une création"; ?>
<article class="row justify-content-center text-center">
    <h1 class="col-12"><?php echo $events->title; ?></h1>
    <p>Date de publication: <?php echo date("d/m/Y", strtotime($events->created_at)); ?></p>
    <img class="col-4" src="<?php echo $events->picture; ?>" alt="<?php echo $events->title; ?>">
    <p><?php echo $events->description; ?></p>
</article>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $erreur; ?>
    </div>
<?php endif; ?>

<div class="alert alert-warning" role="alert">
    <p>Êtes-vous sûr de vouloir supprimer la création : <?php echo $events->title; ?> ?</p>
    <?php echo $deleteForm; ?>
</div>
