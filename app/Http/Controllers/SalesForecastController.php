<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesForecastController extends Controller
{
    private const HIST_OPTIONS     = [7, 14, 30, 60, 90];
    private const FORECAST_OPTIONS = [7, 14, 30];

    // MySQL DAYOFWEEK: 1=Sun, 2=Mon … 7=Sat
    private const DOW_NAMES = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];

    public function index(Request $request)
    {
        $histDays = (int) $request->get('hist_days', 30);
        if (!in_array($histDays, self::HIST_OPTIONS)) {
            $histDays = 30;
        }

        $forecastDays = (int) $request->get('forecast_days', 7);
        if (!in_array($forecastDays, self::FORECAST_OPTIONS)) {
            $forecastDays = 7;
        }

        // Historical window: last N completed days
        $histTo   = now()->subDay()->endOfDay();
        $histFrom = now()->subDays($histDays)->startOfDay();

        // Comparison window: same length, immediately before
        $compTo   = $histFrom->copy()->subDay()->endOfDay();
        $compFrom = $compTo->copy()->subDays($histDays)->startOfDay();

        $dept = trim((string) $request->get('dept', ''));
        $cat  = trim((string) $request->get('cat',  ''));
        $q    = trim((string) $request->get('q',    ''));

        // ── Seasonality: day-of-week pattern ──────────────────────────────
        $dowPattern     = $this->fetchDowPattern($histFrom, $histTo);
        $seasonalFactor = $this->calcSeasonalFactor($dowPattern['indices'], $forecastDays);
        // seasonalFactor replaces the naive "$forecastDays" multiplier.
        // e.g. for 7 days starting Thursday, it sums the 7 DOW indices instead of always = 7.

        // ── Per-item data ─────────────────────────────────────────────────
        $histRows = $this->fetchRows($histFrom, $histTo, $dept, $cat, $q);
        $compMap  = $this->fetchRows($compFrom, $compTo, $dept, $cat, $q)->keyBy('item_key');

        $rows = $histRows->map(function ($row) use ($histDays, $seasonalFactor, $forecastDays, $compMap) {
            $dailyQty = (float) $row->total_qty     / $histDays;
            $dailyRev = (float) $row->total_revenue / $histDays;

            // Trend vs comparison period
            $comp     = $compMap->get($row->item_key);
            $trendPct = null;
            if ($comp && (float) $comp->total_revenue > 0) {
                $compDailyRev = (float) $comp->total_revenue / $histDays;
                $trendPct     = ($dailyRev - $compDailyRev) / $compDailyRev * 100;
            }

            return (object) [
                'department'    => $row->department,
                'category'      => $row->category,
                'item_name'     => $row->item_name,
                'hist_qty'      => (float) $row->total_qty,
                'hist_rev'      => (float) $row->total_revenue,
                'daily_qty'     => $dailyQty,
                'daily_rev'     => $dailyRev,
                // Seasonally-adjusted forecast (uses DOW factor instead of flat N days)
                'forecast_qty'  => $dailyQty * $seasonalFactor,
                'forecast_rev'  => $dailyRev  * $seasonalFactor,
                // Simple (un-adjusted) for comparison in the legend
                'simple_rev'    => $dailyRev  * $forecastDays,
                'trend_pct'     => $trendPct,
            ];
        });

        // Nested array: dept → category → items
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->department][$row->category][] = $row;
        }

        return view('reports.sales-forecast', [
            'grouped'          => $grouped,
            'histDays'         => $histDays,
            'forecastDays'     => $forecastDays,
            'histFrom'         => $histFrom,
            'histTo'           => $histTo,
            'dept'             => $dept,
            'cat'              => $cat,
            'q'                => $q,
            'totalForecastRev' => $rows->sum('forecast_rev'),
            'totalForecastQty' => $rows->sum('forecast_qty'),
            'totalHistRev'     => $rows->sum('hist_rev'),
            'itemCount'        => $rows->count(),
            'deptCount'        => count($grouped),
            'growingCount'     => $rows->filter(fn($r) => $r->trend_pct !== null && $r->trend_pct > 5)->count(),
            'decliningCount'   => $rows->filter(fn($r) => $r->trend_pct !== null && $r->trend_pct < -5)->count(),
            // Seasonality data for the view
            'dowPattern'       => $dowPattern,
            'seasonalFactor'   => $seasonalFactor,
            'forecastWindow'   => $this->buildForecastWindow($dowPattern['indices'], $forecastDays),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Day-of-week pattern (restaurant-wide, independent of item filters)
     | Returns indices[1..7] where 1.0 = average day.
     ─────────────────────────────────────────────────────────────────────*/
    private function fetchDowPattern(Carbon $from, Carbon $to): array
    {
        $rows = DB::connection('reports_mysql')
            ->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                DAYOFWEEK(date)             AS dow,
                COUNT(DISTINCT DATE(date))  AS day_count,
                SUM(quantity * unitPrice)   AS total_rev
            ")
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->keyBy('dow');

        $totalDays = max(1, $from->diffInDays($to) + 1);
        $totalRev  = $rows->sum('total_rev');
        $overallDailyAvg = $totalRev / $totalDays;

        // Build per-DOW daily averages and indices
        $indices = [];
        $dayAvgs = [];
        for ($d = 1; $d <= 7; $d++) {
            $r = $rows->get($d);
            $dayAvg = ($r && $r->day_count > 0)
                ? (float) $r->total_rev / $r->day_count
                : $overallDailyAvg; // missing day: assume neutral
            $dayAvgs[$d] = $dayAvg;
            $indices[$d] = $overallDailyAvg > 0 ? $dayAvg / $overallDailyAvg : 1.0;
        }

        return [
            'indices'          => $indices,   // [1..7] relative to 1.0
            'day_avgs'         => $dayAvgs,
            'overall_daily_avg'=> $overallDailyAvg,
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Sum of DOW indices for the next $forecastDays starting today.
     | This replaces the flat "$forecastDays" multiplier.
     ─────────────────────────────────────────────────────────────────────*/
    private function calcSeasonalFactor(array $indices, int $forecastDays): float
    {
        $factor = 0.0;
        for ($i = 0; $i < $forecastDays; $i++) {
            // PHP dayOfWeek: 0=Sun → MySQL DAYOFWEEK: 1=Sun, so +1
            $dow     = now()->addDays($i)->dayOfWeek + 1;
            $factor += $indices[$dow] ?? 1.0;
        }
        return $factor;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Build day-by-day breakdown of the forecast window for display.
     ─────────────────────────────────────────────────────────────────────*/
    private function buildForecastWindow(array $indices, int $forecastDays): array
    {
        $window = [];
        for ($i = 0; $i < $forecastDays; $i++) {
            $date    = now()->addDays($i);
            $dow     = $date->dayOfWeek + 1;
            $idx     = $indices[$dow] ?? 1.0;
            $window[] = [
                'date'  => $date->format('d M'),
                'day'   => self::DOW_NAMES[$dow],
                'dow'   => $dow,
                'index' => $idx,
            ];
        }
        return $window;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Per-item aggregation from v_order_index
     ─────────────────────────────────────────────────────────────────────*/
    private function fetchRows(Carbon $from, Carbon $to, string $dept, string $cat, string $q)
    {
        return DB::connection('reports_mysql')
            ->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->when($dept !== '', fn($qb) => $qb->where('department', 'like', "%{$dept}%"))
            ->when($cat  !== '', fn($qb) => $qb->where('category',   'like', "%{$cat}%"))
            ->when($q    !== '', fn($qb) => $qb->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('category',   'like', "%{$q}%")
                    ->orWhere('department', 'like', "%{$q}%");
            }))
            ->selectRaw("
                COALESCE(department, 'NO DEPT')                           AS department,
                COALESCE(category,   'UNCATEGORIZED')                     AS category,
                description                                                AS item_name,
                CONCAT_WS('|', department, category, description)         AS item_key,
                SUM(quantity)                                              AS total_qty,
                SUM(quantity * unitPrice)                                  AS total_revenue
            ")
            ->groupBy('department', 'category', 'description')
            ->having('total_qty', '>', 0)
            ->orderBy('department')
            ->orderBy('category')
            ->orderBy('description')
            ->get();
    }
}
