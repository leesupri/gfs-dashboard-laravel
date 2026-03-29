<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesConsumptionDetailInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start', now()->toDateString());
        $end   = $request->input('end', now()->toDateString());

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        $dateIndexFrom = (int) Carbon::parse($start)->format('Ym');
        $dateIndexTo   = (int) Carbon::parse($end)->format('Ym');

        $invoice   = trim((string) $request->input('invoice', ''));
        $warehouse = trim((string) $request->input('warehouse', ''));
        $item      = trim((string) $request->input('item', ''));

        $rows = DB::connection('reports_mysql')->table('tbl_sales_consumptions as sc')
            ->join('tbl_sales_consumption_lines as scl', 'scl.sales_consumption_id', '=', 'sc.id')
            ->join('tbl_items as item', 'scl.item_id', '=', 'item.id')
            ->join('tbl_categories as cat', 'item.category_id', '=', 'cat.id')
            ->join('tbl_warehouses as warehouse', 'scl.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('tbl_sales as sales', 'sales.id', '=', 'sc.salesId')
            ->whereBetween('sc.dateIndex', [$dateIndexFrom, $dateIndexTo])
            ->whereBetween('sc.date', [$from, $to])
            ->whereRaw('COALESCE(item.inventoryToRecipeConversion,0) > 0')

            // filters
            ->when($invoice !== '', fn ($q) =>
                $q->where('sales.invoice_id', $invoice)
            )
            ->when($warehouse !== '', fn ($q) =>
                $q->where('warehouse.name', 'like', "%{$warehouse}%")
            )
            ->when($item !== '', fn ($q) =>
                $q->where('item.name', 'like', "%{$item}%")
            )

            ->selectRaw("
                cat.name              AS category,
                item.name             AS item,
                sales.invoice_id      AS invoice_id,
                sales.date            AS date,
                scl.resultDescription,
                scl.resultQuantity,
                scl.quantity,
                item.recipeUom        AS uom,
                scl.unitCost,
                scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0)) AS totalCost,
                warehouse.name        AS warehouse
            ")
            ->orderBy('invoice_id')
            ->orderBy('resultDescription')
            ->orderBy('item.name')
            ->get();

        $byInvoice = $rows->groupBy('invoice_id');

        $grandTotal = $rows->sum('totalCost');

        return view('reports.consumption_detail_invoice.index', [
            'title'      => 'Sales Consumption Detail per Invoice',
            'active'     => 'reports-consumption-invoice',
            'rows'       => $rows,
            'byInvoice'  => $byInvoice,
            'start'      => $start,
            'end'        => $end,
            'invoice'    => $invoice,
            'warehouse'  => $warehouse,
            'item'       => $item,
            'grandTotal' => $grandTotal,
        ]);
    }
    public function export(Request $request)
{
    $start = $request->input('start', now()->toDateString());
    $end   = $request->input('end', now()->toDateString());

    $from = Carbon::parse($start)->startOfDay();
    $to   = Carbon::parse($end)->endOfDay();

    $dateIndexFrom = (int) Carbon::parse($start)->format('Ym');
    $dateIndexTo   = (int) Carbon::parse($end)->format('Ym');

    $rows = DB::table('tbl_sales_consumptions as sc')
        ->join('tbl_sales_consumption_lines as scl', 'scl.sales_consumption_id', '=', 'sc.id')
        ->join('tbl_items as item', 'scl.item_id', '=', 'item.id')
        ->join('tbl_warehouses as warehouse', 'scl.warehouse_id', '=', 'warehouse.id')
        ->leftJoin('tbl_sales as sales', 'sales.id', '=', 'sc.salesId')
        ->whereBetween('sc.dateIndex', [$dateIndexFrom, $dateIndexTo])
        ->whereBetween('sc.date', [$from, $to])
        ->selectRaw("
            sales.invoice_id,
            sales.date,
            scl.resultDescription,
            item.name AS item,
            scl.quantity,
            item.recipeUom AS uom,
            warehouse.name AS warehouse,
            scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0)) AS totalCost
        ")
        ->orderBy('invoice_id')
        ->get();

    $file = "consumption_detail_invoice_{$start}_to_{$end}.csv";

    return response()->streamDownload(function () use ($rows) {
        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Invoice',
            'Date',
            'Result',
            'Item',
            'Qty',
            'UOM',
            'Warehouse',
            'Total Cost'
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->invoice_id,
                optional($r->date)->format('Y-m-d'),
                $r->resultDescription,
                $r->item,
                $r->quantity,
                $r->uom,
                $r->warehouse,
                number_format($r->totalCost, 2, '.', '')
            ]);
        }

        fclose($out);
    }, $file);
}
}
