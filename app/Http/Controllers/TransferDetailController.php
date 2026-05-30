<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferDetailController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $fromWarehouse = trim((string) $request->get('from_warehouse', ''));
        $toWarehouse = trim((string) $request->get('to_warehouse', ''));
        $category = trim((string) $request->get('category', ''));
        $item = trim((string) $request->get('item', ''));
        $transferId = trim((string) $request->get('transfer_id', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::connection('reports_mysql')
            ->table('tbl_transfer_lines as tl')
            ->join('tbl_transfers as t', 'tl.transfer_id', '=', 't.id')
            ->join('tbl_warehouses as fromWarehouse', 't.from_id', '=', 'fromWarehouse.id')
            ->join('tbl_items as i', 'tl.item_id', '=', 'i.id')
            ->join('tbl_categories as c', 'i.category_id', '=', 'c.id')
            ->join('tbl_warehouses as toWarehouse', 'toWarehouse.id', '=', 't.to_id')
            ->whereBetween('t.date', [$from, $to])
            ->when($fromWarehouse !== '', function ($query) use ($fromWarehouse) {
                $query->where('fromWarehouse.name', 'like', '%' . $fromWarehouse . '%');
            })
            ->when($toWarehouse !== '', function ($query) use ($toWarehouse) {
                $query->where('toWarehouse.name', 'like', '%' . $toWarehouse . '%');
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('c.name', 'like', '%' . $category . '%');
            })
            ->when($item !== '', function ($query) use ($item) {
                $query->where('i.name', 'like', '%' . $item . '%');
            })
            ->when($transferId !== '', function ($query) use ($transferId) {
                $query->where('t.id', 'like', '%' . $transferId . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('c.name', 'like', '%' . $q . '%')
                        ->orWhere('i.name', 'like', '%' . $q . '%')
                        ->orWhere('i.code', 'like', '%' . $q . '%')
                        ->orWhere('fromWarehouse.name', 'like', '%' . $q . '%')
                        ->orWhere('toWarehouse.name', 'like', '%' . $q . '%')
                        ->orWhere('tl.description', 'like', '%' . $q . '%')
                        ->orWhere('t.id', 'like', '%' . $q . '%');
                });
            });

        $rowsQuery = (clone $baseQuery)
            ->select([
                'c.name as category',
                'i.name as item_name',
                'i.code as item_code',
                't.id as transfer_id',
                't.date',
                'tl.quantity',
                'i.uom',
                'fromWarehouse.name as from_warehouse',
                'toWarehouse.name as to_warehouse',
                'tl.description',
                't.savedBy as created_by',
            ]);

        if ($export === 'csv') {
            $rows = (clone $rowsQuery)
                ->orderBy('category')
                ->orderBy('item_name')
                ->orderBy('transfer_id')
                ->get();

            return $this->exportCsv($rows);
        }

        $rows = (clone $rowsQuery)
            ->orderBy('category')
            ->orderBy('item_name')
            ->orderBy('transfer_id')
            ->limit(2000)
            ->get();

        $truncated = $rows->count() >= 2000;

        // Build nested structure with plain arrays to minimise memory overhead
        $groupedRows = [];
        foreach ($rows as $row) {
            $cat  = $row->category;
            $iKey = $row->item_name;

            if (!isset($groupedRows[$cat])) {
                $groupedRows[$cat] = ['category' => $cat, 'items' => [], 'total_qty' => 0.0];
            }
            if (!isset($groupedRows[$cat]['items'][$iKey])) {
                $groupedRows[$cat]['items'][$iKey] = [
                    'item_name' => $iKey,
                    'item_code' => $row->item_code,
                    'uom'       => $row->uom,
                    'rows'      => [],
                    'total_qty' => 0.0,
                ];
            }
            $groupedRows[$cat]['items'][$iKey]['rows'][]    = $row;
            $groupedRows[$cat]['items'][$iKey]['total_qty'] += (float) $row->quantity;
            $groupedRows[$cat]['total_qty']                 += (float) $row->quantity;
        }

        foreach ($groupedRows as &$cg) {
            $cg['items'] = array_values($cg['items']);
        }
        $groupedRows = array_values($groupedRows);

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_lines,
                COUNT(DISTINCT t.id) as total_transfers,
                SUM(tl.quantity) as total_quantity
            ')
            ->first();

        return view('reports.transfer-detail', [
            'title'        => 'Transfer Detail Report',
            'groupedRows'  => $groupedRows,
            'truncated'    => $truncated,
            'start'        => $start,
            'end'          => $end,
            'fromWarehouse'=> $fromWarehouse,
            'toWarehouse'  => $toWarehouse,
            'category'     => $category,
            'item'         => $item,
            'transferId'   => $transferId,
            'q'            => $q,
            'summary'      => $summary,
        ]);
    }

    private function exportCsv($rows)
    {
        $headers = [
            'Category', 'Item Name', 'Item Code', 'Transfer ID', 'Date',
            'Quantity', 'UOM', 'From Warehouse', 'To Warehouse', 'Description', 'Created By',
        ];

        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [
                $row->category,
                $row->item_name,
                $row->item_code,
                $row->transfer_id,
                $row->date ? Carbon::parse($row->date)->format('d/m/Y H:i:s') : '',
                (float) $row->quantity,
                $row->uom,
                $row->from_warehouse,
                $row->to_warehouse,
                $row->description,
                $row->created_by,
            ];
        }

        return ExcelExport::download(
            'transfer-detail-' . now()->format('Ymd_His') . '.xlsx',
            'Transfer Detail Report',
            $headers,
            $dataRows
        );
    }
}