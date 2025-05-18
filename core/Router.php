<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : null;

if (!isset($_GET['controller'])) {
    require_once __DIR__ . '/../views/home/index.php';
    exit;
}

class Router {
    public function routes()
    {
        // 📞 Appels vidéo
        if (isset($_GET['controller']) && $_GET['controller'] === 'call') {
            $action = $_GET['action'] ?? 'start';
            $controllerName = 'CallController';
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';
        
            if (file_exists($controllerPath)) {
                require_once $controllerPath;
            }
        
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    exit;
                }
            }
        
            $_SESSION['error_message'] = "Action d'appel non trouvée";
            header('Location: index.php?controller=home&action=error');
            exit;
        }
        if (preg_match('/\.php$/', $_SERVER['REQUEST_URI'])) {
    // Laisser le serveur traiter directement les fichiers PHP
    return;
}
        // 🔔 Notifications
        if (isset($_GET['controller']) && $_GET['controller'] === 'notification') {
            $action = $_GET['action'] ?? 'get';
            $controllerName = 'NotificationController';
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                require_once $controllerPath;
            }

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    exit;
                }
            }

            $_SESSION['error_message'] = "Page non trouvée";
            header('Location: index.php?controller=home&action=error');
            exit;
        }

        // 🚨 Alertes automatiques
        if (isset($_GET['controller']) && $_GET['controller'] === 'alert') {
            $action = $_GET['action'] ?? 'check';
            $controllerName = 'AlertController';
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                require_once $controllerPath;
            }

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    exit;
                }
            }

            http_response_code(404);
            echo json_encode(['error' => 'AlertController ou action introuvable']);
            exit;
        }

        // 🌐 Routage MVC classique
        $controller = isset($_GET['controller']) ? ucfirst($_GET['controller']) : 'Home';
        $controllerName = $controller . 'Controller';
        $action = $_GET['action'] ?? 'index';

        $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
        } else {
            http_response_code(404);
            echo "Erreur 404 : Contrôleur '$controllerName' introuvable.";
            exit;
        }

        if (class_exists($controllerName)) {
            $controllerInstance = new $controllerName();
            if (method_exists($controllerInstance, $action)) {
                $params = array_diff_key($_GET, array_flip(['controller', 'action']));
                call_user_func_array([$controllerInstance, $action], array_values($params));
            } else {
                http_response_code(404);
                echo "Erreur 404 : Action '$action' introuvable dans $controllerName.";
            }
        } else {
            http_response_code(404);
            echo "Erreur 404 : Contrôleur '$controllerName' introuvable.";
        }
    }
}