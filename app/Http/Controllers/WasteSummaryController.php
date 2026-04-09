<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteSummaryController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $export = $request->get('export');

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $rows = DB::connection('reports_mysql')
            ->table('v_waste_card')
            ->selectRaw('
                category,
                name,
                code,
                uom,
                unitCost,
                SUM(quantity) as quantity,
                SUM(quantity * unitCost) as total
            ')
            ->whereBetween('date', [$from, $to])
            ->groupBy('category', 'name', 'code', 'uom', 'unitCost')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Group like Jasper (category → items)
        $grouped = $rows->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'items' => $items,
                'total' => $items->sum('total'),
            ];
        })->values();

        $grandTotal = $rows->sum('total');

        if ($export === 'csv') {
            return $this->exportCsv($rows, $start, $end);
        }

        return view('reports.waste-summary', [
            'title' => 'Waste Summary Report',
            'grouped' => $grouped,
            'grandTotal' => $grandTotal,
            'start' => $start,
            'end' => $end,
        ]);
    }

    private function exportCsv($rows, $start, $end)
    {
        $filename = 'waste-summary-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $start, $end) {
            $handle = fopen('php://output', 'w');

            // Excel UTF-8 fix
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Waste Summary Report']);
            fputcsv($handle, ['Start', $start]);
            fputcsv($handle, ['End', $end]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Category',
                'Item Name',
                'Code',
                'Quantity',
                'UOM',
                'Unit Cost',
                'Total',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->category,
                    $row->name,
                    $row->code,
                    (float)$row->quantity,
                    $row->uom,
                    number_format($row->unitCost, 2),
                    number_format($row->total, 2),
                ]);
            }

            fclose($handle);
        }, $filename);
    }
}