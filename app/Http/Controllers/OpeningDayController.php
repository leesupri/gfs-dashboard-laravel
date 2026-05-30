<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningDayController extends Controller
{
    public function index(Request $request)
    {
        $start  = $request->get('start', now()->startOfMonth()->toDateString());
        $end    = $request->get('end',   now()->toDateString());
        $export = $request->get('export', '');

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        $rows = $this->fetchRows($from, $to);

        if ($export === 'csv') {
            return $this->exportExcel($rows, $start, $end);
        }

        $totalDays   = $rows->count();
        $closedDays  = $rows->filter(fn($r) => !is_null($r->closedBy_id))->count();
        $openDays    = $totalDays - $closedDays;

        return view('reports.opening-day', [
            'title'      => 'Opening Day Report',
            'rows'       => $rows,
            'start'      => $start,
            'end'        => $end,
            'totalDays'  => $totalDays,
            'closedDays' => $closedDays,
            'openDays'   => $openDays,
        ]);
    }

    private function fetchRows(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')
            ->table('tbl_daily_procedures as dp')
            ->leftJoin('tbl_employees as e1', 'e1.id', '=', 'dp.createdBy_id')
            ->leftJoin('tbl_employees as e2', 'e2.id', '=', 'dp.closedBy_id')
            ->whereBetween('dp.date', [$from, $to])
            ->selectRaw("
                dp.`date`,
                dp.`created`,
                dp.`closed`,
                dp.`createdBy_id`,
                dp.`closedBy_id`,
                e1.name AS opening,
                e2.name AS closing
            ")
            ->orderBy('dp.date', 'desc')
            ->get();
    }

    private function exportExcel($rows, string $start, string $end)
    {
        $headers = ['Date', 'Opening Day By', 'Opening Time', 'End of Day By', 'EOD Time', 'Status'];

        $dataRows = $rows->map(fn($r) => [
            $r->date    ? Carbon::parse($r->date)->format('d M Y')    : '',
            $r->opening ?? '—',
            $r->created ? Carbon::parse($r->created)->format('d M Y H:i') : '—',
            $r->closing ?? '—',
            $r->closed  ? Carbon::parse($r->closed)->format('d M Y H:i')  : '—',
            $r->closedBy_id ? 'Closed' : 'Open',
        ])->toArray();

        return ExcelExport::download(
            "opening-day_{$start}_to_{$end}.xlsx",
            'Opening Day Report',
            $headers,
            $dataRows
        );
    }
}
