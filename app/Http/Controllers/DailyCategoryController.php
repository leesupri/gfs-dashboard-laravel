<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyCategoryController extends Controller
{
    public function index(Request $request)
    {
        $start  = $request->get('start', now()->toDateString());
        $end    = $request->get('end',   now()->toDateString());
        $export = $request->get('export', '');

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        $rows = DB::connection('reports_mysql')
            ->table('v_order')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                category,
                SUM(quantity)             AS qty,
                SUM(quantity * unitPrice) AS amount
            ")
            ->groupBy('category')
            ->havingRaw('SUM(quantity) <> 0')
            ->orderByDesc('amount')
            ->get();

        if ($export === 'csv') {
            return ExcelExport::download(
                "daily-category_{$start}_to_{$end}.xlsx",
                'Daily Category Sales',
                ['Category', 'Qty', 'Amount'],
                $rows->map(fn($r) => [$r->category, (float)$r->qty, (float)$r->amount])->toArray()
            );
        }

        $totalQty    = $rows->sum('qty');
        $totalAmount = $rows->sum('amount');

        return view('reports.daily-category', compact(
            'rows', 'start', 'end', 'totalQty', 'totalAmount'
        ));
    }
}
