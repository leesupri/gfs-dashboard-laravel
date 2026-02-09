<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            'filters'   => compact('from','to')
        ]);
    }

    /* ===================== CSV EXPORT ===================== */
    public function exportCsv(Request $request): StreamedResponse
{
    [$from, $to] = $this->dateRange($request);

    // Prepare all data OUTSIDE the stream
    $summary     = $this->summaryTotals($from, $to);
    $payments    = $this->paymentSummary($from, $to);
    $departments = $this->departmentSummary($from, $to);
    $categories  = $this->categorySummary($from, $to);
    $profit      = $this->profitSummary($from, $to);
    $stats       = $this->visitorStats($from, $to);
    $voids       = $this->voidSummary($from, $to);
    $filename = 'summary_sales_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

    return response()->streamDownload(function () use (
        $summary,
        $payments,
        $departments,
        $categories,
        $profit,
        $stats,
        $voids
    ) {
        $out = fopen('php://output', 'w');

        // UTF-8 BOM (Excel safe)
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        /* ================= SUMMARY ================= */
        fputcsv($out, ['SUMMARY SALES']);
        fputcsv($out, ['Subtotal', $summary->subtotal]);
        fputcsv($out, ['Discount', $summary->discount]);
        fputcsv($out, ['Net Sales', $summary->subtotal - $summary->discount]);

        if ($summary->service > 0) {
            fputcsv($out, ['Service Charge', $summary->service]);
        }

        if ($summary->tax > 0) {
            fputcsv($out, ['Tax', $summary->tax]);
        }

        if ($summary->rounding != 0) {
            fputcsv($out, ['Rounding', $summary->rounding]);
        }

        fputcsv($out, ['TOTAL', $summary->total]);
        fputcsv($out, []);

        /* ================= PAYMENT ================= */
        fputcsv($out, ['PAYMENT']);
        fputcsv($out, ['Method', 'Qty', 'Amount', 'Percent']);

        foreach ($payments as $p) {
            fputcsv($out, [
                $p->name,
                $p->qty,
                $p->amount,
                round($p->percentage, 2) . '%'
            ]);
        }
        fputcsv($out, []);

        /* ================= DEPARTMENT ================= */
        fputcsv($out, ['DEPARTMENT']);
        fputcsv($out, ['Department', 'Qty', 'Sales', 'Percent']);

        foreach ($departments as $d) {
            fputcsv($out, [
                $d->department,
                $d->qty,
                $d->price,
                round($d->percent, 2) . '%'
            ]);
        }
        fputcsv($out, []);

        /* ================= CATEGORY ================= */
        fputcsv($out, ['CATEGORY']);
        fputcsv($out, ['Category', 'Qty', 'Sales', 'Percent']);

        foreach ($categories as $c) {
            fputcsv($out, [
                $c->category,
                $c->qty,
                $c->price,
                round($c->percent, 2) . '%'
            ]);
        }
        fputcsv($out, []);

        /* ================= PROFIT & LOSS ================= */
        fputcsv($out, ['PROFIT & LOSS']);
        fputcsv($out, ['Subtotal', $profit->subtotal]);
        fputcsv($out, ['Discount', $profit->discount]);
        fputcsv($out, ['Net Sales', $profit->netSales]);
        fputcsv($out, ['Total Cost', $profit->totalCost]);
        fputcsv($out, ['Cost %', round($profit->costPercent, 2) . '%']);
        fputcsv($out, ['Profit', $profit->profit]);
        fputcsv($out, []);

        /* ================= VISITOR STAT ================= */
        fputcsv($out, ['VISITOR STATISTIC']);
        fputcsv($out, ['Total Sales', $stats->ttl]);
        fputcsv($out, ['Total Guest', $stats->guest]);
        fputcsv($out, ['Avg / Guest', $stats->avgGuest]);
        fputcsv($out, ['Transactions', $stats->trans]);
        fputcsv($out, ['Avg / Transaction', $stats->avgTrans]);
        fputcsv($out, []);

        /* ================= VOID ================= */
        fputcsv($out, ['VOID']);
        fputcsv($out, ['Reason', 'Qty', 'Amount']);

        foreach ($voids as $v) {
            fputcsv($out, [
                $v->description,
                $v->qty,
                $v->price
            ]);
        }

        fclose($out);
    },  $filename );
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
        return DB::table('tbl_sales')
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
    $rows = DB::table('v_order_index')
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
    $rows = DB::table('v_payment_index')
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
        $row = DB::table('v_order_index')
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
    return DB::table('v_void_order_index')
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
    $rows = DB::table('v_order_index')
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
    return DB::table('v_sales_index')
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
}
