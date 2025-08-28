<?php

declare(strict_types=1);

namespace Repositories;

require_once __DIR__ . '/../classes/Product.php';

use Classes\Product;
use mysqli;
use Exception;

class ProductRepository
{
	private mysqli $db_conn;

	public function __construct(mysqli $db_conn)
	{
		$this->db_conn = $db_conn;
	}

	public function create(Product $product): Product
	{
		$sql = "INSERT INTO Products (name, image, price, stock) VALUES (?, ?, ?, ?)";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("ssdi", $product->getName(), $product->getImage(), $product->getPrice(), $product->getStock());

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to create product: " . $stmt->error);
		}

		$product_id = $this->db_conn->insert_id;
		$stmt->close();

		return new Product($product_id, $product->getName(), $product->getImage(), $product->getPrice(), $product->getStock());
	}

	public function getById(int $id): ?Product
	{
		$sql = "SELECT id, name, image, price, stock FROM Products WHERE id = ?";
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

		return new Product(
			(int)$row['id'],
			$row['name'],
			$row['image'],
			(float)$row['price'],
			(int)$row['stock']
		);
	}

	public function getAll(): array
	{
		$sql = "SELECT id, name, image, price, stock FROM Products ORDER BY id ASC";
		$result = $this->db_conn->query($sql);

		if (!$result) {
			throw new Exception("Failed to execute query: " . $this->db_conn->error);
		}

		$products = [];
		while ($row = $result->fetch_assoc()) {
			$products[] = new Product(
				(int)$row['id'],
				$row['name'],
				$row['image'],
				(float)$row['price'],
				(int)$row['stock']
			);
		}

		return $products;
	}

	public function getByName(string $name): array
	{
		$sql = "SELECT id, name, image, price, stock FROM Products WHERE name LIKE ? ORDER BY name ASC";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$search_term = "%{$name}%";
		$stmt->bind_param("s", $search_term);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to execute query: " . $stmt->error);
		}

		$result = $stmt->get_result();
		$products = [];

		while ($row = $result->fetch_assoc()) {
			$products[] = new Product(
				(int)$row['id'],
				$row['name'],
				$row['image'],
				(float)$row['price'],
				(int)$row['stock']
			);
		}

		$stmt->close();
		return $products;
	}

	public function update(int $id, string $name, string $image, float $price, int $stock): ?Product
	{
		if (!$this->getById($id)) {
			return null;
		}

		$sql = "UPDATE Products SET name = ?, image = ?, price = ?, stock = ? WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("ssdii", $name, $image, $price, $stock, $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to update product: " . $stmt->error);
		}

		$stmt->close();

		return new Product($id, $name, $image, $price, $stock);
	}


	public function delete(int $id): bool
	{
		$sql = "DELETE FROM Products WHERE id = ?";
		$stmt = $this->db_conn->prepare($sql);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . $this->db_conn->error);
		}

		$stmt->bind_param("i", $id);

		if (!$stmt->execute()) {
			$stmt->close();
			throw new Exception("Failed to delete product: " . $stmt->error);
		}

		$affected_rows = $stmt->affected_rows;
		$stmt->close();

		return $affected_rows > 0;
	}
}
