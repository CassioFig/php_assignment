<?php
declare(strict_types=1);

namespace Controllers;

use Classes\Product;
use Repositories\ProductRepository;

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
}
