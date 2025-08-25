<?php
require_once __DIR__. '/config.php';
require_once __DIR__. '/Controllers/UserController.php';

use Controllers\UserController;

try {
    // Create db connection
    $db_conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
    if ($db_conn->connect_error) {
        throw new Exception("Connection failed: " . $db_conn->connect_error, 500);
    }

    // Routing
    $router = new Router($db_conn);
    $router->route();
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo json_encode(array('errors' => array($e->getMessage())));
    return;
} finally {
    // Close db connection
    if (isset($db_conn)) {
        $db_conn->close();
    }
}

class Router
{
    private $userController;

    public function __construct($db_conn)
    {
        $this->userController = new UserController($db_conn);
    }

    public function route()
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $request_uri = $_SERVER['REQUEST_URI'];
        $route = $request_method. $request_uri;

        switch($route) {
            case 'post/user':
                $this->userController->create();
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}

