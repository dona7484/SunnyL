<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : null;

if (!isset($_GET['controller'])) {
    require_once __DIR__ . '/../views/home/index.php';
    exit;
}

class Router {
      private $routes = [];
    private $middlewares = [];
    public function __construct() {
        $this->routes = [
            'apiGet' => [],
            'apiPost' => [],
            'apiPut' => [],
            'apiDelete' => []
        ];
        $this->middlewares = [
            'apiGet' => [],
            'apiPost' => [],
            'apiPut' => [],
            'apiDelete' => []
        ];
    }
    
    public function routes()
    {

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
    private function handleApiMethod($methodName, $path, $defaultHandler) {
    // Vérifier si la méthode existe
    if (!method_exists($this, $methodName)) {
        $defaultHandler();
        return;
    }
    
    // Variable pour indiquer si une route a été trouvée
    $routeFound = false;
    
    // Parcourir les routes enregistrées pour cette méthode
    if (isset($this->routes[$methodName])) {
        foreach ($this->routes[$methodName] as $route => $handler) {
            if ($this->matchRoute($route, $path)) {
                $routeFound = true;
                
                // Exécuter le middleware si présent
                if (isset($this->middlewares[$methodName][$route])) {
                    $middleware = $this->middlewares[$methodName][$route];
                    $middlewareResult = $middleware();
                    $handler($middlewareResult);
                } else {
                    // Exécuter le gestionnaire sans middleware
                    $handler();
                }
                break;
            }
        }
    }
    
    // Si aucune route n'a été trouvée, appeler le gestionnaire par défaut
    if (!$routeFound) {
        $defaultHandler();
    }
}
// Méthode pour enregistrer une route
private function registerRoute($method, $route, $handler, $middleware = null) {
    $this->routes[$method][$route] = $handler;
    if ($middleware) {
        $this->middlewares[$method][$route] = $middleware;
    }
}
    public function handleApiRequest() {
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Extraire le chemin de l'URI
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Log pour le débogage
    error_log("API Request: $method $path");
    
    // Définir un gestionnaire par défaut pour les routes inconnues
    $defaultHandler = function() {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    };
    
    // Appeler la méthode correspondante selon la méthode HTTP
    switch ($method) {
        case 'GET':
            $this->handleApiMethod('apiGet', $path, $defaultHandler);
            break;
        case 'POST':
            $this->handleApiMethod('apiPost', $path, $defaultHandler);
            break;
        case 'PUT':
            $this->handleApiMethod('apiPut', $path, $defaultHandler);
            break;
        case 'DELETE':
            $this->handleApiMethod('apiDelete', $path, $defaultHandler);
            break;
        default:
            $defaultHandler();
            break;
    }
}
    public function apiGet($route, $handler, $middleware = null) {
    $this->registerRoute('apiGet', $route, $handler, $middleware);
}

public function apiPost($route, $handler, $middleware = null) {
    $this->registerRoute('apiPost', $route, $handler, $middleware);
}

public function apiPut($route, $handler, $middleware = null) {
    $this->registerRoute('apiPut', $route, $handler, $middleware);
}

public function apiDelete($route, $handler, $middleware = null) {
    $this->registerRoute('apiDelete', $route, $handler, $middleware);
}

private function matchRoute($routePattern, $requestPath) {
    // Convertir le pattern de route en expression régulière
    $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '(?P<$1>[^/]+)', $routePattern);
    $pattern = '#^' . $pattern . '$#';
    
    // Tester si le chemin de la requête correspond au pattern
    if (preg_match($pattern, $requestPath, $matches)) {
        // Stocker les paramètres capturés
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $_GET[$key] = $value;
            }
        }
        return true;
    }
    
    return false;
}

} // <-- Add this closing brace to end the Router class

