<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierShiftController extends Controller
{
    public function index(Request $request)
    {
        $start  = $request->get('start', now()->toDateString());
        $end    = $request->get('end',   now()->toDateString());
        $export = $request->get('export', '');

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        $rows = $this->fetchRows($from, $to);

        if ($export === 'csv') {
            return $this->exportExcel($rows, $start, $end);
        }

        // Group: station → cashier → shifts
        $grouped = [];
        foreach ($rows as $row) {
            $s = $row->station  ?? 'No Station';
            $c = $row->ciprut   ?? 'Unknown';
            $grouped[$s][$c][] = $row;
        }

        // KPIs
        $totalShifts    = count($rows);
        $totalVariance  = $rows->sum('bedanyaCuy');
        $stationCount   = count($grouped);
        $shortages      = $rows->filter(fn($r) => (float)$r->bedanyaCuy < 0)->count();
        $surpluses      = $rows->filter(fn($r) => (float)$r->bedanyaCuy > 0)->count();

        return view('reports.cashier-shift', [
            'title'         => 'Cashier Shift Report',
            'grouped'       => $grouped,
            'start'         => $start,
            'end'           => $end,
            'totalShifts'   => $totalShifts,
            'totalVariance' => $totalVariance,
            'stationCount'  => $stationCount,
            'shortages'     => $shortages,
            'surpluses'     => $surpluses,
        ]);
    }

    private function fetchRows(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')
            ->table('tbl_cashier_shifts as cs')
            ->join('tbl_employees as e', 'cs.cashier_id', '=', 'e.id')
            ->whereBetween('cs.openingTime', [$from, $to])
            ->selectRaw("
                cs.station,
                cs.openingTime,
                cs.closingTime,
                cs.openingBalance,
                cs.cashSales,
                cs.payIn,
                cs.payOut,
                cs.closingBalance,
                e.name                                                         AS ciprut,
                cs.closingBalance
                  - (cs.openingBalance + cs.cashSales + cs.payIn - cs.payOut)  AS bedanyaCuy
            ")
            ->orderBy('cs.station')
            ->orderBy('e.name')
            ->orderBy('cs.openingTime')
            ->get();
    }

    private function exportExcel($rows, string $start, string $end)
    {
        $headers = [
            'Station', 'Cashier',
            'Opening Time', 'Closing Time',
            'Opening Balance', 'Cash Sales', 'Pay In', 'Pay Out',
            'Closing Balance', 'Difference',
        ];

        $dataRows = $rows->map(fn($r) => [
            $r->station,
            $r->ciprut,
            $r->openingTime ? Carbon::parse($r->openingTime)->format('d/m/Y H:i') : '',
            $r->closingTime ? Carbon::parse($r->closingTime)->format('d/m/Y H:i') : '',
            (float) $r->openingBalance,
            (float) $r->cashSales,
            (float) $r->payIn,
            (float) $r->payOut,
            (float) $r->closingBalance,
            (float) $r->bedanyaCuy,
        ])->toArray();

        return ExcelExport::download(
            "cashier-shift_{$start}_to_{$end}.xlsx",
            'Cashier Shift Report',
            $headers,
            $dataRows
        );
    }
}
