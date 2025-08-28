<?php
namespace Controllers;

require_once __DIR__. '/../classes/User.php';
require_once __DIR__. '/../repositories/UserRepository.php';

use Exception;
use Classes\User;
use Repositories\UserRepository;

class UserController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create($role = 'User')
    {
        // Validate _POST
        if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            throw new Exception("Missing required fields", 400);
        }

        $user = new User(null, $_POST['name'], $_POST['email'], $role);
        $user->setPassword($_POST['password']);

        $newUser = $this->userRepository->create($user);

        http_response_code(201);
        echo json_encode(['message' => 'User created.', 'user' => $newUser]);
    }

    public function login()
    {
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            throw new Exception("Missing required fields", 400);
        }

        if(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
            $email = $_POST["email"];
        }else{
            throw new Exception("Invalid email format.", 400);
        }
        $user = $this->userRepository->findByEmail($email);
        if(!$user){
            throw new Exception("Email/Password not valid.", 401);
        }

        if(!password_verify(hash_hmac("sha256", $_POST['password'], PEPPER), $user->getPassword())){
            throw new Exception("Email/Password not valid.", 401);
        }

        session_start();
        $_SESSION['user'] = $user;
        $_SESSION['LAST_ACTIVITY'] = time();

        echo json_encode(['message' => 'Login successful.', 'user' => $user]);
    }

    public function logout()
    {
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        if(session_status() === PHP_SESSION_ACTIVE){
            session_unset();
            session_destroy();
            echo "logged out";
        } else {
            throw new Exception("Login required.", 401);
        }
    }
}