<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Warehouse::all());
    }
}
