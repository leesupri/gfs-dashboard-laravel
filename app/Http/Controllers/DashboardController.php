<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $start  = $request->query('start', now()->toDateString());
        $end    = $request->query('end',   now()->toDateString());
        $from   = Carbon::parse($start)->startOfDay();
        $to     = Carbon::parse($end)->endOfDay();
        $diFrom = (int) $from->format('Ym');
        $diTo   = (int) $to->format('Ym');

        $salesBase = DB::connection('reports_mysql')->table('tbl_sales')
            ->where('closed', 1)->where('voidCheck', 0)->whereNotNull('invoice_id')
            ->whereBetween('dateIndex', [$diFrom, $diTo])
            ->whereBetween('date', [$from, $to]);

        $kpi         = $this->kpi($salesBase);
        $days        = max(1, $from->diffInDays($to) + 1);
        $dateRange   = $this->dailyTrend($salesBase, $from, $days);
        $hourlySales = $this->hourlySales($salesBase);
        $byDept      = $this->byDept($from, $to);
        $byCategory  = $this->byCategory($from, $to);
        $topProducts = $this->topProducts($from, $to);
        $paymentRows = $this->payments($from, $to);
        $outletRows  = $this->outlets($salesBase);

        return view('dashboard.index', compact(
            'start', 'end', 'kpi', 'dateRange', 'days',
            'byDept', 'byCategory', 'topProducts',
            'hourlySales', 'paymentRows', 'outletRows'
        ));
    }

    private function kpi($base)
    {
        return (clone $base)->selectRaw("
            COUNT(*)                                       AS trx,
            COALESCE(SUM(subtotal), 0)                     AS grossSales,
            COALESCE(SUM(discountAmount), 0)               AS discount,
            COALESCE(SUM(subtotal - discountAmount), 0)    AS netSales,
            COALESCE(SUM(total), 0)                        AS total,
            COALESCE(AVG(total), 0)                        AS avgTicket,
            COALESCE(SUM(pax), 0)                          AS totalPax,
            COALESCE(SUM(total) / NULLIF(SUM(pax), 0), 0) AS avgPerPax
        ")->first();
    }

    private function dailyTrend($base, Carbon $from, int $days)
    {
        $raw = (clone $base)
            ->selectRaw("DATE(date) AS day, COUNT(*) AS trx, COALESCE(SUM(total),0) AS total")
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $range = collect();
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $range->push([
                'label' => Carbon::parse($d)->format('d M'),
                'trx'   => (int)   ($raw[$d]->trx   ?? 0),
                'total' => (float) ($raw[$d]->total  ?? 0),
            ]);
        }
        return $range;
    }

    private function hourlySales($base)
    {
        $raw = (clone $base)
            ->selectRaw("HOUR(closedTime) AS hr, COUNT(*) AS trx, COALESCE(SUM(total),0) AS total")
            ->groupBy('hr')->orderBy('hr')->get()->keyBy('hr');

        return collect(range(0, 23))->map(fn($h) => [
            'label' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
            'trx'   => (int)   ($raw[$h]->trx   ?? 0),
            'total' => (float) ($raw[$h]->total  ?? 0),
        ]);
    }

    private function byDept(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("COALESCE(NULLIF(department,''),'OTHER') AS name,
                COALESCE(SUM(quantity*unitPrice),0) AS total, COALESCE(SUM(quantity),0) AS qty")
            ->groupByRaw("COALESCE(NULLIF(department,''),'OTHER')")
            ->orderByDesc('total')->get();
    }

    private function byCategory(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("COALESCE(NULLIF(category,''),'OTHER') AS name,
                COALESCE(SUM(quantity*unitPrice),0) AS total, COALESCE(SUM(quantity),0) AS qty")
            ->groupByRaw("COALESCE(NULLIF(category,''),'OTHER')")
            ->orderByDesc('total')->limit(10)->get();
    }

    private function topProducts(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('description')->where('description', '!=', '')
            ->selectRaw("description AS name,
                COALESCE(SUM(quantity),0) AS qty, COALESCE(SUM(quantity*unitPrice),0) AS total")
            ->groupBy('description')->orderByDesc('qty')->limit(10)->get();
    }

    private function payments(Carbon $from, Carbon $to)
    {
        $case = "CASE
            WHEN paymentType='CASH'                         THEN 'CASH'
            WHEN paymentType='CARD' AND name LIKE '%QRIS%'  THEN 'QRIS'
            WHEN paymentType='CARD'                         THEN 'EDC'
            ELSE 'OTHER' END";

        return DB::connection('reports_mysql')->table('v_payment_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("{$case} AS bucket, COUNT(*) AS trx, COALESCE(SUM(amount),0) AS total")
            ->groupByRaw($case)->orderByDesc('total')->get();
    }

    private function outlets($base)
    {
        $case = "CASE
            WHEN tableName LIKE 'HALL%'   THEN 'HALL'
            WHEN tableName LIKE 'Balkon%' THEN 'BALKON'
            WHEN tableName LIKE 'VIP%'    THEN 'VIP'
            ELSE 'OTHER' END";

        $sub = (clone $base)->selectRaw("{$case} AS outlet, total");

        return DB::connection('reports_mysql')->query()
            ->fromSub($sub, 'x')
            ->selectRaw("outlet, COUNT(*) AS trx, COALESCE(SUM(total),0) AS total")
            ->groupBy('outlet')->orderByDesc('total')->get();
    }
}
