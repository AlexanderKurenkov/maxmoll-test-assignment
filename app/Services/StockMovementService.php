<?php

namespace App\Services;

use App\Models\StockMovement;

class StockMovementService
{
    public function getMovements(array $filters = [], int $perPage = 15)
    {
        $query = StockMovement::query()->with(['product', 'warehouse']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query->latest()->paginate($perPage);
    }
}
