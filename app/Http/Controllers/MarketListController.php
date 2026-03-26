<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketListController extends Controller
{
    public function marketList(Request $request)
    {
        $start = $request->get('start', '');
        $end = $request->get('end', '');
        $category = trim((string) $request->get('category', ''));
        $q = trim((string) $request->get('q', ''));
        $export = trim((string) $request->get('export', ''));

        $baseQuery = DB::table('tbl_items as i')
            ->leftJoin('tbl_categories as c', 'c.id', '=', 'i.category_id')
            ->where(function ($query) {
                $query->where('i.stocked', 1)
                    ->orWhere('i.purchased', 1);
            })
            ->whereRaw('CAST(i.active AS UNSIGNED) = 1')
            ->when($start !== '' && $end !== '', function ($query) use ($start, $end) {
                $from = Carbon::parse($start)->startOfDay();
                $to = Carbon::parse($end)->endOfDay();

                $query->where(function ($sub) use ($from, $to) {
                    $sub->whereBetween('i.saved', [$from, $to])
                        ->orWhereBetween('i.updated', [$from, $to]);
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('c.name', 'like', '%' . $category . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('i.name', 'like', '%' . $q . '%')
                        ->orWhere('i.code', 'like', '%' . $q . '%')
                        ->orWhere('i.barcode', 'like', '%' . $q . '%')
                        ->orWhere('i.plu', 'like', '%' . $q . '%')
                        ->orWhere('c.name', 'like', '%' . $q . '%')
                        ->orWhere('i.uom', 'like', '%' . $q . '%')
                        ->orWhere('i.purchaseUom', 'like', '%' . $q . '%')
                        ->orWhere('i.recipeUom', 'like', '%' . $q . '%');
                });
            });

        if ($export === 'csv') {
            return $this->exportCsv(clone $baseQuery, $start, $end, $category, $q);
        }

        $rows = (clone $baseQuery)
            ->select([
                'c.id as category_id',
                'c.code as category_code',
                'c.name as category',
                'i.id as item_id',
                'i.code as item_code',
                'i.name as item_name',
                'i.barcode',
                'i.plu',
                'i.uom as inventory_uom',
                'i.purchaseUom as purchase_uom',
                'i.recipeUom as recipe_uom',
                'i.purchaseToInventoryConversion as purchase_to_inventory_conversion',
                'i.inventoryToRecipeConversion as inventory_to_recipe_conversion',
                'i.purchasePrice as purchase_price',
                DB::raw('COALESCE(i.averageCost, 0) as average_cost'),
                'i.saved',
                'i.updated',
            ])
            ->orderByDesc('i.updated')
            ->orderByDesc('i.saved')
            ->orderBy('c.name')
            ->orderBy('i.name')
            ->paginate(100)
            ->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_items,
                AVG(COALESCE(i.averageCost, 0)) as average_cost_mean
            ')
            ->first();

        return view('reports.market-list', [
            'title' => 'Market List Report',
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
        $filename = 'market-list-' . now()->format('Ymd_His') . '.csv';

        $rows = $baseQuery
            ->select([
                'c.code as category_code',
                'c.name as category',
                'i.id as item_id',
                'i.code as item_code',
                'i.name as item_name',
                'i.barcode',
                'i.plu',
                'i.uom as inventory_uom',
                'i.purchaseUom as purchase_uom',
                'i.recipeUom as recipe_uom',
                'i.purchaseToInventoryConversion as purchase_to_inventory_conversion',
                'i.inventoryToRecipeConversion as inventory_to_recipe_conversion',
                'i.purchasePrice as purchase_price',
                DB::raw('COALESCE(i.averageCost, 0) as average_cost'),
                'i.saved',
                'i.updated',
            ])
            ->orderByDesc('i.updated')
            ->orderByDesc('i.saved')
            ->orderBy('c.name')
            ->orderBy('i.name')
            ->get();

        return response()->streamDownload(function () use ($rows, $start, $end, $category, $q) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Market List Report']);
            fputcsv($handle, ['Start Date', $start !== '' ? $start : 'All Dates']);
            fputcsv($handle, ['End Date', $end !== '' ? $end : 'All Dates']);
            fputcsv($handle, ['Category Filter', $category !== '' ? $category : 'All']);
            fputcsv($handle, ['Search Filter', $q !== '' ? $q : 'All']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Category Code',
                'Category',
                'Item ID',
                'Item Code',
                'Item Name',
                'Barcode',
                'PLU',
                'Inventory UOM',
                'Purchase UOM',
                'Recipe UOM',
                'Purchase To Inventory Conversion',
                'Inventory To Recipe Conversion',
                'Purchase Price',
                'Average Cost',
                'Saved',
                'Updated',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->category_code,
                    $row->category,
                    $row->item_id,
                    $row->item_code,
                    $row->item_name,
                    $row->barcode,
                    $row->plu,
                    $row->inventory_uom,
                    $row->purchase_uom,
                    $row->recipe_uom,
                    (float) $row->purchase_to_inventory_conversion,
                    (float) $row->inventory_to_recipe_conversion,
                    number_format((float) $row->purchase_price, 2, ',', '.'),
                    number_format((float)$row->average_cost, 2, ',', '.'),
                    $row->saved,
                    $row->updated,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}