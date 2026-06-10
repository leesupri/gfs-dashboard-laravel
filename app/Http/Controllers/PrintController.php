<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PrinterStation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintController extends Controller
{
    /* ── Receipt ─────────────────────────────────────────────── */

    public function receipt(int $invoiceId)
    {
        $sale = DB::connection('reports_mysql')
            ->table('tbl_sales as s')
            ->leftJoin('tbl_employees as e', 's.closedBy_id', '=', 'e.id')
            ->leftJoin('tbl_customers as c', 's.customer_id', '=', 'c.id')
            ->where('s.invoice_id', $invoiceId)
            ->where('s.voidCheck', '!=', 1)
            ->selectRaw("
                s.invoice_id, s.date, s.closedTime, s.tableName, s.type, s.pax,
                s.subtotal, s.discountAmount, s.serviceChargeAmount,
                (s.tax1Amount + s.tax2Amount + s.tax3Amount) AS taxAmount,
                s.total, s.printCount,
                e.name AS cashier,
                c.name AS member
            ")
            ->first();

        abort_if(!$sale, 404, 'Invoice not found.');

        $items = DB::connection('reports_mysql')
            ->table('v_order_index')
            ->select(['id', 'invoice_id', 'created', 'salesType', 'description',
                      'quantity', 'unitPrice', 'discountAmount', 'department', 'category'])
            ->where('invoice_id', $invoiceId)
            ->whereRaw("COALESCE(NULLIF(TRIM(description),''),'') <> ''")
            ->where(function ($w) {
                $w->where('quantity', '!=', 0)
                  ->orWhere('unitPrice', '!=', 0)
                  ->orWhere('discountAmount', '!=', 0)
                  ->orWhereRaw("UPPER(COALESCE(category,'')) = 'MODIFIER'")
                  ->orWhereRaw("UPPER(COALESCE(department,'')) = 'MODIFIER'")
                  ->orWhereRaw("LEFT(TRIM(description),1) IN ('~','-')");
            })
            ->orderBy('created')->orderBy('id')
            ->get();

        $payments = DB::connection('reports_mysql')
            ->table('v_payment_index')
            ->selectRaw("
                CASE
                  WHEN paymentType='CASH' THEN 'CASH'
                  WHEN paymentType='CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                  WHEN paymentType='CARD' THEN 'EDC'
                  ELSE 'OTHER'
                END AS bucket,
                COALESCE(NULLIF(name,''), paymentType) AS method,
                ROUND(SUM(total), 2) AS amount
            ")
            ->where('invoice_id', $invoiceId)
            ->groupBy('bucket', 'method')
            ->orderByDesc('amount')
            ->get();

        $paidTotal = $payments->sum('amount');
        $change    = round($paidTotal - $sale->total, 2);

        return view('print.receipt', [
            'sale'      => $sale,
            'items'     => $items,
            'payments'  => $payments,
            'paidTotal' => $paidTotal,
            'change'    => $change,
            'logo'      => $this->logoUrl(),
            'title'     => 'Receipt #' . $invoiceId,
            'dateRange' => Carbon::parse($sale->date)->format('d M Y'),
        ]);
    }

    /* ── Item Sales ──────────────────────────────────────────── */

    public function itemSales(Request $request)
    {
        $from  = $request->start ? Carbon::parse($request->start)->startOfDay() : now()->startOfDay();
        $to    = $request->end   ? Carbon::parse($request->end)->endOfDay()     : now()->endOfDay();
        $diFrom = (int) $from->format('Ym');
        $diTo   = (int) $to->format('Ym');

        $temp = DB::connection('reports_mysql')->table('tbl_sales as s')
            ->join('tbl_invoices as inv', 's.invoice_id', '=', 'inv.id')
            ->join('tbl_sales_lines as sl', 's.id', '=', 'sl.sales_id')
            ->whereBetween('s.dateIndex', [$diFrom, $diTo])
            ->whereBetween('s.date', [$from, $to])
            ->where('s.voidCheck', 0)
            ->where('sl.quantity', '>', 0)
            ->groupBy('sl.item_id')
            ->selectRaw('
                sl.item_id,
                SUM(sl.quantity - sl.voidQuantity)                         AS quantity,
                SUM((sl.quantity - sl.voidQuantity) * sl.unitPrice)        AS subtotal,
                SUM((sl.quantity - sl.voidQuantity) * sl.unitCost)         AS cost,
                SUM(sl.discountAmount)                                     AS discount
            ');

        $rows = DB::connection('reports_mysql')->table('tbl_items as i')
            ->leftJoinSub($temp, 't', 'i.id', '=', 't.item_id')
            ->leftJoin('tbl_categories as c', 'i.category_id', '=', 'c.id')
            ->leftJoin('tbl_departments as d', 'c.department_id', '=', 'd.id')
            ->where('i.sales', 1)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(t.quantity, 0) != 0')
                  ->orWhereRaw('COALESCE(t.subtotal, 0) != 0');
            })
            ->selectRaw('
                d.name AS department,
                c.name AS category,
                i.name,
                COALESCE(t.quantity, 0) AS quantity,
                COALESCE(t.subtotal, 0) AS subtotal,
                COALESCE(t.cost,     0) AS cost,
                COALESCE(t.discount, 0) AS discount
            ')
            ->orderBy('d.name')->orderBy('c.name')->orderBy('i.name')
            ->get();

        $byDept = $rows->groupBy(fn ($r) => $r->department ?: 'NO DEPARTMENT')
                       ->map(fn ($d) => $d->groupBy(fn ($r) => $r->category ?: 'UNCATEGORIZED'));

        $totals = [
            'qty'      => $rows->sum('quantity'),
            'subtotal' => $rows->sum('subtotal'),
            'cost'     => $rows->sum('cost'),
            'discount' => $rows->sum('discount'),
        ];
        $totals['net']     = $totals['subtotal'] - $totals['discount'];
        $totals['costPct'] = $totals['net'] > 0 ? ($totals['cost'] / $totals['net']) * 100 : 0;

        return view('print.item-sales', [
            'byDept'    => $byDept,
            'totals'    => $totals,
            'logo'      => $this->logoUrl(),
            'title'     => 'Item Sales',
            'dateRange' => $from->format('d M Y') . ($from->isSameDay($to) ? '' : ' – ' . $to->format('d M Y')),
        ]);
    }

    /* ── Summary Sales ───────────────────────────────────────── */

    public function summarySales(Request $request)
    {
        $from = $request->start ? Carbon::parse($request->start)->startOfDay() : now()->startOfDay();
        $to   = $request->end   ? Carbon::parse($request->end)->endOfDay()     : now()->endOfDay();

        $summary = DB::connection('reports_mysql')->table('tbl_sales')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('invoice_id')
            ->where('closed', 1)->where('voidCheck', 0)
            ->selectRaw('
                COALESCE(SUM(subtotal), 0)                              AS subtotal,
                COALESCE(SUM(discountAmount), 0)                        AS discount,
                COALESCE(SUM(serviceChargeAmount), 0)                   AS service,
                COALESCE(SUM(tax1Amount+tax2Amount+tax3Amount), 0)      AS tax,
                COALESCE(SUM(roundingAmount), 0)                        AS rounding,
                COALESCE(SUM(total), 0)                                 AS total,
                COUNT(*)                                                AS trxCount
            ')
            ->first();

        $payments = DB::connection('reports_mysql')->table('v_payment_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('name, COUNT(*) AS qty, SUM(amount) AS amount')
            ->groupBy('name')->orderBy('name')
            ->get();
        $payTotal = $payments->sum('amount');
        $payments = $payments->map(function ($r) use ($payTotal) {
            $r->pct = $payTotal > 0 ? ($r->amount / $payTotal) * 100 : 0;
            return $r;
        });

        $departments = DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('department, SUM(quantity) AS qty, SUM(quantity * unitPrice) AS price')
            ->groupBy('department')->orderBy('price', 'desc')
            ->get();
        $deptTotal = $departments->sum('price');
        $departments = $departments->map(function ($r) use ($deptTotal) {
            $r->pct = $deptTotal > 0 ? ($r->price / $deptTotal) * 100 : 0;
            return $r;
        });

        $profit = DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                SUM(quantity * unitPrice)                                    AS subtotal,
                SUM(discountAmount)                                          AS discount,
                SUM((quantity * unitPrice) - discountAmount)                 AS netSales,
                SUM(quantity * unitCost)                                     AS totalCost,
                SUM(((quantity*unitPrice)-discountAmount)-(quantity*unitCost)) AS profit
            ')
            ->first();
        $profit->costPct = $profit->netSales > 0 ? ($profit->totalCost / $profit->netSales) * 100 : 0;

        return view('print.summary-sales', [
            'summary'     => $summary,
            'payments'    => $payments,
            'departments' => $departments,
            'profit'      => $profit,
            'logo'        => $this->logoUrl(),
            'title'       => 'Summary Sales',
            'dateRange'   => $from->format('d M Y') . ($from->isSameDay($to) ? '' : ' – ' . $to->format('d M Y')),
        ]);
    }

    /* ── Auto Cut ───────────────────────────────────────────── */

    public function sendCut(PrinterStation $station)
    {
        if (!$station->ip_address) {
            return response()->json(['ok' => false, 'error' => 'No IP address configured for this station.'], 422);
        }

        try {
            $socket = @fsockopen($station->ip_address, 9100, $errno, $errstr, 3);
            if (!$socket) {
                return response()->json(['ok' => false, 'error' => "Cannot connect to {$station->ip_address}: {$errstr}"], 503);
            }
            // ESC/POS GS V 65 0 — full cut with paper feed
            fwrite($socket, "\x1d\x56\x41\x00");
            fclose($socket);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ── Test Printer ───────────────────────────────────────── */

    public function testPrinter(PrinterStation $station)
    {
        return view('print.test', [
            'station'   => $station,
            'logo'      => $this->logoUrl(),
            'title'     => 'Test Print',
            'dateRange' => now()->format('d M Y H:i:s'),
        ]);
    }

    /* ── Helper ──────────────────────────────────────────────── */

    private function logoUrl(): ?string
    {
        $path = AppSetting::get('logo_path');
        return $path ? asset('storage/' . $path) : null;
    }
}
