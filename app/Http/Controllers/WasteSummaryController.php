<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
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
            return $this->exportCsv($rows);
        }

        return view('reports.waste-summary', [
            'title' => 'Waste Summary Report',
            'grouped' => $grouped,
            'grandTotal' => $grandTotal,
            'start' => $start,
            'end' => $end,
        ]);
    }

    private function exportCsv($rows)
    {
        $headers = ['Category', 'Item Name', 'Code', 'Quantity', 'UOM', 'Unit Cost', 'Total'];

        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [
                $row->category,
                $row->name,
                $row->code,
                (float) $row->quantity,
                $row->uom,
                (float) $row->unitCost,
                (float) $row->total,
            ];
        }

        return ExcelExport::download(
            'waste-summary-' . now()->format('Ymd_His') . '.xlsx',
            'Waste Summary Report',
            $headers,
            $dataRows
        );
    }
}