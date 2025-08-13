<?php

use Controllers\UserController;

try {
    $router = new Router();
    $router->route();
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo json_encode(array('errors' => array($e->getMessage())));
    return;
}

class Router
{
    private $userController;

    public function __construct()
    {
        $this->userController = new UserController();
    }

    public function route()
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $request_uri = $_SERVER['REQUEST_URI'];
        $route = $request_method . "::" . $request_uri;

        switch($route) {
            case 'post::user':
                $this->userController->create();
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}

