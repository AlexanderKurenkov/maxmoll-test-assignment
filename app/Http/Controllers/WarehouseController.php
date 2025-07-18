<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для управления складами.
 */
class WarehouseController extends Controller
{
    /**
     * Получение списка всех складов.
     *
     * @return JsonResponse JSON-ответ со списком складов.
     */
    public function index(): JsonResponse
    {
        // Возвращаем все склады в формате JSON
        return response()->json(Warehouse::all());
    }
}