<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoidReportController extends Controller
{
    public function index(Request $request)
    {
        // ✅ default today
        $start = $request->query('start', now()->toDateString());
        $end   = $request->query('end', now()->toDateString());

        // optional: keep safe order (if user swaps)
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $rows = DB::table('tbl_sales_lines as sl')
            ->join('tbl_sales as s', 'sl.sales_id', '=', 's.id')
            ->join('tbl_employees as e', 'sl.employee_id', '=', 'e.id')
            ->join('tbl_items as i', 'sl.item_id', '=', 'i.id')
            ->select([
                'e.name as employee',
                's.invoice_id as invoiceNo',
                's.date as saleDate',       // ✅ filter base date (like Jasper)
                'sl.created as lineCreated',// useful for ordering
                'sl.createdAt',
                'sl.description as itemName',
                'sl.quantity',
                'sl.unitPrice',
                'sl.voidReason',
            ])
            ->where('sl.quantity', '<', 0)
            ->whereBetween('s.date', [
                $start . ' 00:00:00',
                $end   . ' 23:59:59',
            ])
            ->where(function ($w) {
                $w->where('i.noReport', '!=', 1)
                  ->orWhere('sl.unitPrice', '>', 0);
            })
            ->orderBy('e.name')
            ->orderBy('sl.created')
            ->get();

        $groups = $rows->groupBy('employee')->map(function ($items) {
            return [
                'items' => $items,
                'total_qty'   => $items->sum('quantity'),
                'total_value' => $items->sum(fn ($x) => ($x->unitPrice ?? 0) * ($x->quantity ?? 0)),
            ];
        });

        $grand = [
            'qty'   => $rows->sum('quantity'),
            'value' => $rows->sum(fn ($x) => ($x->unitPrice ?? 0) * ($x->quantity ?? 0)),
        ];

        return view('reports.void.index', [
            'groups'  => $groups,
            'grand'   => $grand,
            'filters' => compact('start', 'end'),
        ]);
    
    }
}
