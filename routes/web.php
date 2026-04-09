<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ItemSalesController;
use App\Http\Controllers\noSalesController;
use App\Http\Controllers\SummarySalesController;
use App\Http\Controllers\VoidReportController;
use App\Http\Controllers\MarketListController;
use App\Http\Controllers\Reports\SalesConsumptionWarehouseController;
use App\Http\Controllers\Reports\SalesConsumptionDetailInvoiceController;
use App\Http\Controllers\Reports\RecipeReportController;
use App\Http\Controllers\Reports\OrderBoardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductionSummaryController;
use App\Http\Controllers\ProductionCardController;
use App\Http\Controllers\PurchaseSummaryController;
use App\Http\Controllers\PurchaseDetailController;
use App\Http\Controllers\PurchaseDetailPartnerController;
use App\Http\Controllers\PhysicalStockCountSummaryController;
use App\Http\Controllers\TransferDetailController;
use App\Http\Controllers\WasteSummaryController;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffSettingController;
use App\Http\Controllers\SecuritySettingController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['staff.auth', 'route.permission'])->group(function () {
   

    Route::get('/settings/staff', [StaffSettingController::class, 'index'])->name('settings.staff');
    Route::post('/settings/staff', [StaffSettingController::class, 'store'])->name('settings.staff.store');
    Route::put('/settings/staff/{staffUser}', [StaffSettingController::class, 'update'])->name('settings.staff.update');
    Route::delete('/settings/staff/{staffUser}', [StaffSettingController::class, 'destroy'])->name('settings.staff.destroy');

    Route::get('/settings/security', [SecuritySettingController::class, 'index'])->name('settings.security');
    Route::put('/settings/security/{staffUser}', [SecuritySettingController::class, 'update'])->name('settings.security.update');
});
// Route::get('/', function () {
//     return view('dashboard', [
//         'title' => 'Dashboard',
//         'active' => 'dashboard',
//     ]);
// })->name('dashboard');
Route::redirect('/', '/welcome');
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
    Route::get('/reports/activity-log', [ReportController::class, 'activityLog'])->name('reports.activityLog');
    Route::get('/reports/market-list', [MarketListController::class, 'marketList'])->name('reports.marketList');
    Route::get('/reports/order-board', [OrderBoardController::class, 'index'])->name('reports.orderBoard');
    Route::get('/reports/production-summary', [ProductionSummaryController::class, 'index'])->name('reports.productionSummary');
    Route::get('/reports/production-card', [ProductionCardController::class, 'index'])->name('reports.productionCard.index');
    Route::get('/reports/purchase-summary', [PurchaseSummaryController::class, 'index'])->name('reports.purchaseSummary');
    Route::get('/reports/purchase-detail', [PurchaseDetailController::class, 'index'])->name('reports.purchaseDetail');
    Route::get('/reports/purchase-detail-partner', [PurchaseDetailPartnerController::class, 'index'])->name('reports.purchaseDetailPartner');
    Route::get('/reports/physical-stock-count-summary', [PhysicalStockCountSummaryController::class, 'index'])->name('reports.physicalStockCountSummary');
    Route::get('/reports/transfer-detail', [TransferDetailController::class, 'index'])->name('reports.transferDetail');
    Route::get('/reports/waste-summary', [WasteSummaryController::class, 'index'])->name('reports.wasteSummary');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/export', [SalesController::class, 'export'])->name('sales.export');
    
    Route::get('/sales/{invoiceId}', [SalesController::class, 'show'])->name('sales.show'); // optional detail page
    
    Route::get('/sales/{invoice_id}/receipt', [SalesController::class, 'receipt'])
        ->whereNumber('invoice_id')
        ->name('sales.receipt');
    Route::get('/reports/production-card/{id}', [ProductionCardController::class, 'show'])
        ->whereNumber('id')
        ->name('reports.productionCard.show');
});

Route::middleware(['staff.auth'])->group(function () {
    Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');
    Route::get('/settings/change-password', [AuthController::class, 'showChangePassword'])->name('settings.changePassword');
    Route::post('/settings/change-password', [AuthController::class, 'changePassword'])->name('settings.changePassword.update');
});

