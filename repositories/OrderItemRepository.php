<?php

declare(strict_types=1);

namespace Repositories;

require_once __DIR__ . '/../classes/OrderItem.php';

use Classes\OrderItem;
use mysqli;
use Exception;
use DateTime;

class OrderItemRepository
{
	private mysqli $db_conn;

	public function __construct(mysqli $db_conn)
	{
		$this->db_conn = $db_conn;
	}

	public function create(OrderItem $orderItem): OrderItem
	{
		$sql = "INSERT INTO OrderItem (order_id, product_id, amount) VALUES (?, ?, ?)";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("iii", $orderItem->getOrderId(), $orderItem->getProductId(), $orderItem->getAmount());

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to create order item: " . $stmt->error);
		}

		$orderItem_id = $this->db_conn->insert_id;
		$stmt->close();

		return $this->getById($orderItem_id);
	}

	public function getById(int $id): ?OrderItem
	{
		$sql = "SELECT id, order_id, product_id, amount, created_at, updated_at FROM OrderItem WHERE id = ?";
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

		return new OrderItem(
			(int)$row['id'],
			(int)$row['order_id'],
			(int)$row['product_id'],
			(int)$row['amount'],
			new DateTime($row['created_at']),
			new DateTime($row['updated_at'])
		);
	}

	public function getAll(): array
	{
		$sql = "SELECT id, order_id, product_id, amount, created_at, updated_at FROM OrderItem ORDER BY id ASC";
		$result = $this->db_conn->query($sql);

		if (!$result) {
			throw new Exception("Failed to execute query: " . $this->db_conn->error);
		}

		$orderItems = [];
		while ($row = $result->fetch_assoc()) {
			$orderItems[] = new OrderItem(
				(int)$row['id'],
				(int)$row['order_id'],
				(int)$row['product_id'],
				(int)$row['amount'],
				new DateTime($row['created_at']),
				new DateTime($row['updated_at'])
			);
		}

		return $orderItems;
	}

	public function getByOrderId(int $orderId): array
	{
		$sql = "SELECT id, order_id, product_id, amount, created_at, updated_at FROM OrderItem WHERE order_id = ? ORDER BY id ASC";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("i", $orderId);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to execute query: " . $stmt->error);
		}

		$result = $stmt->get_result();
		$orderItems = [];

		while ($row = $result->fetch_assoc()) {
			$orderItems[] = new OrderItem(
				(int)$row['id'],
				(int)$row['order_id'],
				(int)$row['product_id'],
				(int)$row['amount'],
				new DateTime($row['created_at']),
				new DateTime($row['updated_at'])
			);
		}

		$stmt->close();
		return $orderItems;
	}

	public function update(int $id, int $amount): ?OrderItem
	{
		if (!$this->getById($id)) {
			return null;
		}

		$sql = "UPDATE OrderItem SET amount = ? WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("ii", $amount, $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to update order item: " . $stmt->error);
		}

		$stmt->close();

		return $this->getById($id);
	}

	public function delete(int $id): bool
	{
		$sql = "DELETE FROM OrderItem WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("i", $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to delete order item: " . $stmt->error);
		}

		$affected_rows = $stmt->affected_rows;
		$stmt->close();

		return $affected_rows > 0;
	}
}
