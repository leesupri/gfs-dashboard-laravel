<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderBoardController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start', now()->toDateString());
        $end   = $request->query('end', now()->toDateString());

        $invoice    = trim((string) $request->query('invoice', ''));
        $table      = trim((string) $request->query('table', ''));
        $station    = trim((string) $request->query('station', ''));     // createdAtHo / closedAt
        $department = trim((string) $request->query('department', ''));
        $category   = trim((string) $request->query('category', ''));
        $q          = trim((string) $request->query('q', ''));

        $rows = DB::table('v_order_all')
            ->select([
                'id',
                'invoice_id',
                'date',
                'salesType',
                'description',
                'quantity',
                'unitPrice',
                'unitCost',
                'category',
                'department',
                'employee',
                'discountAmount',
                'created',
                'closedBy',
                'closedTime',
                'closedAt',
                'createdAtHo',
                'tableName',
                'customer',
            ])
            ->whereBetween('date', [
                $start . ' 00:00:00',
                $end . ' 23:59:59',
            ])
            ->when($invoice !== '', function ($query) use ($invoice) {
                $query->where('invoice_id', 'like', "%{$invoice}%");
            })
            ->when($table !== '', function ($query) use ($table) {
                $query->where('tableName', 'like', "%{$table}%");
            })
            ->when($station !== '', function ($query) use ($station) {
                $query->where(function ($w) use ($station) {
                    $w->where('createdAtHo', 'like', "%{$station}%")
                      ->orWhere('closedAt', 'like', "%{$station}%");
                });
            })
            ->when($department !== '', function ($query) use ($department) {
                $query->where('department', 'like', "%{$department}%");
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', 'like', "%{$category}%");
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('description', 'like', "%{$q}%")
                      ->orWhere('employee', 'like', "%{$q}%")
                      ->orWhere('closedBy', 'like', "%{$q}%")
                      ->orWhere('customer', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('invoice_id')
            ->orderBy('created')
            ->orderBy('id')
            ->get();

        $byBill = $rows->groupBy('invoice_id')->map(function ($items, $invoiceId) {
            $first = $items->first();

            $gross = $items->sum(function ($r) {
                return ((float) $r->quantity * (float) $r->unitPrice);
            });

            $discount = $items->sum(function ($r) {
                return (float) ($r->discountAmount ?? 0);
            });

            return (object) [
                'invoice_id'      => $invoiceId,
                'date'            => $first->date,
                'salesType'       => $first->salesType,
                'tableName'       => $first->tableName,
                'customer'        => $first->customer,
                'ordered_by'      => $first->employee,
                'ordered_at'      => $items->min('created'),
                'order_station'   => $first->createdAtHo,
                'closed_by'       => $first->closedBy,
                'closed_time'     => $first->closedTime,
                'close_station'   => $first->closedAt,
                'gross'           => $gross,
                'discount'        => $discount,
                'net'             => $gross - $discount,
                'item_count'      => $items->count(),
                'lines'           => $items,
            ];
        });

        return view('reports.order_board.index', [
            'title'      => 'Order Board',
            'active'     => 'reports-order-board',
            'byBill'     => $byBill,
            'start'      => $start,
            'end'        => $end,
            'invoice'    => $invoice,
            'table'      => $table,
            'station'    => $station,
            'department' => $department,
            'category'   => $category,
            'q'          => $q,
        ]);
    }
}