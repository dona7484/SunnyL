<?php

// Définir SKIP_ROUTER avant d'inclure ce fichier si vous ne voulez pas qu'il s'exécute (ex: pour cli_test_jwt.php)
if (defined('SKIP_ROUTER') && SKIP_ROUTER) {
    return;
}

class Router {
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    private $middlewares = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    // Routes web classiques (votre logique existante)
    public function routes() {
        // 📞 Appels vidéo
        if (isset($_GET['controller']) && $_GET['controller'] === 'call') { // [cite: 202]
            $action = $_GET['action'] ?? 'start'; // [cite: 202]
            $controllerName = 'CallController'; // [cite: 202]
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php'; // [cite: 202]
        
            if (file_exists($controllerPath)) { // [cite: 202]
                require_once $controllerPath; // [cite: 202]
            }
        
            if (class_exists($controllerName)) { // [cite: 203]
                $controller = new $controllerName(); // [cite: 203]
                if (method_exists($controller, $action)) { // [cite: 203]
                    $controller->$action(); // [cite: 203]
                    exit;
                }
            }
        
            $_SESSION['error_message'] = "Action d'appel non trouvée"; // [cite: 203]
            header('Location: index.php?controller=home&action=error'); // [cite: 203]
            exit;
        }

        // 🔔 Notifications
        if (isset($_GET['controller']) && $_GET['controller'] === 'notification') { // [cite: 204]
            $action = $_GET['action'] ?? 'get'; // [cite: 204]
            $controllerName = 'NotificationController'; // [cite: 204]
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php'; // [cite: 204]

            if (file_exists($controllerPath)) { // [cite: 204]
                require_once $controllerPath; // [cite: 204]
            }

            if (class_exists($controllerName)) { // [cite: 204]
                $controller = new $controllerName(); // [cite: 204]
                if (method_exists($controller, $action)) { // [cite: 204]
                    $controller->$action(); // [cite: 204]
                    exit;
                }
            }

            $_SESSION['error_message'] = "Page non trouvée"; // [cite: 205]
            header('Location: index.php?controller=home&action=error'); // [cite: 205]
            exit;
        }

        // 🚨 Alertes automatiques
        if (isset($_GET['controller']) && $_GET['controller'] === 'alert') { // [cite: 205]
            $action = $_GET['action'] ?? 'check'; // [cite: 205]
            $controllerName = 'AlertController'; // [cite: 205]
            $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php'; // [cite: 205]

            if (file_exists($controllerPath)) { // [cite: 205]
                require_once $controllerPath; // [cite: 205]
            }

            if (class_exists($controllerName)) { // [cite: 205]
                $controller = new $controllerName(); // [cite: 205]
                if (method_exists($controller, $action)) { // [cite: 205]
                    $controller->$action(); // [cite: 206]
                    exit;
                }
            }

            http_response_code(404); // [cite: 206]
            echo json_encode(['error' => 'AlertController ou action introuvable']); // [cite: 206]
            exit;
        }
        
        // 🌐 Routage MVC classique pour le web
        $controllerNameSegment = isset($_GET['controller']) ? ucfirst($_GET['controller']) : 'Home'; // [cite: 206]
        $controllerName = $controllerNameSegment . 'Controller'; // [cite: 206]
        $action = $_GET['action'] ?? 'index'; // [cite: 206]

        $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php'; // [cite: 206]
        if (file_exists($controllerPath)) { // [cite: 206]
            require_once $controllerPath; // [cite: 206]
        } else {
            http_response_code(404); // [cite: 206]
            // Tenter de charger ErrorController pour une page 404 plus propre si le contrôleur n'est pas trouvé
            $errorControllerPath = __DIR__ . '/../Controllers/ErrorController.php';
            if (file_exists($errorControllerPath)) {
                require_once $errorControllerPath;
                if(class_exists('ErrorController')) {
                    $errorCtrl = new ErrorController();
                    $errorCtrl->notFound("Contrôleur '$controllerName' introuvable.");
                    exit;
                }
            }
            echo "Erreur 404 : Contrôleur '$controllerName' introuvable."; // [cite: 206]
            exit;
        }

        if (class_exists($controllerName)) { // [cite: 206]
            $controllerInstance = new $controllerName(); // [cite: 207]
            if (method_exists($controllerInstance, $action)) { // [cite: 207]
                // Préparer les paramètres à passer à l'action
                // Exclure 'controller' et 'action' des paramètres GET
                $params = [];
                foreach ($_GET as $key => $value) {
                    if ($key !== 'controller' && $key !== 'action') {
                        $params[] = $value;
                    }
                }
                call_user_func_array([$controllerInstance, $action], $params); // [cite: 207]
            } else {
                http_response_code(404); // [cite: 207]
                $errorControllerPath = __DIR__ . '/../Controllers/ErrorController.php';
                if (file_exists($errorControllerPath)) {
                    require_once $errorControllerPath;
                     if(class_exists('ErrorController')) {
                        $errorCtrl = new ErrorController();
                        $errorCtrl->notFound("Action '$action' introuvable dans $controllerName.");
                        exit;
                    }
                }
                echo "Erreur 404 : Action '$action' introuvable dans $controllerName."; // [cite: 207]
            }
        } else {
            http_response_code(404); // [cite: 207]
             $errorControllerPath = __DIR__ . '/../Controllers/ErrorController.php';
            if (file_exists($errorControllerPath)) {
                require_once $errorControllerPath;
                if(class_exists('ErrorController')) {
                    $errorCtrl = new ErrorController();
                    $errorCtrl->notFound("Contrôleur '$controllerName' (classe) introuvable.");
                    exit;
                }
            }
            echo "Erreur 404 : Contrôleur '$controllerName' (classe) introuvable."; // [cite: 207]
        }
    }

    // Méthodes pour enregistrer les routes API
    public function apiGet($route, $handler, $middleware = null) {
        $this->registerRoute('GET', $route, $handler, $middleware);
    }

    public function apiPost($route, $handler, $middleware = null) {
        $this->registerRoute('POST', $route, $handler, $middleware);
    }

    public function apiPut($route, $handler, $middleware = null) {
        $this->registerRoute('PUT', $route, $handler, $middleware);
    }

    public function apiDelete($route, $handler, $middleware = null) {
        $this->registerRoute('DELETE', $route, $handler, $middleware);
    }

    private function registerRoute($method, $route, $handler, $middleware = null) {
        $this->routes[$method][$route] = $handler;
        if ($middleware) {
            $this->middlewares[$method][$route] = $middleware;
        }
    }

    private function matchRoute($routePattern, $requestPath, &$params = []) {
        $pattern = preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function ($match) {
            return '(?P<' . $match[1] . '>[^/]+)';
        }, $routePattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }
        return false;
    }

    public function handleApiRequest($requestPath, $requestMethod) {
        header('Content-Type: application/json'); // Toutes les réponses API seront en JSON
        $methodRoutes = $this->routes[$requestMethod] ?? [];
        $params = [];

        foreach ($methodRoutes as $route => $handler) {
            if ($this->matchRoute($route, $requestPath, $params)) {
                $middleware = $this->middlewares[$requestMethod][$route] ?? null;
                $userData = null;

                if ($middleware) {
                    // Exécute le middleware. Il devrait retourner les données utilisateur ou gérer l'erreur.
                    $userData = call_user_func($middleware); // Le middleware gère exit() en cas d'erreur
                    if ($userData === false) { // Si le middleware retourne false explicitement, ne pas continuer.
                        return; // Le middleware a déjà géré la réponse d'erreur.
                    }
                }
                
                // Préparer les arguments pour le handler
                $handlerArgs = [];
                if ($userData !== null) {
                    $handlerArgs[] = $userData; // Passer les données utilisateur comme premier argument
                }
                // Ajouter les paramètres de la route
                foreach ($params as $paramValue) {
                    $handlerArgs[] = $paramValue;
                }

                // Appeler le handler avec les arguments préparés
                if (is_array($handler) && class_exists($handler[0]) && method_exists($handler[0], $handler[1])) {
                    $controllerInstance = new $handler[0]();
                    call_user_func_array([$controllerInstance, $handler[1]], $handlerArgs);
                } elseif (is_callable($handler)) {
                     // Si userData est attendu par une fonction anonyme, il faut le passer
                    if ($middleware && $userData !== null) {
                        call_user_func_array($handler, array_merge([$userData], array_values($params)));
                    } else {
                        call_user_func_array($handler, array_values($params));
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Invalid API handler configuration for route: ' . $route]);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'API Endpoint not found: ' . $requestMethod . ' ' . $requestPath]);
    }
}