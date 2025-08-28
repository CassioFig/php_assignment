<?php
declare(strict_types=1);

namespace Classes;

use JsonSerializable;

class Product implements JsonSerializable {
  private int $id;
	private string $name;
	private string $image;
	private float $price;
	private int $stock;

	public function __construct(int $id, string $name, string $image, float $price, int $stock) {
		$this->id = $id;
		$this->name = $name;
		$this->image = $image;
		$this->price = $price;
		$this->stock = $stock;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getImage(): string {
		return $this->image;
	}

	public function getPrice(): float {
		return $this->price;
	}

	public function getStock(): int {
		return $this->stock;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'image' => $this->image,
			'price' => $this->price,
			'stock' => $this->stock,
		];
	}
}