<?php

namespace App\Http\Controllers;

use App\Services\StockMovementService;
use Illuminate\Http\Request;

/**
 * Контроллер для получения информации о движении товаров на складе.
 */
class StockMovementController extends Controller
{
    /**
     * @param StockMovementService $stockMovementService Сервис для работы с движением товаров.
     */
    public function __construct(private StockMovementService $stockMovementService){}

    /**
     * Получение списка движений товаров с фильтрацией.
     *
     * @param Request $request HTTP-запрос.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Получение фильтров из запроса
        $filters = $request->only(['product_id', 'warehouse_id', 'created_from', 'created_to']);
        // Получение движений через сервис
        $movements = $this->stockMovementService->getMovements($filters);

        return response()->json($movements);
    }
}