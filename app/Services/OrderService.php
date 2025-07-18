<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class OrderService
{
    public function getOrders(array $filters = [], int $perPage = 15, int $page = 1)
    {
        // Новый экземпляр построителя запросов Eloquent.
        $query = Order::query();

        if (!empty($filters['customer'])) {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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

        // Выполнить SQL-запрос с пагинацией.
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer' => $data['customer'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'active',
            ]);

            foreach ($data['items'] as $item) {
                $this->decreaseStock($item['product_id'], $data['warehouse_id'], $item['count']);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
            }

            return $order->load('items');
        });
    }

    public function updateOrder(int $id, array $data)
    {
        $order = Order::where('status', 'active')->findOrFail($id);

        return DB::transaction(function () use ($order, $data) {
            foreach ($order->items as $item) {
                $this->increaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            $order->items()->delete();
            $order->update(['customer' => $data['customer']]);

            foreach ($data['items'] as $item) {
                $this->decreaseStock($item['product_id'], $order->warehouse_id, $item['count']);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
            }

            return $order->load('items');
        });
    }

    public function completeOrder(int $id): void
    {
        $order = Order::where('status', 'active')->findOrFail($id);
        $order->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function cancelOrder(int $id): void
    {
        $order = Order::where('status', 'active')->findOrFail($id);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $this->increaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            $order->update(['status' => 'canceled']);
        });
    }

    public function resumeOrder(int $id): void
    {
        $order = Order::where('status', 'canceled')->findOrFail($id);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $this->decreaseStock($item->product_id, $order->warehouse_id, $item->count);
            }

            $order->update(['status' => 'active']);
        });
    }

    private function decreaseStock(int $productId, int $warehouseId, int $count): void
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (!$stock || $stock->stock < $count) {
            throw ValidationException::withMessages([
                'stock' => "Not enough stock for product ID {$productId}"
            ]);
        }

        Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('stock', $count);
    }

    private function increaseStock(int $productId, int $warehouseId, int $count): void
    {
        Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->increment('stock', $count);
    }
}
