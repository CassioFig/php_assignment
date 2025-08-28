<?php
require_once __DIR__. '/config.php';
require_once __DIR__. '/repositories/UserRepository.php';
require_once __DIR__. '/Controllers/UserController.php';
require_once __DIR__. '/enums/UserRole.php';

use Repositories\UserRepository;
use Controllers\UserController;
use Enums\UserRole;

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
    error_log("[ERROR] ". $e->getMessage());
    http_response_code($e->getCode() ?: 500);
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
    private $userRespository;

    public function __construct($db_conn)
    {
        $this->userRespository = new UserRepository($db_conn);
        $this->userController = new UserController($this->userRespository);
    }

    public function route()
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $request_uri = $_SERVER['REQUEST_URI'];
        $route = $request_method. $request_uri;

        // Authorization
        $currentUser = $this->userController->getCurrentUser();
        // must be logged in to access these routes
        if (in_array($route, ['post/order']) && $currentUser === null) {
            throw new Exception("Unauthorized", 401);
        }
        // must be admin to access these routes
        elseif (in_array($route, ['post/admin']) && ($currentUser === null || $currentUser->getRole() !== UserRole::ADMIN)) {
            throw new Exception("Forbidden", 403);
        }

        switch($route) {
            case 'post/user':
                $this->userController->create();
                break;
            case 'post/admin':
                $this->userController->create(UserRole::ADMIN);
                break;
            case 'post/login':
                $this->userController->login();
                break;
            case 'get/logout':
                $this->userController->logout();
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}

