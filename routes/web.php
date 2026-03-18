<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ItemSalesController;
use App\Http\Controllers\noSalesController;
use App\Http\Controllers\SummarySalesController;
use App\Http\Controllers\VoidReportController;
use App\Http\Controllers\Reports\SalesConsumptionWarehouseController;
use App\Http\Controllers\Reports\SalesConsumptionDetailInvoiceController;
use App\Http\Controllers\Reports\RecipeReportController;


Route::get('/', function () {
    return view('dashboard', [
        'title' => 'Dashboard',
        'active' => 'dashboard',
    ]);
})->name('dashboard');
Route::get('/item-sales', [ItemSalesController::class, 'index'])->name('itemSales.index');
Route::get('/item-sales/export', [ItemSalesController::class, 'exportCsv'])->name('itemSales.export');
Route::get('/no-sales', [NoSalesController::class, 'index'])->name('noSales.index');
Route::get('/reports/void', [VoidReportController::class, 'index'])->name('reports.void');
Route::get('/summary-sales', [SummarySalesController::class, 'index'])->name('summarySales.index');
Route::get('/summary-sales/export', [SummarySalesController::class,'exportCsv'])->name('summarySales.export');
Route::get('/no-sales/export', [NoSalesController::class, 'export'])->name('noSales.export');
Route::get('/reports/consumption-warehouse', [SalesConsumptionWarehouseController::class, 'index'])->name('reports.consumptionWarehouse');
Route::get('/reports/consumption-warehouse/export', [SalesConsumptionWarehouseController::class, 'export'])->name('reports.consumptionWarehouse.export');
Route::get('/reports/consumption-detail-invoice', [SalesConsumptionDetailInvoiceController::class, 'index'])->name('reports.consumptionDetailInvoice');
Route::get('/reports/consumption-detail-invoice/export', [SalesConsumptionDetailInvoiceController::class, 'export'])->name('reports.consumptionDetailInvoice.export');
Route::get('/reports/recipe', [RecipeReportController::class, 'index'])->name('reports.recipe');
Route::get('/reports/recipe/export', [RecipeReportController::class, 'export'])->name('reports.recipe.export');
Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
Route::get('/sales/export', [SalesController::class, 'export'])->name('sales.export');
Route::get('/sales/{invoiceId}', [SalesController::class, 'show'])->name('sales.show'); // optional detail page
Route::get('/sales/{invoice_id}/receipt', [SalesController::class, 'receipt'])
  ->whereNumber('invoice_id')
  ->name('sales.receipt');
