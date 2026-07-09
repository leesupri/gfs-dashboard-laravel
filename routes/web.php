<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashierShiftController;
use App\Http\Controllers\DailyCategoryController;
use App\Http\Controllers\DailyHourController;
use App\Http\Controllers\DailyItemCountController;
use App\Http\Controllers\NoSalesReceiptDetailController;
use App\Http\Controllers\OpeningDayController;
use App\Http\Controllers\UserPageLogController;
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
use App\Http\Controllers\Reports\RecipeBoardController;
use App\Http\Controllers\Reports\OrderBoardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductionSummaryController;
use App\Http\Controllers\ProductionCardController;
use App\Http\Controllers\PurchaseSummaryController;
use App\Http\Controllers\PurchaseDetailController;
use App\Http\Controllers\PurchaseDetailPartnerController;
use App\Http\Controllers\PhysicalStockCountSummaryController;
use App\Http\Controllers\TransferDetailController;
use App\Http\Controllers\SalesForecastController;
use App\Http\Controllers\WasteSummaryController;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffSettingController;
use App\Http\Controllers\SecuritySettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketManagementController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrinterSettingController;
use App\Http\Controllers\CostAlertSettingController;
use App\Http\Controllers\StockCountController;

// ── Public ticket routes (no auth required) ──────────────────────────────────
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/status', [TicketController::class, 'status'])->name('tickets.status');
Route::post('/tickets/status', [TicketController::class, 'statusCheck'])->name('tickets.statusCheck');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
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
Route::middleware(['staff.auth', 'route.permission', 'restrict.today.report'])->group(function () {
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
    Route::get('/reports/recipe-board',        [RecipeBoardController::class, 'index'])->name('reports.recipe-board');
    Route::get('/reports/recipe-board/export', [RecipeBoardController::class, 'export'])->name('reports.recipe-board.export');
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
    Route::get('/reports/sales-forecast', [SalesForecastController::class, 'index'])->name('reports.salesForecast');
    Route::get('/reports/cashier-shift', [CashierShiftController::class, 'index'])->name('reports.cashierShift');
    Route::get('/reports/opening-day', [OpeningDayController::class, 'index'])->name('reports.openingDay');
    Route::get('/reports/no-sales-receipt-detail', [NoSalesReceiptDetailController::class, 'index'])->name('reports.noSalesReceiptDetail');
    Route::get('/reports/daily-category',   [DailyCategoryController::class,  'index'])->name('reports.dailyCategory');
    Route::get('/reports/daily-hour',       [DailyHourController::class,      'index'])->name('reports.dailyHour');
    Route::get('/reports/daily-item-count', [DailyItemCountController::class, 'index'])->name('reports.dailyItemCount');
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
    // Change-password is self-service — every authenticated user can change their own
    Route::get('/settings/change-password', [AuthController::class, 'showChangePassword'])->name('settings.changePassword');
    Route::post('/settings/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:10,1')   // prevent brute-forcing current password
        ->name('settings.changePassword.update');
});

// Page activity log requires an explicit permission grant (admin-level data)
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/settings/page-logs', [UserPageLogController::class, 'index'])->name('settings.pageLog');
});

// ── Staff helpdesk / ticket management ───────────────────────────────────────
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/support/tickets', [TicketManagementController::class, 'index'])->name('support.tickets.index');
    Route::get('/support/tickets/{ticket}', [TicketManagementController::class, 'show'])->name('support.tickets.show');
    Route::post('/support/tickets/{ticket}/reply', [TicketManagementController::class, 'reply'])->name('support.tickets.reply');
    Route::patch('/support/tickets/{ticket}/status', [TicketManagementController::class, 'updateStatus'])->name('support.tickets.updateStatus');
    Route::patch('/support/tickets/{ticket}/assign', [TicketManagementController::class, 'assign'])->name('support.tickets.assign');
});

// ── Thermal print views ───────────────────────────────────────────────────────
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/print/receipt/{invoiceId}',  [PrintController::class, 'receipt'])->whereNumber('invoiceId')->name('print.receipt');
    Route::get('/print/item-sales',           [PrintController::class, 'itemSales'])->name('print.itemSales');
    Route::get('/print/summary-sales',        [PrintController::class, 'summarySales'])->name('print.summarySales');
    Route::get('/print/test/{station}',       [PrintController::class, 'testPrinter'])->name('print.test');
    Route::post('/print/cut/{station}',       [PrintController::class, 'sendCut'])->name('print.cut');
});

// ── Printer settings ──────────────────────────────────────────────────────────
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/settings/printer',                          [PrinterSettingController::class, 'index'])->name('settings.printer');
    Route::post('/settings/printer/stations',                [PrinterSettingController::class, 'store'])->name('settings.printer.store');
    Route::put('/settings/printer/stations/{station}',       [PrinterSettingController::class, 'update'])->name('settings.printer.update');
    Route::delete('/settings/printer/stations/{station}',    [PrinterSettingController::class, 'destroy'])->name('settings.printer.destroy');
    Route::post('/settings/printer/logo',                    [PrinterSettingController::class, 'uploadLogo'])->name('settings.printer.logo');
});

// ── Cost alert settings ───────────────────────────────────────────────────────
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/settings/cost-alert',          [CostAlertSettingController::class, 'index'])->name('settings.costAlert');
    Route::put('/settings/cost-alert',          [CostAlertSettingController::class, 'update'])->name('settings.costAlert.update');
    Route::post('/settings/cost-alert/test',    [CostAlertSettingController::class, 'sendTest'])->name('settings.costAlert.test');
    Route::post('/settings/cost-alert/run',     [CostAlertSettingController::class, 'runCheck'])->name('settings.costAlert.run');
});

// ── Stock counts ──────────────────────────────────────────────────────────────
Route::middleware(['staff.auth', 'route.permission'])->group(function () {
    Route::get('/stock/counts',                  [StockCountController::class, 'index'])->name('stock.counts.index');
    Route::get('/stock/counts/{id}',             [StockCountController::class, 'show'])->name('stock.counts.show');
    Route::post('/stock/counts/{id}/approve',    [StockCountController::class, 'approve'])->name('stock.counts.approve');
    Route::post('/stock/counts/{id}/reject',     [StockCountController::class, 'reject'])->name('stock.counts.reject');
});


