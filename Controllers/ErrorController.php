<?php
// Fichier: C:\wamp64\www\SunnyLink\Controllers\ErrorController.php

class ErrorController extends Controller {
    
    public function index($message = 'Une erreur est survenue') {
        $this->render('error/generic', ['message' => $message]);
    }
    
    public function spotify($message = 'Erreur Spotify') {
        $this->render('error/spotify', ['error' => $message]);
    }
    
    public function notFound() {
        $this->render('error/404', ['message' => 'Page non trouvée']);
    }
    
    public function serverError() {
        $this->render('error/500', ['message' => 'Erreur serveur interne']);
    }
}
