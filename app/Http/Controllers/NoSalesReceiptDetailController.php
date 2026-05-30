<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoSalesReceiptDetailController extends Controller
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

        // Group by sale ID, keeping sale header data + collecting line items
        $receipts = [];
        foreach ($rows as $row) {
            $id = $row->id;
            if (!isset($receipts[$id])) {
                $receipts[$id] = [
                    'id'            => $row->id,
                    'created'       => $row->created,
                    'closed'        => $row->closed,
                    'notes'         => $row->notes,
                    'tableName'     => $row->tableName,
                    'subtotal'      => (float) $row->subtotal,
                    'discount'      => (float) $row->discount,
                    'serviceAmount' => (float) $row->serviceAmount,
                    'taxAmount'     => (float) $row->taxAmount,
                    'total'         => (float) $row->total,
                    'type'          => $row->type,
                    'fullName'      => $row->fullName,
                    'guest'         => $row->guest,
                    'member'        => $row->member,
                    'items'         => [],
                ];
            }
            if ($row->description) {
                $receipts[$id]['items'][] = [
                    'description' => $row->description,
                    'remark'      => $row->remark,
                    'quantity'    => (float) $row->quantity,
                    'price'       => (float) $row->price,
                ];
            }
        }

        $totalReceipts = count($receipts);
        $grandTotal    = array_sum(array_column($receipts, 'total'));

        return view('reports.no-sales-receipt-detail', [
            'title'          => 'No Sales Receipt Detail',
            'receipts'       => $receipts,
            'start'          => $start,
            'end'            => $end,
            'totalReceipts'  => $totalReceipts,
            'grandTotal'     => $grandTotal,
        ]);
    }

    private function fetchRows(Carbon $from, Carbon $to)
    {
        return DB::connection('reports_mysql')
            ->table('tbl_sales_lines as sl')
            ->join('tbl_sales as s',        'sl.sales_id',    '=', 's.id')
            ->leftJoin('tbl_employees as e', 's.closedBy_id', '=', 'e.id')
            ->leftJoin('tbl_customers as c', 's.customer_id', '=', 'c.id')
            ->whereBetween('s.date', [$from, $to])
            ->where('s.voidCheck', '!=', 1)
            ->whereNull('s.invoice_id')
            ->where('s.closed', 1)
            ->selectRaw("
                s.id,
                s.created,
                s.closedTime                                              AS closed,
                s.notes,
                s.tableName,
                s.subtotal,
                s.discountAmount                                          AS discount,
                s.serviceChargeAmount                                     AS serviceAmount,
                s.tax1Amount + s.tax2Amount + s.tax3Amount                AS taxAmount,
                s.total,
                s.type,
                e.name                                                    AS fullName,
                s.pax                                                     AS guest,
                s.closedAt,
                sl.description,
                sl.remark,
                sl.quantity,
                c.name                                                    AS member,
                CASE
                    WHEN sl.type = 1 THEN sl.quantity * sl.unitPrice
                    WHEN sl.type = 2 THEN sl.amount
                    WHEN sl.type = 3 THEN sl.amount - sl.changeAmount
                    ELSE 0
                END                                                       AS price
            ")
            ->orderBy('s.id')
            ->orderBy('sl.idx')
            ->limit(2000)
            ->get();
    }

    private function exportExcel($rows, string $start, string $end)
    {
        $headers = [
            'Order #', 'Table', 'Type', 'Cashier', 'Pax', 'Member', 'Notes',
            'Opened', 'Closed',
            'Item', 'Remark', 'Qty', 'Price',
            'Subtotal', 'Service', 'Tax', 'Discount', 'Total',
        ];

        $dataRows = [];
        $seen = [];
        foreach ($rows as $r) {
            $isFirst = !isset($seen[$r->id]);
            $seen[$r->id] = true;
            $dataRows[] = [
                $r->id,
                $isFirst ? $r->tableName  : '',
                $isFirst ? $r->type       : '',
                $isFirst ? $r->fullName   : '',
                $isFirst ? $r->guest      : '',
                $isFirst ? $r->member     : '',
                $isFirst ? $r->notes      : '',
                $isFirst && $r->created  ? Carbon::parse($r->created)->format('d/m/Y H:i') : '',
                $isFirst && $r->closed   ? Carbon::parse($r->closed)->format('d/m/Y H:i')  : '',
                $r->description,
                $r->remark ?? '',
                (float) $r->quantity,
                (float) $r->price,
                $isFirst ? (float) $r->subtotal      : '',
                $isFirst ? (float) $r->serviceAmount  : '',
                $isFirst ? (float) $r->taxAmount      : '',
                $isFirst ? (float) $r->discount       : '',
                $isFirst ? (float) $r->total          : '',
            ];
        }

        return ExcelExport::download(
            "no-sales-receipt-detail_{$start}_to_{$end}.xlsx",
            'No Sales Receipt Detail',
            $headers,
            $dataRows
        );
    }
}
