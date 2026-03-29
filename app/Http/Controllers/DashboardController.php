<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start', now()->toDateString());
        $end   = $request->query('end', now()->toDateString());

        $from = $start . ' 00:00:00';
        $to   = $end . ' 23:59:59';

        /* =========================
         * KPI
         * ========================= */
        $kpi = DB::connection('reports_mysql')->table('tbl_sales')
            ->where('closed', 1)
            ->where('voidCheck', 0)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                COUNT(*) as trx,
                COALESCE(SUM(total),0) as sales,
                COALESCE(AVG(total),0) as avgTicket
            ")
            ->first();

        /* =========================
         * Department Sales
         * ========================= */
        $byDept = DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('created', [$from, $to])
            ->whereRaw("COALESCE(NULLIF(TRIM(description),''),'') <> ''")
            ->selectRaw("
                COALESCE(department,'OTHER') as name,
                COALESCE(SUM(quantity * unitPrice),0) as total
            ")
            ->groupBy('name')
            ->orderByDesc('total')
            ->get();

        /* =========================
         * Category Sales
         * ========================= */
        $byCategory = DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('created', [$from, $to])
            ->whereRaw("COALESCE(NULLIF(TRIM(description),''),'') <> ''")
            ->selectRaw("
                COALESCE(category,'OTHER') as name,
                COALESCE(SUM(quantity * unitPrice),0) as total
            ")
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        /* =========================
         * Top Products
         * ========================= */
        $topProducts = DB::connection('reports_mysql')->table('v_order_index')
            ->whereBetween('created', [$from, $to])
            ->whereRaw("COALESCE(NULLIF(TRIM(description),''),'') <> ''")
            ->selectRaw("
                description as name,
                COALESCE(SUM(quantity),0) as qty,
                COALESCE(SUM(quantity * unitPrice),0) as total
            ")
            ->groupBy('name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        /* =========================
         * Hourly Sales
         * ========================= */
        $hourlyRaw = DB::connection('reports_mysql')->table('tbl_sales')
            ->where('closed', 1)
            ->where('voidCheck', 0)
            ->whereBetween('closedTime', [$from, $to])
            ->selectRaw("
                HOUR(closedTime) as hr,
                COUNT(*) as trx,
                COALESCE(SUM(total),0) as total
            ")
            ->groupBy('hr')
            ->orderBy('hr')
            ->get()
            ->keyBy('hr');

        $hourlySales = collect(range(0, 23))->map(function ($h) use ($hourlyRaw) {
            return [
                'label' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'trx'   => (int) ($hourlyRaw[$h]->trx ?? 0),
                'total' => (float) ($hourlyRaw[$h]->total ?? 0),
            ];
        });

        /* =========================
         * Payment Breakdown
         * ========================= */
        $paymentRows = DB::connection('reports_mysql')->table('v_payment_index')
            ->whereBetween('created', [$from, $to])
            ->selectRaw("
                CASE
                    WHEN paymentType = 'CASH' THEN 'CASH'
                    WHEN paymentType = 'CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                    WHEN paymentType = 'CARD' THEN 'EDC'
                    ELSE 'OTHER'
                END as bucket,
                COUNT(*) as trx,
                COALESCE(SUM(total),0) as total
            ")
            ->groupBy('bucket')
            ->orderByDesc('total')
            ->get();

        /* =========================
         * Outlet Comparison
         * ========================= */
        $outletRows = DB::connection('reports_mysql')->table(DB::raw("
    (
        SELECT 
            CASE
                WHEN tableName LIKE 'HALL%' THEN 'HALL'
                WHEN tableName LIKE 'Balkon%' THEN 'BALKON'
                WHEN tableName LIKE 'VIP%' THEN 'VIP'
                ELSE 'OTHER'
            END as outlet,
            total
        FROM tbl_sales
        WHERE closed = 1
          AND voidCheck = 0
          AND date BETWEEN '{$from}' AND '{$to}'
    ) as t
"))
->selectRaw("
    outlet,
    COUNT(*) as trx,
    COALESCE(SUM(total),0) as total
")
->groupBy('outlet')
->orderByDesc('total')
->get();

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'start' => $start,
            'end' => $end,
            'kpi' => $kpi,
            'byDept' => $byDept,
            'byCategory' => $byCategory,
            'topProducts' => $topProducts,
            'hourlySales' => $hourlySales,
            'paymentRows' => $paymentRows,
            'outletRows' => $outletRows,
        ]);
    }
}