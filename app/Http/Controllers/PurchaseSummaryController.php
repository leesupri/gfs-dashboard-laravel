<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseSummaryController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $category = trim((string) $request->get('category', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::connection('reports_mysql')->table('v_purchase_card')
            ->whereBetween('date', [$from, $to])
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', 'like', '%' . $category . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('category', 'like', '%' . $q . '%')
                        ->orWhere('name', 'like', '%' . $q . '%')
                        ->orWhere('code', 'like', '%' . $q . '%')
                        ->orWhere('uom', 'like', '%' . $q . '%');
                });
            });

        $rowsQuery = (clone $baseQuery)
            ->selectRaw('
                category,
                name,
                code,
                SUM(quantity) as quantity,
                uom,
                SUM(quantity * unitCost) as totalCost
            ')
            ->groupBy('category', 'name', 'code', 'uom');

        if ($export === 'csv') {
            return $this->exportCsv(clone $rowsQuery);
        }

        $rows = (clone $rowsQuery)
            ->orderBy('category')
            ->orderBy('name')
            ->orderBy('code')
            ->paginate(100)
            ->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as raw_lines,
                SUM(quantity * unitCost) as grand_total
            ')
            ->first();

        return view('reports.purchase-summary', [
            'title' => 'Purchase Summary Report',
            'rows' => $rows,
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    protected function exportCsv($rowsQuery)
    {
        $rows = $rowsQuery
            ->orderBy('category')
            ->orderBy('name')
            ->orderBy('code')
            ->get();

        $headers = ['Category', 'Item Name', 'Item Code', 'Quantity', 'UOM', 'Total Cost'];

        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [
                $row->category,
                $row->name,
                $row->code,
                (float) $row->quantity,
                $row->uom,
                (float) $row->totalCost,
            ];
        }

        return ExcelExport::download(
            'purchase-summary-' . now()->format('Ymd_His') . '.xlsx',
            'Purchase Summary Report',
            $headers,
            $dataRows
        );
    }
}