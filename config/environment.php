<?php
class EnvironmentConfig {
    
    /**
     * Détecte l'environnement actuel
     */
    public static function getEnvironment() {
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        $isLocalhost = (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8080']) || 
                       strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                       strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);
        
        if ($isWindows && $isLocalhost) {
            return 'local_wamp';
        } elseif ($isLocalhost) {
            return 'local_linux';
        } else {
            return 'production';
        }
    }
    
    /**
     * Récupère la configuration selon l'environnement
     */
    public static function getConfig() {
        $env = self::getEnvironment();
        
        $configs = [
            'local_wamp' => [
                'upload_path' => $_SERVER['DOCUMENT_ROOT'] . '/sunnylink distant/SunnyLink/public/uploads/',
                'upload_url_base' => '/sunnylink distant/SunnyLink/public/uploads/',
                'max_file_size' => 10 * 1024 * 1024, // 10MB en local
                'debug' => true,
                'permissions' => 0777 // Plus permissif en local Windows
            ],
            'local_linux' => [
                'upload_path' => __DIR__ . '/../public/uploads/',
                'upload_url_base' => '/uploads/',
                'max_file_size' => 10 * 1024 * 1024, // 10MB en local
                'debug' => true,
                'permissions' => 0755
            ],
            'production' => [
                // Chemins possibles pour VPS
                'upload_path' => self::findProductionUploadPath(),
                'upload_url_base' => '/uploads/',
                'max_file_size' => 5 * 1024 * 1024, // 5MB en production
                'debug' => false,
                'permissions' => 0755
            ]
        ];
        
        return $configs[$env] ?? $configs['production'];
    }
    
    /**
     * Trouve le bon chemin d'upload en production
     */
    private static function findProductionUploadPath() {
        // Chemins possibles sur VPS - ORDRE DE PRIORITÉ
        $possiblePaths = [
            '/var/www/html/SunnyLink/public/uploads/',  // VOTRE STRUCTURE RÉELLE
            __DIR__ . '/../public/uploads/',            // Relatif depuis config
            dirname(dirname(__DIR__)) . '/public/uploads/', // Relatif depuis Models/config
            $_SERVER['DOCUMENT_ROOT'] . '/SunnyLink/public/uploads/', // DOCUMENT_ROOT + SunnyLink
            '/var/www/html/public/uploads/',            // Fallback que nous avons créé
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/',    // Autres fallbacks
        ];
        
        foreach ($possiblePaths as $path) {
            $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            $parentDir = dirname($normalizedPath);
            
            // Si le répertoire parent existe et est accessible en écriture
            if (is_dir($parentDir) && is_writable($parentDir)) {
                return $normalizedPath;
            }
            
            // Ou si le répertoire existe déjà
            if (is_dir($normalizedPath)) {
                return $normalizedPath;
            }
        }
        
        // Par défaut, utiliser le chemin de votre projet
        return '/var/www/html/SunnyLink/public/uploads/';
    }
    
    /**
     * Récupère le chemin d'upload adapté à l'environnement
     */
    public static function getUploadPath() {
        $config = self::getConfig();
        $path = $config['upload_path'];
        
        // Normaliser les séparateurs selon l'OS
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        
        // S'assurer que le chemin se termine par un séparateur
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Crée le répertoire d'upload s'il n'existe pas
     */
    public static function ensureUploadDirectory() {
        $uploadPath = self::getUploadPath();
        $config = self::getConfig();
        
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, $config['permissions'], true)) {
                throw new Exception("Impossible de créer le répertoire d'upload : " . $uploadPath);
            }
            
            // Créer le fichier .htaccess de sécurité
            self::createSecurityFiles($uploadPath);
            
            if ($config['debug']) {
                error_log("Répertoire d'upload créé : " . $uploadPath);
            }
        }
        
        // Vérifier les permissions
        if (!is_writable($uploadPath)) {
            // Essayer de corriger
            chmod($uploadPath, $config['permissions']);
            if (!is_writable($uploadPath)) {
                throw new Exception("Répertoire d'upload non accessible en écriture : " . $uploadPath);
            }
        }
        
        return $uploadPath;
    }
    
    /**
     * Crée les fichiers de sécurité dans le dossier uploads
     */
    private static function createSecurityFiles($uploadPath) {
        // Fichier .htaccess
        $htaccessPath = $uploadPath . '.htaccess';
        $htaccessContent = "# Sécurité SunnyLink - Upload Directory\n";
        $htaccessContent .= "Options -ExecCGI\n";
        $htaccessContent .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
        $htaccessContent .= "Options -Indexes\n";
        $htaccessContent .= "\n# Bloquer l'accès aux fichiers de script\n";
        $htaccessContent .= "<FilesMatch \"\\.(php|phtml|php3|php4|php5|phar|js|html|htm|sh|bat|exe)$\">\n";
        $htaccessContent .= "    Require all denied\n";
        $htaccessContent .= "</FilesMatch>\n";
        
        file_put_contents($htaccessPath, $htaccessContent);
        
        // Fichier index.html vide pour empêcher le listing
        $indexPath = $uploadPath . 'index.html';
        file_put_contents($indexPath, '<!-- SunnyLink Security -->');
    }
    
    /**
     * Log de debug si activé
     */
    public static function debugLog($message) {
        $config = self::getConfig();
        if ($config['debug']) {
            error_log("[SunnyLink Environment] " . $message);
        }
    }
}