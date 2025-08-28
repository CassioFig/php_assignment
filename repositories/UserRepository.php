<?php
namespace Repositories;

require_once __DIR__. '/../classes/User.php';

use Classes\User;
use Exception;

class UserRepository
{
    private $db_conn;

    public function __construct($db_conn)
    {
        $this->db_conn = $db_conn;
    }

    public function create(User $user)
    {
        $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)";

		$stmt = $this->db_conn->prepare($sql);
		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

        $stmt->bind_param("ssss",
            $user->getName(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getRole()
        );
		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to create user: " . $stmt->error);
		}

		$user->setId($this->db_conn->insert_id);
		$stmt->close();

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM Users WHERE email = ?";

        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $row = $result->fetch_assoc();
        $stmt->close();
        
        return new User($row['id'], $row['name'], $row['email'], $row['password'], $row['role']);
    }
}   