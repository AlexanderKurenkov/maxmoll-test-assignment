<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Сервис для управления заказами.
 */
class OrderService
{
    /**
     * Получает список заказов с возможностью фильтрации и пагинации.
     *
     * @param array $filters Массив фильтров (customer, status, warehouse_id, created_from, created_to).
     * @param int $perPage Количество заказов на страницу.
     * @param int $page Номер текущей страницы.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $filters = [], int $perPage = 15, int $page = 1)
    {
        $query = Order::query();

        // Фильтрация по имени клиента
        if (!empty($filters['customer'])) {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        // Фильтрация по статусу заказа
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Фильтрация по ID склада
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        // Фильтрация по дате создания (от)
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        // Фильтрация по дате создания (до)
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Применение пагинации
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Создает новый заказ и обновляет остатки на складе.
     *
     * @param array $data Данные для создания заказа (customer, warehouse_id, items).
     * @return Order Созданный заказ.
     * @throws ValidationException Если недостаточно товара на складе.
     */
    public function createOrder(array $data)
    {
        // Запуск транзакции для обеспечения атомарности операции
        return DB::transaction(function () use ($data) {
            // Создание нового заказа
            $order = Order::create([
                'customer' => $data['customer'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'active',
            ]);

            // Обработка каждого элемента заказа
            foreach ($data['items'] as $item) {
                // Уменьшение остатка товара на складе
                $this->decreaseStock($item['product_id'], $data['warehouse_id'], $item['count']);

                // Создание элемента заказа
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
            }

            // Загрузка связанных элементов и возврат заказа
            return $order->load('items');
        });
    }

    /**
     * Обновляет существующий заказ и корректирует остатки на складе.
     *
     * @param int $id ID заказа.
     * @param array $data Данные для обновления заказа (customer, items).
     * @return Order Обновленный заказ.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если заказ не найден или неактивен.
     * @throws ValidationException Если недостаточно товара на складе.
     */
    public function updateOrder(int $id, array $data)
    {
        // Поиск активного заказа
        $order = Order::where('status', 'active')->findOrFail($id);

        // Запуск транзакции
        return DB::transaction(function () use ($order, $data) {
            // Возврат старых остатков на склад
            foreach ($order->items as $item) {
                $this->increaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            // Удаление старых элементов заказа
            $order->items()->delete();
            // Обновление данных заказа
            $order->update(['customer' => $data['customer']]);

            // Добавление новых элементов заказа
            foreach ($data['items'] as $item) {
                // Уменьшение остатка товара на складе
                $this->decreaseStock($item['product_id'], $order->warehouse_id, $item['count']);

                // Создание нового элемента заказа
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
            }

            // Загрузка связанных элементов и возврат заказа
            return $order->load('items');
        });
    }

    /**
     * Завершает заказ.
     *
     * @param int $id ID заказа.
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если заказ не найден или неактивен.
     */
    public function completeOrder(int $id): void
    {
        // Поиск активного заказа и обновление его статуса
        $order = Order::where('status', 'active')->findOrFail($id);
        $order->update(['status' => 'completed', 'completed_at' => now()]);
    }

    /**
     * Отменяет заказ и возвращает товары на склад.
     *
     * @param int $id ID заказа.
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если заказ не найден или неактивен.
     */
    public function cancelOrder(int $id): void
    {
        // Поиск активного заказа
        $order = Order::where('status', 'active')->findOrFail($id);

        // Запуск транзакции
        DB::transaction(function () use ($order) {
            // Возврат товаров на склад
            foreach ($order->items as $item) {
                $this->increaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            // Обновление статуса заказа
            $order->update(['status' => 'canceled']);
        });
    }

    /**
     * Возобновляет отмененный заказ и снова списывает товары со склада.
     *
     * @param int $id ID заказа.
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если заказ не найден или не отменен.
     * @throws ValidationException Если недостаточно товара на складе.
     */
    public function resumeOrder(int $id): void
    {
        // Поиск отмененного заказа
        $order = Order::where('status', 'canceled')->findOrFail($id);

        // Запуск транзакции
        DB::transaction(function () use ($order) {
            // Списание товаров со склада
            foreach ($order->items as $item) {
                $this->decreaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            // Обновление статуса заказа
            $order->update(['status' => 'active']);
        });
    }

    /**
     * Уменьшает количество товара на складе.
     *
     * @param int $productId ID продукта.
     * @param int $warehouseId ID склада.
     * @param int $count Количество для уменьшения.
     * @return void
     * @throws ValidationException Если недостаточно товара на складе.
     */
    private function decreaseStock(int $productId, int $warehouseId, int $count): void
    {
        // Поиск и блокировка записи о запасе
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate() // Блокировка строки для предотвращения гонки данных
            ->first();

        // Проверка наличия товара
        if (!$stock || $stock->stock < $count) {
            throw ValidationException::withMessages([
                'stock' => "Недостаточно товара на складе с ID = {$productId}"
            ]);
        }

        // Уменьшение количества товара
        Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('stock', $count);

        // Запись движения товара
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity_change' => -$count, // Отрицательное значение для списания
        ]);
    }

    /**
     * Увеличивает количество товара на складе.
     *
     * @param int $productId ID продукта.
     * @param int $warehouseId ID склада.
     * @param int $count Количество для увеличения.
     * @return void
     */
    private function increaseStock(int $productId, int $warehouseId, int $count): void
    {
        // Увеличение количества товара
        Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate() // Блокировка строки для предотвращения гонки данных
            ->increment('stock', $count);

        // Запись движения товара
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity_change' => $count, // Положительное значение для поступления
        ]);
    }
}