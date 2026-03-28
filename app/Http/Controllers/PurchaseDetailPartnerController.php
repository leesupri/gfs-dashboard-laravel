<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseDetailPartnerController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $category = trim((string) $request->get('category', ''));
        $partner = trim((string) $request->get('partner', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::table('tbl_purchase_invoice_lines as pil')
            ->join('tbl_purchase_invoices as pi', 'pil.purchase_invoice_id', '=', 'pi.id')
            ->leftJoin('tbl_partners as partner', 'pi.partner_id', '=', 'partner.id')
            ->leftJoin('tbl_warehouses as warehouse', 'pi.warehouse_id', '=', 'warehouse.id')
            ->join('tbl_items as item', 'pil.item_id', '=', 'item.id')
            ->join('tbl_categories as category', 'item.category_id', '=', 'category.id')
            ->leftJoin('tbl_employees as emp', 'pi.createdBy_id', '=', 'emp.id')
            ->whereBetween('pi.date', [$from, $to])
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category.name', 'like', '%' . $category . '%');
            })
            ->when($partner !== '', function ($query) use ($partner) {
                $query->where('partner.name', 'like', '%' . $partner . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('category.name', 'like', '%' . $q . '%')
                        ->orWhere('item.name', 'like', '%' . $q . '%')
                        ->orWhere('item.code', 'like', '%' . $q . '%')
                        ->orWhere('partner.name', 'like', '%' . $q . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $q . '%')
                        ->orWhere('emp.name', 'like', '%' . $q . '%')
                        ->orWhere('pi.id', 'like', '%' . $q . '%');
                });
            });

        $selectQuery = (clone $baseQuery)
            ->select([
                'partner.name as Partner',
                'category.name as Category',
                'item.name as ItemName',
                'item.code as ItemCode',
                'pi.id',
                'pi.date',
                'pil.quantity as purchaseQuantity',
                'pil.purchaseUom',
                'pil.purchaseConversion',
                DB::raw('(pil.quantity * pil.purchaseConversion) as quantity'),
                'item.uom',
                DB::raw('(pil.unitPrice / NULLIF(pil.purchaseConversion, 0)) as unitCost'),
                DB::raw('(pil.quantity * pil.unitPrice) as total'),
                'warehouse.name as Warehouse',
                'emp.name as CreateBy',
            ])
            ->orderBy('Partner')
            ->orderBy('Category')
            ->orderBy('ItemName')
            ->orderBy('pi.id');

        if ($export === 'csv') {
            $rows = (clone $selectQuery)->get();
            return $this->exportGroupedCsv($rows, $start, $end, $category, $partner, $q);
        }

        $rows = (clone $selectQuery)->get();

        $groupedRows = $rows->groupBy(function ($row) {
            return $row->Partner ?: 'No Partner';
        })->map(function ($items, $partnerName) {
            return [
                'partner' => $partnerName,
                'items' => $items,
                'subtotal' => $items->sum(fn ($row) => (float) $row->total),
            ];
        });

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_lines,
                SUM(pil.quantity * pil.unitPrice) as grand_total
            ')
            ->first();

        return view('reports.purchase-detail-partner', [
            'title' => 'Purchase Detail Report Group By Partner',
            'groupedRows' => $groupedRows,
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'partner' => $partner,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    private function exportGroupedCsv($rows, $start, $end, $category, $partner, $q): StreamedResponse
    {
        $filename = 'purchase-detail-group-by-partner-' . now()->format('Ymd_His') . '.csv';

        $groupedRows = $rows->groupBy(function ($row) {
            return $row->Partner ?: 'No Partner';
        });

        $grandTotal = $rows->sum(fn ($row) => (float) $row->total);

        return response()->streamDownload(function () use ($groupedRows, $grandTotal, $start, $end, $category, $partner, $q) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Purchase Detail Report Group By Partner']);
            fputcsv($handle, ['Start Date', $start]);
            fputcsv($handle, ['End Date', $end]);
            fputcsv($handle, ['Category Filter', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Partner Filter', $partner !== '' ? $partner : 'All']);
            fputcsv($handle, ['Search Filter', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            foreach ($groupedRows as $partnerName => $items) {
                fputcsv($handle, [$partnerName]);

                fputcsv($handle, [
                    'Category',
                    'Item Name',
                    'Item Code',
                    'GR No.',
                    'Date',
                    'Purc. Qty',
                    'Purc. UOM',
                    'Conversion',
                    'Quantity',
                    'UOM',
                    'Unit Cost',
                    'Total',
                    'Warehouse',
                    'CreateBy',
                ]);

                foreach ($items as $row) {
                    fputcsv($handle, [
                        $row->Category,
                        $row->ItemName,
                        $row->ItemCode,
                        $row->id,
                        $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '',
                        (float) $row->purchaseQuantity,
                        $row->purchaseUom,
                        (float) $row->purchaseConversion,
                        (float) $row->quantity,
                        $row->uom,
                        (float) $row->unitCost,
                        (float) $row->total,
                        $row->Warehouse,
                        $row->CreateBy,
                    ]);
                }

                $partnerSubtotal = $items->sum(fn ($row) => (float) $row->total);

                fputcsv($handle, [
                    'TOTAL',
                    $partnerName,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    (float) $partnerSubtotal,
                    '',
                    '',
                ]);

                fputcsv($handle, []);
            }

            fputcsv($handle, [
                'GRAND TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                (float) $grandTotal,
                '',
                '',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}