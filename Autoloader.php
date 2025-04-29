<?php
class Autoloader {
    public static function register() {
        spl_autoload_register(function ($class) {
            // Chemins possibles pour les classes
            $paths = [
                __DIR__ . '/models/' . $class . '.php',
                __DIR__ . '/Controllers/' . $class . '.php',
                __DIR__ . '/Entities/' . $class . '.php',
                __DIR__ . '/core/' . $class . '.php'
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    return;
                }
            }
            
            // Log pour le débogage
            error_log("Classe non trouvée: $class");
        });
    }
}
