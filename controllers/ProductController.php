<?php
declare(strict_types=1);

namespace Controllers;

use Classes\Product;
use Repositories\ProductRepository;
use Exception;

class ProductController
{
	private ProductRepository $productRepository;

	public function __construct(ProductRepository $productRepository)
	{
		$this->productRepository = $productRepository;
	}

	public function create($data, $files)
	{
		$existingProduct = $this->productRepository->getByName($data['name']);
		if ($existingProduct) {
			http_response_code(409);
			echo json_encode(['error' => 'Product with this name already exists.']);
			return;
		}

		$imagePath = '';
		if (isset($files['image']) && $files['image']['error'] == 0) {
			$uploadDir = 'uploads/';
			
			if (!is_dir($uploadDir)) {
				if (!mkdir($uploadDir, 0755, true)) {
					http_response_code(500);
					echo json_encode(['error' => 'Failed to create upload directory.']);
					return;
				}
			}
			
			$fileName = $files['image']['name'];
			$targetPath = $uploadDir . $fileName;
			
			if (move_uploaded_file($files['image']['tmp_name'], $targetPath)) {
				$imagePath = $targetPath;
			} else {
				http_response_code(500);
				echo json_encode(['error' => 'Failed to upload image.']);
				return;
			}
		}

		$newProduct = new Product(
			0,
			$data['name'],
			$imagePath,
			(float)$data['price'],
			(int)$data['stock']
		);

		$savedProduct = $this->productRepository->create($newProduct);
		if ($savedProduct) {
			http_response_code(201);
			echo json_encode(['message' => 'Product created successfully.', 'product' => $savedProduct]);
		} else {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to create product.']);
		}
	}

	public function getAll()
	{
		try {
			$products = $this->productRepository->getAll();
			http_response_code(200);
			echo json_encode(['products' => $products]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to get products: ' . $e->getMessage()]);
		}
	}

	public function update($id, $data, $files = null)
	{
		$existingProduct = $this->productRepository->getByName($data['name']);
		if ($existingProduct && $existingProduct['id'] != (int)$id) {
			http_response_code(409);
			echo json_encode(['error' => 'Product with this name already exists.']);
			return;
		}

		$currentProduct = $this->productRepository->getById((int)$id);
		if (!$currentProduct) {
			http_response_code(404);
			echo json_encode(['error' => 'Product not found.']);
			return;
		}

		$imagePath = '';
		$oldImagePath = $currentProduct->getImage() ?? '';
		
		if (isset($files['image']) && $files['image']['error'] == 0) {
			$uploadDir = 'uploads/';
			
			if (!is_dir($uploadDir)) {
				if (!mkdir($uploadDir, 0755, true)) {
					http_response_code(500);
					echo json_encode(['error' => 'Failed to create upload directory.']);
					return;
				}
			}
			
			$fileName = $files['image']['name'];
			$targetPath = $uploadDir . $fileName;
			
			if (move_uploaded_file($files['image']['tmp_name'], $targetPath)) {
				$imagePath = $targetPath;
				
				if (!empty($oldImagePath) && file_exists($oldImagePath) && $oldImagePath !== $imagePath) {
					unlink($oldImagePath);
				}
			} else {
				http_response_code(500);
				echo json_encode(['error' => 'Failed to upload image.']);
				return;
			}
		}

		try {
			$updatedProduct = $this->productRepository->update(
				(int)$id,
				$data['name'],
				$imagePath,
				(float)$data['price'],
				(int)$data['stock']
			);
			
			if ($updatedProduct) {
				http_response_code(200);
				echo json_encode(['message' => 'Product updated successfully.', 'product' => $updatedProduct]);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Product not found.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to update product: ' . $e->getMessage()]);
		}
	}

	public function delete($id)
	{
		try {
			$product = $this->productRepository->getById((int)$id);
			if (!$product) {
				http_response_code(404);
				echo json_encode(['error' => 'Product not found.']);
				return;
			}

			$deleted = $this->productRepository->delete((int)$id);
			if ($deleted) {
				if (!empty($product->getImage()) && file_exists($product->getImage())) {
					unlink($product->getImage());
				}
				
				http_response_code(200);
				echo json_encode(['message' => 'Product deleted successfully.']);
			} else {
				http_response_code(500);
				echo json_encode(['error' => 'Failed to delete product.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to delete product: ' . $e->getMessage()]);
		}
	}
}
