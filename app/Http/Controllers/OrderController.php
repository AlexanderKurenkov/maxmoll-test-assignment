<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,completed,canceled',
            'warehouse_id' => 'nullable|integer|min:1',
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 15;

        $filters = [
            'customer' => $validated['customer'] ?? null,
            'status' => $validated['status'] ?? null,
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'created_from' => $validated['created_from'] ?? null,
            'created_to' => $validated['created_to'] ?? null,
        ];

        $orders = $this->orderService->getOrders($filters, $perPage, $page);
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.count' => 'required|integer|min:1',
        ]);

        $order = $this->orderService->createOrder($validated);
        return response()->json($order, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.count' => 'required|integer|min:1',
        ]);

        $order = $this->orderService->updateOrder($id, $validated);

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
