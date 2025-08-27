<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/repositories/ProductRepository.php';

use Controllers\ProductController;
use Controllers\UserController;
use Repositories\ProductRepository;

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
    private $productController;
    private $productRepository;

    public function __construct($db_conn)
    {
        $this->userController = new UserController($db_conn);
        $this->productRepository = new ProductRepository($db_conn);
        $this->productController = new ProductController($this->productRepository);
    }

    public function route()
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $request_uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($request_uri, PHP_URL_PATH);

        $segments = explode('/', trim($path, '/'));
        $last_segment = end($segments);

        $route = $request_method . '/' . $last_segment;

        switch ($route) {
            case 'post/user':
                $this->userController->create();
                break;
            case 'post/product':
                $this->productController->create($_POST, $_FILES);
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}
