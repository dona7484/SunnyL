<?php

// namespace Controllers;

abstract class Controller
{
    public function render(string $path, array $data = [])
    {
        // Vérifiez que le fichier de vue existe avant de l'inclure
        $viewFile = dirname(__DIR__) . '/views/' . $path . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("Le fichier de vue '$viewFile' n'existe pas.");
        }

        extract($data); // Transforme ['list' => $list] en $list directement utilisable

        ob_start(); // Démarre la temporisation de sortie

        include $viewFile; // Inclut le fichier de la vue

        $content = ob_get_clean(); // Lit le contenu courant du tampon de sortie puis l'efface

        include dirname(__DIR__) . '/views/base.php'; // On fabrique le "template" de notre site
    }
    /**
 * Génère un token CSRF pour protéger les formulaires
 * 
 * @return string Token CSRF
 */
protected function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si un token CSRF est valide
 * 
 * @param string $token Token à vérifier
 * @return bool True si le token est valide
 */
protected function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    
    // Optionnel: Régénérer le token après vérification pour encore plus de sécurité
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $valid;
}

/**
 * Vérifie que le token CSRF est valide ou affiche une erreur
 * 
 * @param string $token Token à vérifier
 * @param string $redirectUrl URL de redirection en cas d'échec
 */
protected function requireValidCSRFToken($token, $redirectUrl = 'index.php?controller=home&action=index') {
    if (!$this->validateCSRFToken($token)) {
        $_SESSION['error_message'] = "Erreur de sécurité: formulaire invalide ou expiré.";
        header('Location: ' . $redirectUrl);
        exit;
    }
}
}

?>
