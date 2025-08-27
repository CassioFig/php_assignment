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

		$newProduct = new Product(
			0,
			$data['name'],
			'teste',
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
