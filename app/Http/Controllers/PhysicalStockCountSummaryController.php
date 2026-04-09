<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhysicalStockCountSummaryController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $warehouse = trim((string) $request->get('warehouse', ''));
        $category = trim((string) $request->get('category', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::connection('reports_mysql')
            ->table('tbl_adjustment_lines as al')
            ->join('tbl_adjustment as a', 'al.adjustment_id', '=', 'a.id')
            ->join('tbl_items as i', 'al.item_id', '=', 'i.id')
            ->join('tbl_categories as c', 'i.category_id', '=', 'c.id')
            ->join('tbl_warehouses as w', 'a.warehouse_id', '=', 'w.id')
            ->whereBetween('a.date', [$from, $to])
            ->when($warehouse !== '', function ($query) use ($warehouse) {
                $query->where('w.name', 'like', '%' . $warehouse . '%');
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('c.name', 'like', '%' . $category . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('i.name', 'like', '%' . $q . '%')
                        ->orWhere('i.code', 'like', '%' . $q . '%')
                        ->orWhere('c.name', 'like', '%' . $q . '%')
                        ->orWhere('w.name', 'like', '%' . $q . '%');
                });
            });

        $rowsQuery = (clone $baseQuery)
            ->selectRaw("
                w.name as warehouse,
                c.name as category,
                i.name as item_name,
                i.code as item_code,
                i.uom as uom,
                SUM(al.calculated) as calculated,
                SUM(al.actual) as actual,
                SUM(al.actual - al.calculated) as diff,
                CASE
                    WHEN SUM(al.calculated) = 0 THEN 0
                    ELSE SUM(al.actual - al.calculated) / SUM(al.calculated)
                END as variances,
                SUM((al.actual - al.calculated) * al.unitCost) as diff_cost
            ")
            ->groupBy('w.name', 'c.name', 'i.name', 'i.code', 'i.uom');

        if ($export === 'csv') {
            $rows = (clone $rowsQuery)
                ->orderBy('warehouse')
                ->orderBy('category')
                ->orderBy('item_name')
                ->get();

            return $this->exportCsv($rows, $start, $end, $warehouse, $category, $q);
        }

        $rows = (clone $rowsQuery)
            ->orderBy('warehouse')
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        $groupedRows = $rows->groupBy('warehouse')->map(function ($warehouseItems, $warehouseName) {
            $categoryGroups = $warehouseItems->groupBy('category')->map(function ($categoryItems, $categoryName) {
                return [
                    'category' => $categoryName,
                    'items' => $categoryItems,
                    'subtotal_diff_cost' => $categoryItems->sum(fn ($row) => (float) $row->diff_cost),
                ];
            })->values();

            return [
                'warehouse' => $warehouseName,
                'categories' => $categoryGroups,
                'subtotal_diff_cost' => $warehouseItems->sum(fn ($row) => (float) $row->diff_cost),
            ];
        })->values();

        $summary = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as raw_lines,
                SUM((al.actual - al.calculated) * al.unitCost) as grand_diff_cost
            ")
            ->first();

        return view('reports.physical-stock-count-summary', [
            'title' => 'Physical Stock Count Summary Group By Warehouse',
            'groupedRows' => $groupedRows,
            'start' => $start,
            'end' => $end,
            'warehouse' => $warehouse,
            'category' => $category,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    private function exportCsv($rows, $start, $end, $warehouse, $category, $q): StreamedResponse
    {
        $filename = 'physical-stock-count-summary-' . now()->format('Ymd_His') . '.csv';

        $groupedRows = $rows->groupBy('warehouse');
        $grandTotal = $rows->sum(fn ($row) => (float) $row->diff_cost);

        return response()->streamDownload(function () use ($groupedRows, $grandTotal, $start, $end, $warehouse, $category, $q) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Physical Stock Count Summary Group By Warehouse']);
            fputcsv($handle, ['Start Date', $start]);
            fputcsv($handle, ['End Date', $end]);
            fputcsv($handle, ['Warehouse Filter', $warehouse !== '' ? $warehouse : 'All']);
            fputcsv($handle, ['Category Filter', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Search Filter', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            foreach ($groupedRows as $warehouseName => $items) {
                fputcsv($handle, [$warehouseName]);

                fputcsv($handle, [
                    'Category',
                    'Item Name',
                    'Item Code',
                    'UOM',
                    'Calculated',
                    'Actual',
                    'Diff',
                    'Variance',
                    'Diff Cost',
                ]);

                foreach ($items as $row) {
                    fputcsv($handle, [
                        $row->category,
                        $row->item_name,
                        $row->item_code,
                        $row->uom,
                        (float) $row->calculated,
                        (float) $row->actual,
                        (float) $row->diff,
                        (float) $row->variances,
                        (float) $row->diff_cost,
                    ]);
                }

                $warehouseSubtotal = $items->sum(fn ($row) => (float) $row->diff_cost);

                fputcsv($handle, [
                    '',
                    'SUBTOTAL ' . $warehouseName,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    (float) $warehouseSubtotal,
                ]);

                fputcsv($handle, []);
            }

            fputcsv($handle, [
                '',
                'GRAND TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                (float) $grandTotal,
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}