<?php
$title = "FAQ - SunnyLink";
?>

<div class="container mt-5">
    <h1 class="text-center mb-5">Foire Aux Questions</h1>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="mb-4">
                <form class="mb-5">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher une question..." id="faq-search">
                        <button class="btn btn-primary" type="button">Rechercher</button>
                    </div>
                </form>
            </div>
            
            <!-- Section 1: Questions générales -->
            <div class="faq-section mb-5">
                <h2 class="mb-4">Questions générales</h2>
                
                <div class="accordion" id="accordionGeneral">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Qu'est-ce que SunnyLink ?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionGeneral">
                            <div class="accordion-body">
                                SunnyLink est une plateforme qui permet aux membres de la famille d'envoyer des messages, des photos et des rappels d'événements à leurs proches âgés. L'application est spécialement conçue pour être facile à utiliser pour les seniors, avec une interface adaptée sur tablette qui affiche clairement les notifications, les photos et les messages.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Comment fonctionne SunnyLink ?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionGeneral">
                            <div class="accordion-body">
                                Les membres de la famille s'inscrivent sur SunnyLink et connectent leur compte à celui de leur parent âgé. Ils peuvent ensuite envoyer des messages, des photos et créer des événements qui apparaîtront sur le tableau de bord du senior. Le senior reçoit une notification dès qu'un nouveau contenu est disponible et peut y accéder facilement depuis sa tablette.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                SunnyLink est-il gratuit ?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionGeneral">
                            <div class="accordion-body">
                                SunnyLink propose une version de base gratuite qui permet d'accéder aux fonctionnalités essentielles. Des options premium sont disponibles pour les utilisateurs souhaitant bénéficier de fonctionnalités supplémentaires comme le stockage illimité de photos ou les appels vidéo.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Fonctionnalités -->
            <div class="faq-section mb-5">
                <h2 class="mb-4">Fonctionnalités</h2>
                
                <div class="accordion" id="accordionFeatures">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Comment envoyer une photo à mon parent ?
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFeatures">
                            <div class="accordion-body">
                                Pour envoyer une photo, connectez-vous à votre compte, accédez à la section "Photos" depuis votre tableau de bord, cliquez sur "Envoyer une photo", sélectionnez l'image depuis votre appareil, ajoutez éventuellement un message et cliquez sur "Envoyer". Votre parent recevra une notification et pourra voir la photo dans sa galerie.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Comment créer un événement ou un rappel ?
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionFeatures">
                            <div class="accordion-body">
                                Pour créer un événement, accédez à la section "Événements" de votre tableau de bord, cliquez sur "Ajouter un événement", remplissez les informations demandées (titre, date, heure, description, lieu), configurez l'alerte si nécessaire et cliquez sur "Créer l'événement". Votre parent recevra une notification au moment que vous aurez défini.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Comment envoyer un message audio ?
                            </button>
                        </h3>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionFeatures">
                            <div class="accordion-body">
                                Pour envoyer un message audio, allez dans la section "Messages" de votre tableau de bord, cliquez sur "Nouveau message", sélectionnez "Message audio", enregistrez votre message en cliquant sur le bouton d'enregistrement, puis cliquez sur "Envoyer". Votre parent recevra une notification et pourra écouter votre message.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 3: Problèmes techniques -->
            <div class="faq-section mb-5">
                <h2 class="mb-4">Problèmes techniques</h2>
                
                <div class="accordion" id="accordionTechnical">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingSeven">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                Mon parent ne reçoit pas les notifications, que faire ?
                            </button>
                        </h3>
                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionTechnical">
                            <div class="accordion-body">
                                Vérifiez d'abord que la tablette est bien connectée à Internet. Ensuite, assurez-vous que les notifications sont activées dans les paramètres de l'application. Si le problème persiste, essayez de redémarrer la tablette ou de réinstaller l'application. Si aucune de ces solutions ne fonctionne, contactez notre support technique.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingEight">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                Comment réinitialiser le mot de passe ?
                            </button>
                        </h3>
                        <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#accordionTechnical">
                            <div class="accordion-body">
                                Pour réinitialiser votre mot de passe, cliquez sur "Mot de passe oublié" sur la page de connexion, saisissez votre adresse e-mail et suivez les instructions envoyées par e-mail. Si vous n'avez pas accès à votre e-mail, contactez notre support technique pour une assistance supplémentaire.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingNine">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                L'application est lente ou se bloque, que faire ?
                            </button>
                        </h3>
                        <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-bs-parent="#accordionTechnical">
                            <div class="accordion-body">
                                Si l'application est lente ou se bloque, essayez de la fermer complètement et de la rouvrir. Vérifiez également que votre appareil dispose de suffisamment d'espace de stockage et que vous utilisez la dernière version de l'application. Si le problème persiste, essayez de redémarrer votre appareil ou de réinstaller l'application.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <p>Vous n'avez pas trouvé la réponse à votre question ?</p>
                <a href="index.php?controller=support&action=contact" class="btn btn-primary">Contactez-nous</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Script pour la recherche dans la FAQ
    document.getElementById('faq-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const accordionItems = document.querySelectorAll('.accordion-item');
        
        accordionItems.forEach(item => {
            const question = item.querySelector('.accordion-button').textContent.toLowerCase();
            const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
            
            if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
