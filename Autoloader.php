<?php
class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    private static function autoload($class) {
        // Convertir les namespaces en chemins
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        // Liste des dossiers à vérifier
        $directories = [
            __DIR__ . DIRECTORY_SEPARATOR . 'controllers',
            __DIR__ . DIRECTORY_SEPARATOR . 'core',
            __DIR__ . DIRECTORY_SEPARATOR . 'models',
            __DIR__ . DIRECTORY_SEPARATOR . 'entities',
        ];

        foreach ($directories as $directory) {
            $file = $directory . DIRECTORY_SEPARATOR . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            } else {
                // Debugging: Afficher les chemins vérifiés
                echo "Autoloader: Vérification du chemin : $file<br>";
            }
        }

        echo "Autoloader: Impossible de charger la classe $class.";
        die();
    }
}
