<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyHourController extends Controller
{
    // Hours to show in the pivot (09:00 – 24:00 / midnight)
    private const HOURS = [9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,0];

    public function index(Request $request)
    {
        $start  = $request->get('start', now()->toDateString());
        $end    = $request->get('end',   now()->toDateString());
        $export = $request->get('export', '');

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        // Build pivot SELECT: one CASE per hour
        $hourCases = implode(",\n", array_map(fn($h) =>
            "SUM(CASE WHEN DATE_FORMAT(created,'%k') = {$h} THEN quantity ELSE 0 END) AS h{$h}",
            self::HOURS
        ));

        $rows = DB::connection('reports_mysql')
            ->table('v_order')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                DATE(date)                                                         AS salesDate,
                CASE WHEN salesType = 'DELIVERY' THEN 'Delivery' ELSE 'Dine & Tk.Away' END AS salesType,
                {$hourCases},
                SUM(quantity)              AS qty,
                SUM(quantity * unitPrice)  AS ttlPrice
            ")
            ->groupByRaw("
                DATE(date),
                CASE WHEN salesType = 'DELIVERY' THEN 'Delivery' ELSE 'Dine & Tk.Away' END
            ")
            ->havingRaw('SUM(quantity) <> 0')
            ->orderBy('salesDate')
            ->orderBy('salesType')
            ->get();

        if ($export === 'csv') {
            return $this->export($rows, $start, $end);
        }

        // Totals row
        $totals = ['qty' => 0, 'ttlPrice' => 0];
        foreach (self::HOURS as $h) {
            $totals["h{$h}"] = 0;
        }
        foreach ($rows as $r) {
            $totals['qty']      += $r->qty;
            $totals['ttlPrice'] += $r->ttlPrice;
            foreach (self::HOURS as $h) {
                $totals["h{$h}"] += $r->{"h{$h}"};
            }
        }

        return view('reports.daily-hour', [
            'rows'   => $rows,
            'totals' => $totals,
            'hours'  => self::HOURS,
            'start'  => $start,
            'end'    => $end,
        ]);
    }

    private function export($rows, string $start, string $end)
    {
        $hourLabels = array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00', self::HOURS);
        $headers    = array_merge(['Date', 'Sales Type'], $hourLabels, ['Total Qty', 'Total Amount']);

        $dataRows = $rows->map(function ($r) {
            $row = [
                $r->salesDate ? Carbon::parse($r->salesDate)->format('d M Y') : '',
                $r->salesType,
            ];
            foreach (self::HOURS as $h) {
                $row[] = (float) $r->{"h{$h}"};
            }
            $row[] = (float) $r->qty;
            $row[] = (float) $r->ttlPrice;
            return $row;
        })->toArray();

        return ExcelExport::download(
            "daily-hour_{$start}_to_{$end}.xlsx",
            'Daily Hour Date Sales',
            $headers,
            $dataRows
        );
    }
}
