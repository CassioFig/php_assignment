<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/repositories/ProductRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/repositories/OrderItemRepository.php';
require_once __DIR__ . '/controllers/OrderItemController.php';

use Controllers\OrderController;
use Controllers\OrderItemController;
use Controllers\ProductController;
use Controllers\UserController;
use Repositories\OrderItemRepository;
use Repositories\OrderRepository;
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
    private $orderController;
    private $orderRepository;
    private $orderItemController;
    private $orderItemRepository;

    public function __construct($db_conn)
    {
        $this->userController = new UserController($db_conn);
        $this->productRepository = new ProductRepository($db_conn);
        $this->productController = new ProductController($this->productRepository);
        $this->orderRepository = new OrderRepository($db_conn);
        $this->orderController = new OrderController($this->orderRepository);
        $this->orderItemRepository = new OrderItemRepository($db_conn);
        $this->orderItemController = new OrderItemController($this->orderItemRepository);
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
            case 'post/order':
                $data = json_decode(file_get_contents('php://input'), true);
                $this->orderController->create($data);
                break;
            case 'get/order':
                $this->orderController->getAll();
                break;
            case 'post/order-item':
                $data = json_decode(file_get_contents('php://input'), true);
                $this->orderItemController->create($data);
                break;
            case 'get/order-item':
                $this->orderItemController->getAll();
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}
