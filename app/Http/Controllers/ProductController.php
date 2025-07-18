<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для управления товарами.
 */
class ProductController extends Controller
{
    /**
     * Получение списка товаров с пагинацией.
     *
     * @param Request $request HTTP-запрос.
     * @return JsonResponse JSON-ответ со списком товаров.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Валидация входящих параметров пагинации
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100', // Максимальное значение per_page для предотвращения злоупотреблений
        ]);

        // 2. Установка значений по умолчанию, если они не предоставлены
        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 15; // Количество элементов на странице по умолчанию

        // 3. Построение запроса и применение пагинации
        $products = Product::with(['stocks.warehouse'])
            ->paginate($perPage, ['*'], 'page', $page);

        // 4. Возврат данных с пагинацией
        return response()->json($products);
    }
}