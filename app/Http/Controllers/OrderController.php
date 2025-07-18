<?php
namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->orderService->getOrders($request));
    }

    public function store(Request $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->all());
        return response()->json($order, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrder($id, $request->all());
        return response()->json($order);
    }

    public function complete(int $id): JsonResponse
    {
        $this->orderService->completeOrder($id);
        return response()->json(['message' => 'Order completed']);
    }

    public function cancel(int $id): JsonResponse
    {
        $this->orderService->cancelOrder($id);
        return response()->json(['message' => 'Order canceled']);
    }

    public function resume(int $id): JsonResponse
    {
        $this->orderService->resumeOrder($id);
        return response()->json(['message' => 'Order resumed']);
    }
}
