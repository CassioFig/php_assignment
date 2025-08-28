<?php
namespace Repositories;

require_once __DIR__ . '/../repositories/RepositoryInterface.php';

use Repositories\RepositoryInterface;
use Classes\User;
use Exception;

class UserRepository implements RepositoryInterface
{
    private $db_conn;

    public function __construct($db_conn)
    {
        $this->db_conn = $db_conn;
    }

    public function create(object $entity): object
    {
        if (!$entity instanceof User) {
            throw new Exception("Entity must be an instance of User");
        }

        $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $stmt->bind_param("ssss",
            $entity->getName(),
            $entity->getEmail(),
            $entity->getPassword(),
            $entity->getRole()
        );
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to create user: " . $stmt->error);
        }

        $entity->setId($this->db_conn->insert_id);
        $stmt->close();

        return $entity;
    }

    public function findById(int|string $id): ?object
    {
        $sql = "SELECT * FROM Users WHERE id = ?";
        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $stmt->bind_param("i", $id);
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

        return new User($row['id'], $row['name'], $row['email'], $row['role'], $row['password']);
    }

    public function findBy(array $criteria): array
    {
        $columns = array_keys($criteria);
        $placeholders = implode(' AND ', array_map(fn($col) => "$col = ?", $columns));
        $sql = "SELECT * FROM Users WHERE $placeholders";

        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $types = str_repeat('s', count($criteria));
        $values = array_values($criteria);
        $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new User($row['id'], $row['name'], $row['email'], $row['role'], $row['password']);
        }

        $stmt->close();
        return $users;
    }

    public function update(object $entity): object
    {
        if (!$entity instanceof User) {
            throw new Exception("Entity must be an instance of User");
        }

        $sql = "UPDATE Users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $stmt->bind_param("ssssi",
            $entity->getName(),
            $entity->getEmail(),
            $entity->getPassword(),
            $entity->getRole(),
            $entity->getId()
        );

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to update user: " . $stmt->error);
        }

        $stmt->close();
        return $entity;
    }

    public function delete(object $entity): bool
    {
        if (!$entity instanceof User) {
            throw new Exception("Entity must be an instance of User");
        }

        $sql = "DELETE FROM Users WHERE id = ?";
        $stmt = $this->db_conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
        }

        $stmt->bind_param("i", $entity->getId());

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to delete user: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }
}
