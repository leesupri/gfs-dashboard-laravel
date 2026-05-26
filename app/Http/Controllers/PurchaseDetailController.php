<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseDetailController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $category = trim((string) $request->get('category', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();

        $baseQuery = DB::connection('reports_mysql')->table('tbl_purchase_invoice_lines as pil')
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

        if ($export === 'csv') {
            return $this->exportCsv(clone $baseQuery, $start, $end, $category, $q);
        }

        $rows = (clone $baseQuery)
            ->select([
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
                'partner.name as Partner',
                'warehouse.name as Warehouse',
                'emp.name as CreateBy',
            ])
            ->orderBy('Category')
            ->orderBy('ItemName')
            ->orderBy('pi.id')
            ->paginate(100)
            ->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_lines,
                SUM(pil.quantity * pil.unitPrice) as grand_total
            ')
            ->first();

        return view('reports.purchase-detail', [
            'title' => 'Purchase Detail Report',
            'rows' => $rows,
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    protected function exportCsv($baseQuery, $start, $end, $category, $q): StreamedResponse
    {
        $filename = 'purchase-detail-' . now()->format('Ymd_His') . '.csv';

        $rows = $baseQuery
            ->select([
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
                'partner.name as Partner',
                'warehouse.name as Warehouse',
                'emp.name as CreateBy',
            ])
            ->orderBy('Category')
            ->orderBy('ItemName')
            ->orderBy('pi.id')
            ->get();

        return response()->streamDownload(function () use ($rows, $start, $end, $category, $q) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Purchase Detail Report']);
            fputcsv($handle, ['Start Date', $start]);
            fputcsv($handle, ['End Date', $end]);
            fputcsv($handle, ['Category Filter', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Search Filter', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Category',
                'Item Name',
                'Item Code',
                'Invoice ID',
                'Date',
                'Purchase Quantity',
                'Purchase UOM',
                'Purchase Conversion',
                'Inventory Quantity',
                'Inventory UOM',
                'Unit Cost',
                'Total',
                'Supplier',
                'Warehouse',
                'Created By',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->Category,
                    $row->ItemName,
                    $row->ItemCode,
                    $row->id,
                    $row->date,
                    number_format((float) $row->purchaseQuantity, 2, ',', '.'),
                    $row->purchaseUom,
                    number_format((float) $row->purchaseConversion, 2, ',', '.'),
                    number_format((float) $row->quantity, 2, ',', '.'),
                    $row->uom,
                    number_format((float) $row->unitCost, 2, ',', '.'),
                    number_format((float) $row->total, 2, ',', '.'),
                    $row->Partner,
                    $row->Warehouse,
                    $row->CreateBy,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}