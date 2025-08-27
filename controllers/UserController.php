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

    public function create()
    {
        // Validate _POST
        if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            throw new Exception("Missing required fields", 400);
        }

        $user = new User(null, $_POST['name'], $_POST['email']);
        $user->setPassword($_POST['password']);

        $newUser = $this->userRepository->create($user);

        http_response_code(201);
        echo json_encode(['message' => 'User created.', 'user' => $newUser]);
    }
}