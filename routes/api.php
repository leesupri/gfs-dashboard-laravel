<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ItemApiController;
use App\Http\Controllers\Api\StockCountApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------
| API Routes
|----------------------------------------------------------------------
| All routes return JSON. Authentication is custom Bearer-token via
| the StaffApiAuth middleware — NOT Laravel Sanctum / built-in auth.
*/

// ── Public: login (no auth required) ──────────────────────────────
Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.auth.login');

// ── Protected: require valid Bearer token ─────────────────────────
Route::middleware('staff.api.auth')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.auth.logout');
    Route::get('/auth/me',     [AuthApiController::class, 'me'])->name('api.auth.me');

    // Warehouses (read-only from POS)
    Route::get('/warehouses', [WarehouseApiController::class, 'index'])->name('api.warehouses.index');

    // Items (read-only from POS)
    Route::get('/items',      [ItemApiController::class, 'index'])->name('api.items.index');
    Route::get('/items/{id}', [ItemApiController::class, 'show'])->name('api.items.show');

    // Stock counts (writable on mysql)
    Route::prefix('stock-counts')->name('api.stock-counts.')->group(function () {
        Route::get('/',                        [StockCountApiController::class, 'index'])->name('index');
        Route::post('/',                       [StockCountApiController::class, 'store'])->name('store');
        Route::get('/{id}',                    [StockCountApiController::class, 'show'])->name('show');
        Route::delete('/{id}',                 [StockCountApiController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit',            [StockCountApiController::class, 'submit'])->name('submit');
        Route::post('/{id}/lines',             [StockCountApiController::class, 'addLine'])->name('lines.store');
        Route::put('/{id}/lines/{lineId}',     [StockCountApiController::class, 'updateLine'])->name('lines.update');
        Route::delete('/{id}/lines/{lineId}',  [StockCountApiController::class, 'removeLine'])->name('lines.destroy');
    });
});
