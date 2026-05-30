<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionSummaryController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $category = trim((string) $request->get('category', ''));
        $warehouse = trim((string) $request->get('warehouse', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::connection('reports_mysql')->table('v_production_card')
            ->whereBetween('date', [$from, $to])
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', 'like', '%' . $category . '%');
            })
            ->when($warehouse !== '', function ($query) use ($warehouse) {
                $query->where('warehouse', 'like', '%' . $warehouse . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', '%' . $q . '%')
                        ->orWhere('code', 'like', '%' . $q . '%')
                        ->orWhere('category', 'like', '%' . $q . '%')
                        ->orWhere('uom', 'like', '%' . $q . '%')
                        ->orWhere('warehouse', 'like', '%' . $q . '%');
                });
            });

        $rowsQuery = (clone $baseQuery)
            ->selectRaw('
                category,
                name as item_name,
                code as item_code,
                SUM(quantity) as quantity,
                uom,
                warehouse
            ')
            ->groupBy('category', 'name', 'code', 'uom', 'warehouse');

        if ($export === 'csv') {
            return $this->exportCsv(clone $rowsQuery);
        }

        $rows = (clone $rowsQuery)
            ->orderBy('category')
            ->orderBy('item_name')
            ->paginate(100)
            ->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(DISTINCT CONCAT_WS("|", category, name, code, uom, warehouse)) as total_lines,
                SUM(quantity) as total_quantity
            ')
            ->first();

        return view('reports.production-summary', [
            'title' => 'Production Summary Report',
            'rows' => $rows,
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'warehouse' => $warehouse,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    protected function exportCsv($rowsQuery)
    {
        $rows = $rowsQuery
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        $headers = ['Category', 'Item Name', 'Item Code', 'Quantity', 'UOM', 'Warehouse'];

        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [
                $row->category,
                $row->item_name,
                $row->item_code,
                (float) $row->quantity,
                $row->uom,
                $row->warehouse,
            ];
        }

        return ExcelExport::download(
            'production-summary-' . now()->format('Ymd_His') . '.xlsx',
            'Production Summary Report',
            $headers,
            $dataRows
        );
    }
}