<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesConsumptionWarehouseController extends Controller
{
    public function index(Request $request)
    {
        // default: today
        $start = $request->input('start', now()->toDateString());
        $end   = $request->input('end', now()->toDateString());

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        // If you really need dateIndex filter, compute it or just ignore it.
        // Jasper uses sc.dateindex too, but in your Laravel DB maybe you can skip it safely.
        // If you MUST keep, add ->whereBetween('sc.dateindex', [$dateIndexFrom, $dateIndexTo])

        $dateIndexFrom = (int) Carbon::parse($start)->format('Ym');
        $dateIndexTo   = (int) Carbon::parse($end)->format('Ym');

        // search filters
        $q         = trim((string) $request->input('q', ''));          // search both
        $warehouse = trim((string) $request->input('warehouse', ''));  // warehouse only
        $item      = trim((string) $request->input('item', ''));       // item only

        $rows = DB::connection('reports_mysql')->table('tbl_sales_consumptions as sc')
            ->join('tbl_sales_consumption_lines as scl', 'scl.sales_consumption_id', '=', 'sc.id')
            ->join('tbl_items as item', 'scl.item_id', '=', 'item.id')
            ->join('tbl_warehouses as warehouse', 'scl.warehouse_id', '=', 'warehouse.id')
            ->whereBetween('sc.dateIndex', [$dateIndexFrom, $dateIndexTo]) 
            ->whereBetween('sc.date', [$from, $to])
            ->whereRaw("COALESCE(item.inventoryToRecipeConversion,0) > 0")
            
            // ✅ search
        ->when($warehouse !== '', function ($query) use ($warehouse) {
            $query->where('warehouse.name', 'like', "%{$warehouse}%");
        })
        ->when($item !== '', function ($query) use ($item) {
            $query->where('item.name', 'like', "%{$item}%");
        })
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('warehouse.name', 'like', "%{$q}%")
                    ->orWhere('item.name', 'like', "%{$q}%");
            });
        })
            
            ->selectRaw("
                warehouse.name AS warehouse,
                item.name AS item,
                item.recipeUom AS uom,
                SUM(scl.quantity) AS quantity,
                SUM(scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0))) AS totalCost
                
            ")
            ->groupBy('warehouse.name', 'item.name', 'item.recipeUom')
            ->havingRaw("
            SUM(scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0))) <> 0
            ")
            ->orderBy('warehouse.name')
            ->orderBy('item.name')
            ->get();

        // group for blade
        $byWarehouse = $rows->groupBy('warehouse');

        $grandTotal = $rows->sum('totalCost');

        return view('reports.consumption_warehouse.index', [
            'title' => 'Sales Consumption per Warehouse',
            'active' => 'reports-consumption-warehouse',
            'rows' => $rows,
            'start' => $start,
            'end' => $end,
            'q' => $q,
            'warehouse' => $warehouse,
            'item' => $item,
            'byWarehouse' => $byWarehouse,
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

        $warehouse = trim((string) $request->input('warehouse', ''));
        $item      = trim((string) $request->input('item', ''));
        $q         = trim((string) $request->input('q', ''));

        $rows = DB::connection('reports_mysql')->table('tbl_sales_consumptions as sc')
            ->join('tbl_sales_consumption_lines as scl', 'scl.sales_consumption_id', '=', 'sc.id')
            ->join('tbl_items as item', 'scl.item_id', '=', 'item.id')
            ->join('tbl_warehouses as warehouse', 'scl.warehouse_id', '=', 'warehouse.id')
            ->whereBetween('sc.dateIndex', [$dateIndexFrom, $dateIndexTo]) 
            ->whereBetween('sc.date', [$from, $to])
            ->selectRaw("
                warehouse.name AS warehouse,
                item.name AS item,
                item.recipeUom AS uom,
                SUM(scl.quantity) AS quantity,
                SUM(scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0))) AS totalCost
            ")
            ->when($warehouse !== '', function ($query) use ($warehouse) {
                $query->where('warehouse.name', 'like', "%{$warehouse}%");
            })
            ->when($item !== '', function ($query) use ($item) {
                $query->where('item.name', 'like', "%{$item}%");
            })
            ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
            $sub->where('warehouse.name', 'like', "%{$q}%")
            ->orWhere('item.name', 'like', "%{$q}%");
            });
            })
            ->havingRaw("
            SUM(scl.quantity * (scl.unitCost / NULLIF(item.inventoryToRecipeConversion,0))) <> 0
            ")
            ->groupBy('warehouse.name', 'item.name', 'item.recipeUom')
            ->orderBy('warehouse.name')
            ->orderBy('item.name')
            ->get();

        // filename follow filter date
        $fileName = "sales_consumption_warehouse_{$dateIndexFrom}_to_{$dateIndexTo}_{$start}_to_{$end}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Warehouse', 'Item', 'Quantity' , 'UOM',  'Total Cost']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->warehouse,
                    $r->item,
                    number_format((float)$r->quantity, 2, '.', ''),
                    $r->uom,
                    
                    number_format((float)$r->totalCost, 2, '.', ''),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}