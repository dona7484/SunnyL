<?php
class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    private static function autoload($class) {
        // Remplacer les namespaces par des chemins
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    
        $directories = [
            __DIR__ . '/Controllers',
            __DIR__ . '/core',
            __DIR__ . '/models',
            __DIR__ . '/entities',
            __DIR__ . '/config',
        ];

        foreach ($directories as $directory) {
            $file = $directory . DIRECTORY_SEPARATOR . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // En cas d'échec silencieux (désactive le die pour éviter de bloquer tout)
        error_log("Autoloader : Classe non trouvée : $class");
    }
}
