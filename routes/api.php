<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ItemApiController;
use App\Http\Controllers\Api\StockCountApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public
    Route::post('/auth/login', [AuthApiController::class, 'login'])
        ->name('api.auth.login');

    // Protected
    Route::middleware('staff.api.auth')->group(function () {
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])
            ->name('api.auth.logout');
        Route::get('/auth/me', [AuthApiController::class, 'me'])
            ->name('api.auth.me');

        Route::get('/warehouses', [WarehouseApiController::class, 'index'])->name('api.warehouses.index');

        Route::get('/items', [ItemApiController::class, 'index'])->name('api.items.index');
        Route::get('/items/{id}', [ItemApiController::class, 'show'])->name('api.items.show');

        Route::prefix('stock-counts')->name('api.stock-counts.')->group(function () {
            Route::get('/', [StockCountApiController::class, 'index'])->name('index');
            Route::post('/', [StockCountApiController::class, 'store'])->name('store');
            Route::get('/{id}', [StockCountApiController::class, 'show'])->name('show');
            Route::put('/{id}', [StockCountApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [StockCountApiController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/submit', [StockCountApiController::class, 'submit'])->name('submit');
            Route::post('/{id}/lines', [StockCountApiController::class, 'upsertLines'])->name('lines.upsert');
            Route::put('/{id}/lines/{lineId}', [StockCountApiController::class, 'updateLine'])->name('lines.update');
            Route::delete('/{id}/lines/{lineId}', [StockCountApiController::class, 'destroyLine'])->name('lines.destroy');
        });
    });

});
