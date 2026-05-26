<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

            return $this->exportCsv(
                $rows,
                $start,
                $end,
                $fromWarehouse,
                $toWarehouse,
                $category,
                $item,
                $transferId,
                $q
            );
        }

        $rows = (clone $rowsQuery)
            ->orderBy('category')
            ->orderBy('item_name')
            ->orderBy('transfer_id')
            ->get();

        $groupedRows = $rows->groupBy('category')->map(function ($categoryItems, $categoryName) {
            $itemGroups = $categoryItems->groupBy('item_name')->map(function ($itemItems, $itemName) {
                return [
                    'item_name' => $itemName,
                    'item_code' => $itemItems->first()->item_code,
                    'uom' => $itemItems->first()->uom,
                    'rows' => $itemItems,
                    'total_qty' => $itemItems->sum(fn ($row) => (float) $row->quantity),
                ];
            })->values();

            return [
                'category' => $categoryName,
                'items' => $itemGroups,
                'total_qty' => $categoryItems->sum(fn ($row) => (float) $row->quantity),
            ];
        })->values();

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_lines,
                COUNT(DISTINCT t.id) as total_transfers,
                SUM(tl.quantity) as total_quantity
            ')
            ->first();

        return view('reports.transfer-detail', [
            'title' => 'Transfer Detail Report',
            'groupedRows' => $groupedRows,
            'start' => $start,
            'end' => $end,
            'fromWarehouse' => $fromWarehouse,
            'toWarehouse' => $toWarehouse,
            'category' => $category,
            'item' => $item,
            'transferId' => $transferId,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    private function exportCsv(
        $rows,
        $start,
        $end,
        $fromWarehouse,
        $toWarehouse,
        $category,
        $item,
        $transferId,
        $q
    ): StreamedResponse {
        $filename = 'transfer-detail-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use (
            $rows,
            $start,
            $end,
            $fromWarehouse,
            $toWarehouse,
            $category,
            $item,
            $transferId,
            $q
        ) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Transfer Detail Report']);
            fputcsv($handle, ['Start Date', $start]);
            fputcsv($handle, ['End Date', $end]);
            fputcsv($handle, ['From Warehouse', $fromWarehouse !== '' ? $fromWarehouse : 'All']);
            fputcsv($handle, ['To Warehouse', $toWarehouse !== '' ? $toWarehouse : 'All']);
            fputcsv($handle, ['Category', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Item', $item !== '' ? $item : 'All']);
            fputcsv($handle, ['Transfer ID', $transferId !== '' ? $transferId : 'All']);
            fputcsv($handle, ['Search', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Category',
                'Item Name',
                'Item Code',
                'Transfer ID',
                'Date',
                'Quantity',
                'UOM',
                'From Warehouse',
                'To Warehouse',
                'Description',
                'Created By',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
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
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}