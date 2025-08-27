<?php
namespace Controllers;

require_once __DIR__. '/../classes/User.php';

use User;

class UserController
{
    private $db_conn;
    
    public function __construct($db_conn)
    {
        $this->db_conn = $db_conn;
    }

    public function create()
    {
        // Validate _POST
        if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            http_response_code(400);
            echo 'Missing required fields';
            return;
        }

        $user = new User($_POST['name'], $_POST['email'], $_POST['password']);

        $insertPrep = $this->db_conn->prepare("INSERT INTO Users (name, email, password) VALUES (?, ?, ?)");
        $insertPrep->bind_param("sss",
            $user->getName(),
            $user->getEmail(),
            $user->getPassword()
        );
        $insertPrep->execute();
        $insertPrep->close();

        http_response_code(201);
        echo 'User created';
    }
}