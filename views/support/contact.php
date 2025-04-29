<?php
$title = "Contact - SunnyLink";
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="text-center mb-5">Contactez-nous</h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h3 class="mb-4">Envoyez-nous un message</h3>
                            <form method="POST" action="index.php?controller=support&action=contact">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Adresse email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Sujet</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Envoyer</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h3 class="mb-4">Informations de contact</h3>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope-fill fs-4 me-3 text-primary"></i>
                                </div>
                                <div>
                                    <h5>Email</h5>
                                    <p><a href="mailto:dona7484@gmail.com">dona7484@gmail.com</a></p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-telephone-fill fs-4 me-3 text-primary"></i>
                                </div>
                                <div>
                                    <h5>Téléphone</h5>
                                    <p>+33 (0)6 14 27 00 54</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-geo-alt-fill fs-4 me-3 text-primary"></i>
                                </div>
                                <div>
                                    <h5>Adresse</h5>
                                    <p>123 Rue du Soleil<br>75000 Paris<br>France</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h5>Suivez-nous</h5>
                                <div class="d-flex gap-3 mt-3">
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-facebook fs-4 text-primary"></i>
                                    </a>
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-twitter fs-4 text-primary"></i>
                                    </a>
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-instagram fs-4 text-primary"></i>
                                    </a>
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-linkedin fs-4 text-primary"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <h3 class="mb-4">Questions fréquentes</h3>
                <p>Avant de nous contacter, vous pourriez trouver la réponse à votre question dans notre <a href="index.php?controller=support&action=faq">FAQ</a>.</p>
                
                <div class="accordion" id="accordionFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Comment connecter un compte senior à mon compte famille ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Pour connecter un compte senior à votre compte famille, accédez à vos paramètres, puis à la section "Parents âgés liés" et cliquez sur "Ajouter un parent". Vous devrez ensuite saisir l'adresse email du senior pour établir la connexion.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Comment savoir si mon message a été lu ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Lorsque votre message a été lu par le senior, vous recevrez une notification de confirmation de lecture. Vous pouvez également vérifier le statut de vos messages dans la section "Messages envoyés" où une icône indiquera si le message a été lu.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
