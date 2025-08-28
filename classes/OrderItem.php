<?php
declare(strict_types=1);

namespace Classes;

use DateTime;
use JsonSerializable;

class OrderItem implements JsonSerializable {
  private int $id;
	private int $orderId;
	private int $productId;
	private int $amount;
	private DateTime $createdAt;
	private DateTime $updatedAt;

	public function __construct(int $id, int $orderId, int $productId, int $amount, DateTime $createdAt, DateTime $updatedAt) {
		$this->id = $id;
		$this->orderId = $orderId;
		$this->productId = $productId;
		$this->amount = $amount;
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getOrderId(): int {
		return $this->orderId;
	}

	public function getProductId(): int {
		return $this->productId;
	}

	public function getAmount(): int {
		return $this->amount;
	}

	public function getCreatedAt(): DateTime {
		return $this->createdAt;
	}

	public function getUpdatedAt(): DateTime {
		return $this->updatedAt;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'orderId' => $this->orderId,
			'productId' => $this->productId,
			'amount' => $this->amount,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}