<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoSalesController extends Controller
{
     public function index(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');
        $type  = strtolower($request->query('type',''));
        $q     = trim((string)$request->query('q',''));

        $from = $start ? Carbon::parse($start)->startOfDay() : now()->startOfDay();
        $to   = $end   ? Carbon::parse($end)->endOfDay()   : now()->endOfDay();

        // 🔹 Receipt-style rows (NO aggregation)
        $rows = DB::connection('reports_mysql')->table('tbl_sales_lines as sl')
            ->join('tbl_sales as s','sl.sales_id','=','s.id')
            ->leftJoin('tbl_employees as e','s.closedBy_id','=','e.id')
            ->whereBetween('s.date', [$from, $to])
            ->whereNull('s.invoice_id')       // NO SALES
            ->where('s.closed',1)
            ->where('s.voidCheck','!=',1)
            ->select([
                's.id as sales_id',
                's.created',
                's.closedTime',
                's.tableName',
                's.notes',
                's.subtotal',
                's.discountAmount',
                's.serviceChargeAmount',
                DB::raw('(s.tax1Amount + s.tax2Amount + s.tax3Amount) as taxAmount'),
                's.total',
                'e.name as approved_by',
                'sl.description',
                'sl.quantity',
                'sl.unitCost',
                'sl.idx',
            ])
            ->orderBy('s.id')
            ->orderBy('sl.idx')
            ->get();

        // 🔹 Classify type in PHP (SAFE & readable)
        $rows = $rows->map(function ($r) {
            $text = strtolower($r->description.' '.$r->notes);
            if (str_contains($text,'duty') || str_contains($text,'staff')) {
                $r->no_sale_type = 'DUTY MEAL';
            } elseif (str_contains($text,'compliment') || str_contains($text,'free')) {
                $r->no_sale_type = 'COMPLIMENT';
            } else {
                $r->no_sale_type = 'OTHER';
            }
            return $r;
        });

        // 🔹 Filters
        if ($type) {
            $rows = $rows->filter(fn($r) =>
                strtolower(str_replace(' ','',$r->no_sale_type)) === $type
            );
        }

        if ($q) {
            $rows = $rows->filter(fn($r) =>
                str_contains(strtolower($r->description), strtolower($q)) ||
                str_contains(strtolower($r->notes ?? ''), strtolower($q))
            );
        }

        // 🔹 Group for UI
        $bySales = $rows->groupBy('sales_id');

        return view('no_sales.index', compact('bySales'))
            ->with('filters', compact('start','end','type','q'));
    }
    public function export(Request $request)
    {
        $from = Carbon::parse($request->query('start'))->startOfDay();
        $to   = Carbon::parse($request->query('end'))->endOfDay();

        $rows = DB::connection('reports_mysql')->table('tbl_sales_lines as sl')
            ->join('tbl_sales as s','sl.sales_id','=','s.id')
            ->leftJoin('tbl_employees as e','s.closedBy_id','=','e.id')
            ->leftJoin('tbl_customers as cu','s.customer_id','=','cu.id')
            ->whereBetween('s.date', [$from, $to])
            ->whereNull('s.invoice_id')
            ->where('s.closed',1)
            ->where('s.voidCheck','!=',1)
            ->selectRaw("
                s.id                         as order_id,
                s.created                    as opened,
                s.closedTime                 as closed,
                s.notes                      as notes,
                s.tableName                  as table_name,
                e.name                       as cashier,
                s.pax                        as pax,
                sl.description               as item,
                sl.quantity                  as qty,
                CASE
                  WHEN sl.type = 1 THEN sl.quantity * sl.unitPrice
                  WHEN sl.type = 2 THEN sl.amount
                  WHEN sl.type = 3 THEN sl.amount - sl.changeAmount
                  ELSE 0
                END                           as price,
                s.subtotal                   as subtotal,
                s.discountAmount             as discount,
                s.serviceChargeAmount        as service,
                (s.tax1Amount + s.tax2Amount + s.tax3Amount) as tax,
                s.total                      as total
            ")
            ->orderBy('s.id')
            ->orderBy('sl.idx')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Order #','Opened','Closed','Notes','Table',
                'Cashier','Pax','Item','Qty','Price',
                'Subtotal','Discount','Service','Tax','Total'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->order_id,
                    $r->opened,
                    $r->closed,
                    $r->notes,
                    $r->table_name,
                    $r->cashier,
                    $r->pax,
                    $r->item,
                    number_format($r->qty,0,'.',''),
                    number_format($r->price,2,'.',''),
                    number_format($r->subtotal,2,'.',''),
                    number_format($r->discount,2,'.',''),
                    number_format($r->service,2,'.',''),
                    number_format($r->tax,2,'.',''),
                    number_format($r->total,2,'.',''),
                ]);
            }
            fclose($out);
        }, 'no_sales_receipt_detail_'.now()->format('Ymd_His').'.csv');
    }
}
