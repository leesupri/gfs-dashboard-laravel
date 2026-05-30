<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoSalesController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = $this->fetchItemRows($from, $to);

        $totals = (object)[
            'quantity' => $rows->sum('quantity'),
            'netSales' => $rows->sum('netSales'),
            'disc'     => $rows->sum('disc'),
            'cost'     => $rows->sum('cost'),
            'profit'   => $rows->sum('profit'),
            'service'  => $rows->sum('service'),
            'tax'      => $rows->sum('tax'),
            'total'    => $rows->sum('total'),
        ];

        $net = (float)$totals->netSales - (float)$totals->disc;
        $totals->profitPct = $net > 0 ? ((float)$totals->profit / $net) * 100 : 0;

        $hasService = $totals->service > 0;
        $hasTax     = $totals->tax > 0;

        $grouped = $rows->groupBy('department')->map(fn($d) => $d->groupBy('category'));

        return view('no_sales.index', [
            'grouped'    => $grouped,
            'totals'     => $totals,
            'hasService' => $hasService,
            'hasTax'     => $hasTax,
            'from'       => $from,
            'to'         => $to,
            'filters'    => ['start' => $request->start, 'end' => $request->end],
        ]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $rows        = $this->fetchItemRows($from, $to);

        $headers = [
            'Department', 'Category', 'Description',
            'Qty', 'Net Sales', 'Discount', 'Cost',
            'Profit', 'Profit %', 'Service', 'Tax', 'Total',
        ];

        $dataRows = [];
        foreach ($rows as $r) {
            $dataRows[] = [
                $r->department,
                $r->category,
                $r->description,
                $r->quantity,
                $r->netSales,
                $r->disc,
                $r->cost,
                $r->profit,
                number_format($r->profitPct, 2) . '%',
                $r->service,
                $r->tax,
                $r->total,
            ];
        }

        return ExcelExport::download(
            'no_sales_items_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx',
            'No Sales Items',
            $headers,
            $dataRows
        );
    }

    /* ==================== HELPERS ==================== */

    private function dateRange(Request $request): array
    {
        $from = $request->filled('start')
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfDay();

        $to = $request->filled('end')
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function fetchItemRows(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')
            ->table('tbl_sales_lines as sl')
            ->join('tbl_sales as s',        'sl.sales_id',      '=', 's.id')
            ->join('tbl_items as i',        'sl.item_id',       '=', 'i.id')
            ->join('tbl_categories as cat', 'i.category_id',    '=', 'cat.id')
            ->join('tbl_departments as dep','cat.department_id','=', 'dep.id')
            ->whereBetween('s.date', [$from, $to])
            ->whereNull('s.invoice_id')
            ->where('s.voidCheck', 0)
            ->where('s.closed', 1)
            ->where(function ($q) {
                $q->where('i.noReport', '!=', 1)->orWhere('sl.unitPrice', '>', 0);
            })
            ->selectRaw('
                dep.name                                                                    AS department,
                cat.name                                                                    AS category,
                sl.description,
                SUM(sl.quantity)                                                            AS quantity,
                SUM(sl.quantity * sl.unitPrice)                                             AS netSales,
                SUM(sl.discountAmount)                                                      AS disc,
                SUM((sl.unitPrice - sl.unitCost) * sl.quantity) - SUM(sl.discountAmount)   AS profit,
                SUM(sl.quantity * sl.unitCost)                                              AS cost,
                SUM(sl.serviceChargeAmount)                                                 AS service,
                SUM(sl.tax1Amount)                                                          AS tax
            ')
            ->groupBy('dep.name', 'cat.name', 'sl.description')
            ->orderBy('dep.name')
            ->orderBy('cat.name')
            ->orderBy('sl.description')
            ->get()
            ->map(function ($r) {
                $net          = (float)$r->netSales - (float)$r->disc;
                $r->total     = $net + (float)$r->service + (float)$r->tax;
                $r->profitPct = $net > 0 ? ((float)$r->profit / $net) * 100 : 0;
                return $r;
            });
    }
}
