<?php
class Router
{
    public function routes()
    {
        // Gestion spéciale pour les notifications (API JSON)
        if (isset($_GET['controller']) && $_GET['controller'] === 'notification') {
            $action = $_GET['action'] ?? 'get';
        
            $controllerName = 'NotificationController';
            
            // 🔧 Ajout essentiel :
            $notifPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';
            if (file_exists($notifPath)) {
                require_once $notifPath;
            
            }
        
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
        
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    exit;
                }
            }
        
            http_response_code(404);
            echo json_encode(['error' => 'NotificationController ou action introuvable']);
            exit;
        }
        

        // Routage MVC classique
        $controller = isset($_GET['controller']) ? ucfirst($_GET['controller']) : 'Home';
        $controllerName = $controller . 'Controller';
        $action = $_GET['action'] ?? 'index';

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
