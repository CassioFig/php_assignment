<?php
namespace Controllers;

require_once __DIR__. '/../classes/User.php';
require_once __DIR__. '/../repositories/UserRepository.php';
require_once __DIR__. '/../enums/UserRole.php';

use Exception;
use Classes\User;
use Repositories\UserRepository;
use Enums\UserRole;

class UserController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        try {
            if (!isset($_GET['id'])) {
                error_log("[INFO] Fetch user failed: Missing user ID");
                throw new Exception("Missing user ID", 400);
            }
            $id = (int)$_GET['id'];

            if ($this->getCurrentUser()->getRole() != UserRole::ADMIN && $id != $this->getCurrentUser()->getId()) {
                error_log("[INFO] Fetch user failed: Unauthorized attempt to fetch user ID ". $id);
                throw new Exception("Unauthorized", 401);
            }

            $user = $this->userRepository->findById($id);
            if ($user === null) {
                error_log("[INFO] Fetch user failed: User not found with ID ". $id);
                throw new Exception("User not found", 404);
            }
            error_log("[INFO] User fetched: ". $user->getEmail(). " (ID: ". $user->getId() .")");
            echo json_encode(['user' => $user]);
        } catch (Exception $e) {
            error_log("[ERROR] Fetch user error: ". $e->getMessage());
            throw $e;
        }
    }

    public function create($role = UserRole::USER)
    {
        try {
            // Validate _POST
            if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password'])) {
                error_log("[INFO] User creation failed: Missing required fields");
                throw new Exception("Missing required fields", 400);
            }

            $user = new User(null, $_POST['name'], $_POST['email'], $role);
            $user->setPassword($_POST['password']);

            $newUser = $this->userRepository->create($user);

            error_log("[INFO] User created: ". $_POST['email']. " with role ". $role);
            http_response_code(201);
            echo json_encode(['message' => 'User created.', 'user' => $newUser]);
        } catch (Exception $e) {
            error_log("[ERROR] User creation error: ". $e->getMessage());
            throw $e;
        }
    }

    public function update()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        try {
            if (!isset($data['id'])) {
                error_log("[INFO] User update failed: Missing user ID");
                throw new Exception("Missing user ID", 400);
            }

            $id = (int)$data['id'];

            if ($this->getCurrentUser()->getRole() != UserRole::ADMIN && $id != $this->getCurrentUser()->getId()) {
                error_log("[INFO] User update failed: Unauthorized attempt to update user ID ". $id);
                throw new Exception("Unauthorized", 401);
            }

            $user = $this->userRepository->findById($id);
            if ($user === null) {
                error_log("[INFO] User update failed: User not found with ID ". $id);
                throw new Exception("User not found", 404);
            }

            if (isset($data['name'])) {
                $user->setName($data['name']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['password'])) {
                $user->setPassword($data['password']);
            }
            $updatedUser = $this->userRepository->update($user);
            
            error_log("[INFO] User updated: ". $user->getEmail(). " (ID: ". $user->getId() .")");
            echo json_encode(['message' => 'User updated.', 'user' => $updatedUser]);
        } catch (Exception $e) {
            error_log("[ERROR] User update error: ". $e->getMessage());
            throw $e;
        }
    }

    public function delete()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        try {
            if (!isset($data['id'])) {
                error_log("[INFO] User update failed: Missing user ID");
                throw new Exception("Missing user ID", 400);
            }

            $id = (int)$data['id'];

            if ($this->getCurrentUser()->getRole() != UserRole::ADMIN && $id != $this->getCurrentUser()->getId()) {
                error_log("[INFO] User deletion failed: Unauthorized attempt to delete user ID ". $id);
                throw new Exception("Unauthorized", 401);
            }
            $user = $this->userRepository->findById($id);
            if ($user === null) {
                error_log("[INFO] User deletion failed: User not found with ID ". $id);
                throw new Exception("User not found", 404);
            }
            $this->userRepository->delete($user);
            error_log("[INFO] User deleted: ". $user->getEmail(). " (ID: ". $user->getId() .")");
            echo json_encode(['message' => 'User deleted.']);
        } catch (Exception $e) {
            error_log("[ERROR] User deletion error: ". $e->getMessage());
            throw $e;
        }
    }

    public function login()
    {
        try {
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                error_log("[INFO] Login failed: Missing required fields");
                throw new Exception("Missing required fields", 400);
            }

            if(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
                $email = $_POST["email"];
            }else{
                error_log("[INFO] Login failed: Invalid email format (". $_POST["email"] . ")");
                throw new Exception("Invalid email format.", 400);
            }
            $users = $this->userRepository->findBy(['email' => $email]);
            if(count($users) == 0){
                error_log("[INFO] Login failed: Email not found (". $email . ")");
                throw new Exception("Email/Password not valid.", 401);
            }

            $user = $users[0];

            if(!password_verify(hash_hmac("sha256", $_POST['password'], PEPPER), $user->getPassword())){
                error_log("[INFO] Login failed: Invalid password for email (". $email . ")");
                throw new Exception("Email/Password not valid.", 401);
            }

            session_start();
            $_SESSION['user'] = $user;
            $_SESSION['LAST_ACTIVITY'] = time();

            error_log("[INFO] Login successful: ". $email);
            echo json_encode(['message' => 'Login successful.', 'user' => $user]);
        } catch (Exception $e) {
            error_log("[ERROR] Login error: ". $e->getMessage());
            throw $e;
        }
    }

    public function logout()
    {
        try {
            if(session_status() === PHP_SESSION_NONE){
                session_start();
            }

            if(session_status() === PHP_SESSION_ACTIVE){
                error_log("[INFO] User logged out: ". (isset($_SESSION['user']) ? $_SESSION['user']->getEmail() : 'unknown'));
                session_unset();
                session_destroy();
                echo "logged out";
            } else {
                error_log("[INFO] Logout failed: No active session");
                throw new Exception("Login required.", 401);
            }
        } catch (Exception $e) {
            error_log("[ERROR] Logout error: ". $e->getMessage());
            throw $e;
        }
    }

    public function getCurrentUser(): ?User
    {
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        if(!isset($_SESSION['user']) || !isset($_SESSION['LAST_ACTIVITY'])){
            return null;
        }

        if(time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT){
            session_unset();
            session_destroy();
            return null;
        }

        $_SESSION['LAST_ACTIVITY'] = time();

        return $_SESSION['user'];
    }
}