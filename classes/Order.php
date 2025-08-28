<?php
declare(strict_types=1);

namespace Classes;

use DateTime;
use JsonSerializable;

class Order implements JsonSerializable {
  private int $id;
	private int $userId;
	private float $total;
	private DateTime $createdAt;
	private DateTime $updatedAt;

	public function __construct(int $id, int $userId, float $total, DateTime $createdAt, DateTime $updatedAt) {
		$this->id = $id;
		$this->userId = $userId;
		$this->total = $total;
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getUserId(): int {
		return $this->userId;
	}

	public function getTotal(): float {
		return $this->total;
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
			'userId' => $this->userId,
			'total' => $this->total,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}