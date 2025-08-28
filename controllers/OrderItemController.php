<?php
declare(strict_types=1);

namespace Controllers;

use Classes\OrderItem;
use Repositories\OrderItemRepository;
use DateTime;
use Exception;

class OrderItemController
{
	private OrderItemRepository $orderItemRepository;

	public function __construct(OrderItemRepository $orderItemRepository)
	{
		$this->orderItemRepository = $orderItemRepository;
	}

	public function create($data)
	{
		if (!isset($data['order_id']) || !isset($data['product_id']) || !isset($data['amount'])) {
			http_response_code(400);
			echo json_encode(['error' => 'order_id, product_id and amount are required.']);
			return;
		}

		$newOrderItem = new OrderItem(
			0,
			(int)$data['order_id'],
			(int)$data['product_id'],
			(int)$data['amount'],
			new DateTime(),
			new DateTime()
		);

		try {
			$savedOrderItem = $this->orderItemRepository->create($newOrderItem);
			http_response_code(201);
			echo json_encode(['message' => 'Order item created successfully.', 'orderItem' => $savedOrderItem]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to create order item: ' . $e->getMessage()]);
		}
	}

	public function getAll()
	{
		try {
			$orderItems = $this->orderItemRepository->getAll();
			http_response_code(200);
			echo json_encode(['orderItems' => $orderItems]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to get order items: ' . $e->getMessage()]);
		}
	}

	public function update($id, $data)
	{
		if (!isset($data['amount'])) {
			http_response_code(400);
			echo json_encode(['error' => 'amount is required.']);
			return;
		}

		try {
			$updatedOrderItem = $this->orderItemRepository->update((int)$id, (int)$data['amount']);
			if ($updatedOrderItem) {
				http_response_code(200);
				echo json_encode(['message' => 'Order item updated successfully.', 'orderItem' => $updatedOrderItem]);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Order item not found.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to update order item: ' . $e->getMessage()]);
		}
	}

	public function delete($id)
	{
		try {
			$deleted = $this->orderItemRepository->delete((int)$id);
			if ($deleted) {
				http_response_code(200);
				echo json_encode(['message' => 'Order item deleted successfully.']);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Order item not found.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to delete order item: ' . $e->getMessage()]);
		}
	}
}
