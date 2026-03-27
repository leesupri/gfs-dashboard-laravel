<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $baseQuery = DB::table('v_production_card')
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
            return $this->exportCsv(clone $rowsQuery, $start, $end, $category, $warehouse, $q);
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

    protected function exportCsv($rowsQuery, $start, $end, $category, $warehouse, $q): StreamedResponse
    {
        $filename = 'production-summary-' . now()->format('Ymd_His') . '.csv';

        $rows = $rowsQuery
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        return response()->streamDownload(function () use ($rows, $start, $end, $category, $warehouse, $q) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Production Summary Report']);
            fputcsv($handle, ['Start Date', $start]);
            fputcsv($handle, ['End Date', $end]);
            fputcsv($handle, ['Category Filter', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Warehouse Filter', $warehouse !== '' ? $warehouse : 'All']);
            fputcsv($handle, ['Search Filter', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Category',
                'Item Name',
                'Item Code',
                'Quantity',
                'UOM',
                'Warehouse',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->category,
                    $row->item_name,
                    $row->item_code,
                    number_format((float) $row->quantity, 2, '.', ''),
                    $row->uom,
                    $row->warehouse,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}