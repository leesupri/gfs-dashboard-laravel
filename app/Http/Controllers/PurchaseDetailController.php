<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            return $this->exportCsv(clone $baseQuery);
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

    protected function exportCsv($baseQuery)
    {
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

        $headers = [
            'Category', 'Item Name', 'Item Code', 'Invoice ID', 'Date',
            'Purchase Quantity', 'Purchase UOM', 'Purchase Conversion',
            'Inventory Quantity', 'Inventory UOM', 'Unit Cost', 'Total',
            'Supplier', 'Warehouse', 'Created By',
        ];

        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [
                $row->Category,
                $row->ItemName,
                $row->ItemCode,
                $row->id,
                $row->date,
                (float) $row->purchaseQuantity,
                $row->purchaseUom,
                (float) $row->purchaseConversion,
                (float) $row->quantity,
                $row->uom,
                (float) $row->unitCost,
                (float) $row->total,
                $row->Partner,
                $row->Warehouse,
                $row->CreateBy,
            ];
        }

        return ExcelExport::download(
            'purchase-detail-' . now()->format('Ymd_His') . '.xlsx',
            'Purchase Detail Report',
            $headers,
            $dataRows
        );
    }
}