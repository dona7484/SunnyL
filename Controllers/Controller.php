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
}
?>
