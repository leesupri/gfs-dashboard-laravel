<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyItemCountController extends Controller
{
    public function index(Request $request)
    {
        $start    = (string) ($request->get('start') ?? now()->toDateString());
        $end      = (string) ($request->get('end')   ?? now()->toDateString());
        $category = trim((string) $request->get('category', ''));
        $search   = trim((string) $request->get('q', ''));
        $export   = (string) $request->get('export', '');

        $from = Carbon::parse($start)->startOfDay();
        $to   = Carbon::parse($end)->endOfDay();

        $rows = $this->fetchRows($from, $to, $category, $search);

        $categories = DB::connection('reports_mysql')
            ->table('tbl_categories')
            ->orderBy('name')
            ->pluck('name');

        if ($export === 'csv') {
            return ExcelExport::download(
                "daily-item-count_{$start}_to_{$end}.xlsx",
                'Daily Item Count',
                ['Category', 'Item Name', 'Sales Qty', 'No-Sales Qty', 'Total'],
                $rows->map(fn($r) => [
                    $r->category,
                    $r->item_name,
                    (int) $r->sales_qty,
                    (int) $r->nosales_qty,
                    (int) $r->total_qty,
                ])->toArray()
            );
        }

        $totalSales   = $rows->sum('sales_qty');
        $totalNoSales = $rows->sum('nosales_qty');
        $totalAll     = $rows->sum('total_qty');
        $itemCount    = $rows->count();

        return view('reports.daily-item-count', compact(
            'rows', 'start', 'end', 'category', 'search',
            'categories', 'totalSales', 'totalNoSales', 'totalAll', 'itemCount'
        ));
    }

    private function fetchRows(Carbon $from, Carbon $to, string $category, string $search)
    {
        $query = DB::connection('reports_mysql')
            ->table('tbl_sales_lines as sl')
            ->join('tbl_sales as s',        'sl.sales_id',   '=', 's.id')
            ->join('tbl_items as i',        'sl.item_id',    '=', 'i.id')
            ->join('tbl_categories as c',   'i.category_id', '=', 'c.id')
            ->whereBetween('s.date', [$from, $to])
            ->where('s.voidCheck', 0)
            ->selectRaw('
                c.name  AS category,
                i.name  AS item_name,
                SUM(CASE WHEN s.invoice_id IS NOT NULL THEN sl.quantity ELSE 0 END) AS sales_qty,
                SUM(CASE WHEN s.invoice_id IS NULL     THEN sl.quantity ELSE 0 END) AS nosales_qty,
                SUM(sl.quantity)                                                     AS total_qty
            ')
            ->groupBy('c.name', 'i.id', 'i.name')
            ->havingRaw('SUM(sl.quantity) > 0')
            ->orderBy('c.name')
            ->orderBy('i.name');

        if ($category !== '') {
            $query->where('c.name', $category);
        }

        if ($search !== '') {
            $query->where('i.name', 'like', "%{$search}%");
        }

        return $query->get();
    }
}
