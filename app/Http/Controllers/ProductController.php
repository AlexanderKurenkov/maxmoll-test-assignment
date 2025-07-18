<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 1. Validate the incoming pagination parameters
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100', // Max per_page to prevent abuse
        ]);

        // 2. Set default values if not provided
        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 15; // Default items per page

        // 3. Build the query and apply pagination
        $products = Product::with(['stocks.warehouse'])
            ->paginate($perPage, ['*'], 'page', $page);

        // 4. Return the paginated data
        return response()->json($products);
    }
}
