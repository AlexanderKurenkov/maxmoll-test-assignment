<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @OA\Info(title="API заказов", version="1.0")
 */
class OrderController extends Controller
{
    /**
     * @param OrderService $orderService Сервис для работы с заказами.
     */
    public function __construct(private OrderService $orderService) {}

    /**
     * Получение списка заказов с фильтрацией и пагинацией.
     *
     * @param Request $request HTTP-запрос.
     * @return JsonResponse JSON-ответ со списком заказов.
     */
    public function index(Request $request): JsonResponse
    {
        // Валидация параметров запроса
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

        // Формирование фильтров
        $filters = [
            'customer' => $validated['customer'] ?? null,
            'status' => $validated['status'] ?? null,
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'created_from' => $validated['created_from'] ?? null,
            'created_to' => $validated['created_to'] ?? null,
        ];

        // Получение заказов через сервис
        $orders = $this->orderService->getOrders($filters, $perPage, $page);
        return response()->json($orders);
    }

    /**
     * Создание нового заказа.
     *
     * @param Request $request HTTP-запрос с данными заказа.
     * @return JsonResponse JSON-ответ с созданным заказом.
     */
    public function store(Request $request): JsonResponse
    {
        // Валидация данных нового заказа
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.count' => 'required|integer|min:1',
        ]);

        // Создание заказа через сервис
        $order = $this->orderService->createOrder($validated);
        return response()->json($order, 201);
    }

    /**
     * Обновление существующего заказа.
     *
     * @param Request $request HTTP-запрос с данными для обновления.
     * @param int $id ID заказа.
     * @return JsonResponse JSON-ответ с обновленным заказом.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Валидация данных для обновления
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.count' => 'required|integer|min:1',
        ]);

        // Обновление заказа через сервис
        $order = $this->orderService->updateOrder($id, $validated);

        return response()->json($order);
    }

    /**
     * Завершение заказа.
     *
     * @param int $id ID заказа.
     * @return JsonResponse JSON-ответ с сообщением о выполнении.
     */
    public function complete(int $id): JsonResponse
    {
        $this->orderService->completeOrder($id);
        return response()->json(['message' => 'Заказ выполнен']);
    }

    /**
     * Отмена заказа.
     *
     * @param int $id ID заказа.
     * @return JsonResponse JSON-ответ с сообщением об отмене.
     */
    public function cancel(int $id): JsonResponse
    {
        $this->orderService->cancelOrder($id);
        return response()->json(['message' => 'Заказ отменен']);
    }

    /**
     * Возобновление отмененного заказа.
     *
     * @param int $id ID заказа.
     * @return JsonResponse JSON-ответ с сообщением о возобновлении.
     */
    public function resume(int $id): JsonResponse
    {
        $this->orderService->resumeOrder($id);
        return response()->json(['message' => 'Порядок возобновлен']);
    }
}