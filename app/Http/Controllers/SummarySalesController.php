<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummarySalesController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->start
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfDay();

        $to = $request->end
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfDay();

        return view('summary_sales.index', [
            'summary'   => $this->summaryTotals($from, $to),
            'department'=> $this->departmentSummary($from, $to),
            'category'  => $this->categorySummary($from, $to),
            'payments'  => $this->paymentSummary($from, $to),
            'profit'    => $this->profitSummary($from, $to),
            'stats'     => $this->visitorStats($from, $to),
            'voids'     => $this->voidSummary($from, $to),
            'noSales'   => $this->noSalesSummary($from, $to),
            'filters'   => compact('from','to')
        ]);
    }

    /* ===================== EXCEL EXPORT ===================== */
    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $summary     = $this->summaryTotals($from, $to);
        $payments    = $this->paymentSummary($from, $to);
        $departments = $this->departmentSummary($from, $to);
        $categories  = $this->categorySummary($from, $to);
        $profit      = $this->profitSummary($from, $to);
        $stats       = $this->visitorStats($from, $to);
        $voids       = $this->voidSummary($from, $to);
        $noSales     = $this->noSalesSummary($from, $to);

        // Build SUMMARY SALES section as key-value rows
        $summaryRows = [
            ['Subtotal',  $summary->subtotal],
            ['Discount',  $summary->discount],
            ['Net Sales', $summary->subtotal - $summary->discount],
        ];
        if ($summary->service > 0)   { $summaryRows[] = ['Service Charge', $summary->service]; }
        if ($summary->tax > 0)       { $summaryRows[] = ['Tax',            $summary->tax]; }
        if ($summary->rounding != 0) { $summaryRows[] = ['Rounding',       $summary->rounding]; }
        $summaryRows[] = ['TOTAL', $summary->total];

        // PAYMENT rows
        $paymentRows = $payments->map(fn ($p) => [
            $p->name, $p->qty, $p->amount, round($p->percentage, 2) . '%',
        ])->values()->toArray();

        // DEPARTMENT rows
        $deptRows = $departments->map(fn ($d) => [
            $d->department, $d->qty, $d->price, round($d->percent, 2) . '%',
        ])->values()->toArray();

        // CATEGORY rows
        $catRows = $categories->map(fn ($c) => [
            $c->category, $c->qty, $c->price, round($c->percent, 2) . '%',
        ])->values()->toArray();

        // VOID rows
        $voidRows = $voids->map(fn ($v) => [
            $v->description, $v->qty, $v->price,
        ])->values()->toArray();

        // NO SALES rows
        $noSalesRows = $noSales->map(fn ($n) => [
            $n->name, $n->qty, $n->amount,
        ])->values()->toArray();
        if ($noSales->isNotEmpty()) {
            $noSalesRows[] = ['Total', $noSales->sum('qty'), $noSales->sum('amount')];
        }

        // PROFIT & LOSS rows
        $profitRows = [
            ['Subtotal',   $profit->subtotal],
            ['Discount',   $profit->discount],
            ['Net Sales',  $profit->netSales],
            ['Total Cost', $profit->totalCost],
            ['Cost %',     round($profit->costPercent, 2) . '%'],
            ['Profit',     $profit->profit],
        ];

        // VISITOR STATISTIC rows
        $statsRows = [
            ['Total Sales',      $stats->ttl],
            ['Total Guest',      $stats->guest],
            ['Avg / Guest',      $stats->avgGuest],
            ['Transactions',     $stats->trans],
            ['Avg / Transaction',$stats->avgTrans],
        ];

        $sections = [
            ['heading' => 'SUMMARY SALES',    'headers' => ['Item', 'Value'],                    'rows' => $summaryRows],
            ['heading' => 'PAYMENT',          'headers' => ['Method', 'Qty', 'Amount', 'Percent'], 'rows' => $paymentRows],
            ['heading' => 'DEPARTMENT',       'headers' => ['Department', 'Qty', 'Sales', 'Percent'], 'rows' => $deptRows],
            ['heading' => 'CATEGORY',         'headers' => ['Category', 'Qty', 'Sales', 'Percent'],   'rows' => $catRows],
            ['heading' => 'VOID',             'headers' => ['Reason', 'Qty', 'Amount'],           'rows' => $voidRows],
            ['heading' => 'NO SALES',         'headers' => ['Cashier', 'Count', 'Amount'],        'rows' => $noSalesRows],
            ['heading' => 'PROFIT & LOSS',    'headers' => ['Item', 'Value'],                    'rows' => $profitRows],
            ['heading' => 'VISITOR STATISTIC','headers' => ['Item', 'Value'],                    'rows' => $statsRows],
        ];

        return ExcelExport::downloadMultiSection(
            'summary_sales_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx',
            'Summary Sales',
            $sections
        );
    }

    /* ===================== HELPERS ===================== */

    private function dateRange(Request $request)
{
    $from = $request->filled('start')
        ? Carbon::parse($request->start)->startOfDay()
        : now()->startOfDay();

    $to = $request->filled('end')
        ? Carbon::parse($request->end)->endOfDay()
        : now()->endOfDay();

    return [$from, $to];
}

    private function summaryTotals($from,$to)
    {
        return DB::connection('reports_mysql')->table('tbl_sales')
            ->whereBetween('date', [$from,$to])
            ->whereNotNull('invoice_id')
            ->where('closed',1)
            ->where('voidCheck',0)
            ->selectRaw('
                COALESCE(SUM(subtotal),0) subtotal,
                COALESCE(SUM(discountAmount),0) discount,
                COALESCE(SUM(serviceChargeAmount),0) service,
                COALESCE(SUM(tax1Amount + tax2Amount + tax3Amount),0) tax,
                COALESCE(SUM(roundingAmount),0) rounding,
                COALESCE(SUM(total),0) total
            ')
            ->first();
    }
    private function departmentSummary($from, $to)
{
    $rows = DB::connection('reports_mysql')->table('v_order_index')
        ->whereBetween('date', [$from, $to])
        ->selectRaw('
            department,
            SUM(quantity) qty,
            SUM(quantity * unitPrice) price
        ')
        ->groupBy('department')
        ->get();

    $total = $rows->sum('price');

    return $rows->map(fn($r) => tap($r, function ($x) use ($total) {
        $x->percent = $total > 0 ? ($x->price / $total) * 100 : 0;
    }));
}

    private function paymentSummary($from, $to)
{
    $rows = DB::connection('reports_mysql')->table('v_payment_index')
        ->whereBetween('date', [$from, $to])
        ->selectRaw('
            name,
            COUNT(*) as qty,
            SUM(amount) as amount,
            SUM(subtotal) as subtotal
        ')
        ->groupBy('name')
        ->orderBy('name')
        ->get();

    $totalAmount = $rows->sum('amount');

    return $rows->map(function ($r) use ($totalAmount) {
        $r->percentage = $totalAmount > 0
            ? ($r->amount / $totalAmount) * 100
            : 0;
        return $r;
    });
}

    private function profitSummary($from,$to)
    {
        $row = DB::connection('reports_mysql')->table('v_order_index')
        ->whereBetween('date', [$from, $to])
        ->selectRaw('
            SUM(quantity * unitPrice) subtotal,
            SUM(discountAmount) discount,
            SUM((quantity * unitPrice) - discountAmount) netSales,
            SUM(quantity * unitCost) totalCost,
            SUM(((quantity * unitPrice)-discountAmount)-(quantity*unitCost)) profit
        ')
        ->first();

    $row->costPercent = $row->netSales > 0
        ? ($row->totalCost / $row->netSales) * 100
        : 0;

    return $row;
    }

   private function voidSummary($from, $to)
{
    return DB::connection('reports_mysql')->table('v_void_order_index')
        ->whereBetween('date', [$from, $to])
        ->selectRaw('
            description,
            SUM(quantity) qty,
            SUM(quantity * unitPrice) price
        ')
        ->groupBy('description')
        ->orderBy('description')
        ->get();
}

    private function categorySummary($from, $to)
{
    $rows = DB::connection('reports_mysql')->table('v_order_index')
        ->whereBetween('date', [$from, $to])
        ->whereNotNull('invoice_id')
        ->selectRaw('
            category,
            SUM(quantity) qty,
            SUM(quantity * unitPrice) price
        ')
        ->groupBy('category')
        ->get();

    $total = $rows->sum('price');

    return $rows->map(fn($r) => tap($r, function ($x) use ($total) {
        $x->percent = $total > 0 ? ($x->price / $total) * 100 : 0;
    }));
}
private function visitorStats($from, $to)
{
    return DB::connection('reports_mysql')->table('v_sales_index')
        ->whereBetween('date', [$from, $to])
        ->selectRaw('
            SUM(total) ttl,
            SUM(pax) guest,
            SUM(total) / NULLIF(SUM(pax),0) avgGuest,
            COUNT(*) trans,
            SUM(total) / NULLIF(COUNT(*),0) avgTrans
        ')
        ->first();
}

private function noSalesSummary($from, $to)
{
    return DB::connection('reports_mysql')->table('v_nosales')
        ->whereBetween('date', [$from, $to])
        ->where('nosales', true)
        ->selectRaw('COUNT(*) AS qty, name, SUM(amount) AS amount')
        ->groupBy('name')
        ->havingRaw('SUM(amount) <> 0')
        ->orderBy('name')
        ->get();
}
}
