<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/enums/UserRole.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/repositories/ProductRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/repositories/OrderItemRepository.php';
require_once __DIR__ . '/controllers/OrderItemController.php';

use Controllers\UserController;
use Enums\UserRole;
use Controllers\OrderController;
use Controllers\OrderItemController;
use Controllers\ProductController;
use Repositories\OrderItemRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use Repositories\UserRepository;

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
    error_log("[ERROR] " . $e->getMessage());
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
    private $productController;
    private $productRepository;
    private $orderController;
    private $orderRepository;
    private $orderItemController;
    private $orderItemRepository;

    public function __construct($db_conn)
    {
        $this->userRespository = new UserRepository($db_conn);
        $this->userController = new UserController($this->userRespository);
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

        // Authorization
        $currentUser = $this->userController->getCurrentUser();
        // must be logged in to access these routes
        if (in_array($route, ['get/user', 'patch/user', 'delete/user', 'post/order', 'post/order-item', 'get/order-item', 'put/order-item', 'delete/order-item']) && $currentUser === null) {
            throw new Exception("Unauthorized", 401);
        }
        // must be admin to access these routes
        elseif (in_array($route, ['post/admin', 'post/product', 'put/product', 'delete/product', 'get/order', 'put/order', 'delete/order']) && ($currentUser === null || $currentUser->getRole() !== UserRole::ADMIN)) {
            throw new Exception("Forbidden", 403);
        }

        switch ($route) {
            case 'get/user':
                $this->userController->index();
                break;
            case 'post/user':
                $this->userController->create();
                break;
            case 'patch/user':
                $this->userController->update();
                break;
            case 'delete/user':
                $this->userController->delete();
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
            case 'post/product':
                $this->productController->create($_POST, $_FILES);
                break;
            case 'get/product':
                $this->productController->getAll();
                break;
            case 'put/product':
                $input_data = file_get_contents('php://input');
                $data = $this->parseMultipartData($input_data);
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $this->productController->update($_GET['id'], $data, $_FILES);
                } else {
                    http_response_code(400);
                }
                break;
            case 'delete/product':
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $this->productController->delete($_GET['id']);
                } else {
                    http_response_code(400);
                }
                break;
            case 'post/order':
                $data = json_decode(file_get_contents('php://input'), true);
                $this->orderController->create($data);
                break;
            case 'get/order':
                $this->orderController->getAll();
                break;
            case 'put/order':
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $this->orderController->update($_GET['id'], $data);
                } else {
                    http_response_code(400);
                }
                break;
            case 'delete/order':
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $this->orderController->delete($_GET['id']);
                } else {
                    http_response_code(400);
                }
                break;
            case 'post/order-item':
                $data = json_decode(file_get_contents('php://input'), true);
                $this->orderItemController->create($data);
                break;
            case 'get/order-item':
                $this->orderItemController->getAll();
                break;
            case 'put/order-item':
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $this->orderItemController->update($_GET['id'], $data);
                } else {
                    http_response_code(400);
                }
                break;
            case 'delete/order-item':
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $this->orderItemController->delete($_GET['id']);
                } else {
                    http_response_code(400);
                }
                break;
            default:
                http_response_code(404);
                break;
        }
    }

    private function parseMultipartData($input)
    {
        $data = array();
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        $blocks = preg_split("/-+$boundary/", $input);
        array_pop($blocks); // Remove o último elemento vazio

        foreach ($blocks as $block) {
            if (empty($block)) continue;
            if (strpos($block, "\r\n\r\n") !== false) {
                list($headers, $body) = explode("\r\n\r\n", $block, 2);
            } else {
                continue;
            }
            if (preg_match('/name="([^"]*)"/', $headers, $matches)) {
                $name = $matches[1];
                // Limpar quebras de linha do valor
                $value = trim($body, "\r\n");
                $data[$name] = $value;
            }
        }
        return $data;
    }
}
