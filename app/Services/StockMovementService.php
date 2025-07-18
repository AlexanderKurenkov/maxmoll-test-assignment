<?php

namespace App\Services;

use App\Models\StockMovement;

/**
 * Сервис для работы с движениями товаров на складе.
 */
class StockMovementService
{
    /**
     * Получает список движений товаров с возможностью фильтрации и пагинации.
     *
     * @param array $filters Массив фильтров (product_id, warehouse_id, created_from, created_to).
     * @param int $perPage Количество движений на страницу.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMovements(array $filters = [], int $perPage = 15)
    {
        // Создание запроса для модели StockMovement с загрузкой связанных моделей Product и Warehouse
        $query = StockMovement::query()->with(['product', 'warehouse']);

        // Фильтрация по ID продукта
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
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

        // Сортировка по дате создания в убывающем порядке и применение пагинации
        return $query->latest()->paginate($perPage);
    }
}