<?php
// namespace App\Core;

class Router
{
    public function routes()
    {
        $controller = isset($_GET['controller']) ? ucfirst($_GET['controller']) : 'Home';
        $controller = $controller . 'Controller';

        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        if (class_exists($controller)) {
            $controller = new $controller();

            if (method_exists($controller, $action)) {
                $params = array_slice($_GET, 2);
                call_user_func_array([$controller, $action], $params);
            } else {
                http_response_code(404);
                echo "Erreur 404 : L'action demandée n'existe pas.";
            }
        } else {
            http_response_code(404);
            echo "Erreur 404 : Le contrôleur demandé n'existe pas.";
        }
    }
}
?>
