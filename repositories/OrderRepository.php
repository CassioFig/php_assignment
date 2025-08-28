<?php

declare(strict_types=1);

namespace Repositories;

require_once __DIR__ . '/../classes/Order.php';

use Classes\Order;
use mysqli;
use Exception;
use DateTime;

class OrderRepository
{
	private mysqli $db_conn;

	public function __construct(mysqli $db_conn)
	{
		$this->db_conn = $db_conn;
	}

	public function create(Order $order): Order
	{
		$sql = "INSERT INTO Orders (user_id, total) VALUES (?, ?)";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("id", $order->getUserId(), $order->getTotal());

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to create order: " . $stmt->error);
		}

		$order_id = $this->db_conn->insert_id;
		$stmt->close();

		return $this->getById($order_id);
	}

	public function getById(int $id): ?Order
	{
		$sql = "SELECT id, user_id, total, created_at, updated_at FROM Orders WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("i", $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to execute query: " . $stmt->error);
		}

		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		if (!$row) {
			return null;
		}

		return new Order(
			(int)$row['id'],
			(int)$row['user_id'],
			(float)$row['total'],
			new DateTime($row['created_at']),
			new DateTime($row['updated_at'])
		);
	}

	public function getAll(): array
	{
		$sql = "SELECT id, user_id, total, created_at, updated_at FROM Orders ORDER BY id ASC";
		$result = $this->db_conn->query($sql);

		if (!$result) {
			throw new Exception("Failed to execute query: " . $this->db_conn->error);
		}

		$orders = [];
		while ($row = $result->fetch_assoc()) {
			$orders[] = new Order(
				(int)$row['id'],
				(int)$row['user_id'],
				(float)$row['total'],
				new DateTime($row['created_at']),
				new DateTime($row['updated_at'])
			);
		}

		return $orders;
	}

	public function update(int $id, float $total): ?Order
	{
		if (!$this->getById($id)) {
			return null;
		}

		$sql = "UPDATE Orders SET total = ? WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("di", $total, $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to update order: " . $stmt->error);
		}

		$stmt->close();

		return $this->getById($id);
	}

	public function delete(int $id): bool
	{
		$sql = "DELETE FROM Orders WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("i", $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to delete order: " . $stmt->error);
		}

		$affected_rows = $stmt->affected_rows;
		$stmt->close();

		return $affected_rows > 0;
	}

}
