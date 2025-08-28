<?php
declare(strict_types=1);

namespace Controllers;

use Classes\Order;
use Repositories\OrderRepository;
use DateTime;
use Exception;

class OrderController
{
	private OrderRepository $orderRepository;

	public function __construct(OrderRepository $orderRepository)
	{
		$this->orderRepository = $orderRepository;
	}

	public function create($data)
	{
		if (!isset($data['user_id']) || !isset($data['total'])) {
			http_response_code(400);
			echo json_encode(['error' => 'user_id and total are required.']);
			return;
		}

		$newOrder = new Order(
			0,
			(int)$data['user_id'],
			(float)$data['total'],
			new DateTime(),
			new DateTime()
		);

		try {
			$savedOrder = $this->orderRepository->create($newOrder);
			http_response_code(201);
			echo json_encode(['message' => 'Order created successfully.', 'order' => $savedOrder]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to create order: ' . $e->getMessage()]);
		}
	}

	public function getAll()
	{
		try {
			$orders = $this->orderRepository->getAll();
			http_response_code(200);
			echo json_encode(['orders' => $orders]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to get orders: ' . $e->getMessage()]);
		}
	}

	public function update($id, $data)
	{
		if (!isset($data['total'])) {
			http_response_code(400);
			echo json_encode(['error' => 'total is required.']);
			return;
		}

		try {
			$updatedOrder = $this->orderRepository->update((int)$id, (float)$data['total']);
			if ($updatedOrder) {
				http_response_code(200);
				echo json_encode(['message' => 'Order updated successfully.', 'order' => $updatedOrder]);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Order not found.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to update order: ' . $e->getMessage()]);
		}
	}

	public function delete($id)
	{
		try {
			$deleted = $this->orderRepository->delete((int)$id);
			if ($deleted) {
				http_response_code(200);
				echo json_encode(['message' => 'Order deleted successfully.']);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Order not found.']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to delete order: ' . $e->getMessage()]);
		}
	}
}
