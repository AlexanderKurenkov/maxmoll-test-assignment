<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']); // фильтры + пагинация
    Route::post('/', [OrderController::class, 'store']); // создать заказ
    Route::put('{id}', [OrderController::class, 'update']); // обновить заказ (без статуса)
    Route::post('{id}/complete', [OrderController::class, 'complete']); // завершить
    Route::post('{id}/cancel', [OrderController::class, 'cancel']); // отменить
    Route::post('{id}/resume', [OrderController::class, 'resume']); // возобновить
});

Route::middleware('auth:sanctum')->get('/warehouses', [WarehouseController::class, 'index']); // получение списка складов