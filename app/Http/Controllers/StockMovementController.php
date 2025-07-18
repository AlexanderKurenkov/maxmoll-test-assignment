<?php

namespace App\Http\Controllers;

use App\Services\StockMovementService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(private StockMovementService $stockMovementService){}

    public function index(Request $request)
    {
        $filters = $request->only(['product_id', 'warehouse_id', 'created_from', 'created_to']);
        $movements = $this->stockMovementService->getMovements($filters);

        return response()->json($movements);
    }
}
