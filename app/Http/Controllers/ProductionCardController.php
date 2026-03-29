<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionCardController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', '');
        $end = $request->get('end', '');
        $warehouse = trim((string) $request->get('warehouse', ''));
        $q = trim((string) $request->get('q', ''));

        $baseQuery = DB::connection('reports_mysql')->table('tbl_productions as p')
            ->leftJoin('tbl_warehouses as w', 'p.warehouse_id', '=', 'w.id')
            ->leftJoin('tbl_production_lines as pl', 'pl.production_id', '=', 'p.id')
            ->leftJoin('tbl_items as i', 'pl.item_id', '=', 'i.id')
            ->when($start !== '' && $end !== '', function ($query) use ($start, $end) {
                $from = Carbon::parse($start)->startOfDay();
                $to = Carbon::parse($end)->endOfDay();
                $query->whereBetween('p.date', [$from, $to]);
            })
            ->when($warehouse !== '', function ($query) use ($warehouse) {
                $query->where('w.name', 'like', '%' . $warehouse . '%');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('p.id', 'like', '%' . $q . '%')
                        ->orWhere('w.name', 'like', '%' . $q . '%')
                        ->orWhere('p.notes', 'like', '%' . $q . '%')
                        ->orWhere('i.name', 'like', '%' . $q . '%');
                });
            });

        $rows = (clone $baseQuery)
    ->select([
        'p.id',
        'p.date',
        'w.name as warehouse',
        'p.notes',
        'p.savedBy',
        DB::raw('COUNT(DISTINCT pl.id) as total_lines'),
        DB::raw('COUNT(DISTINCT CASE WHEN pl.parent_id IS NULL THEN pl.id END) as total_products'),
    ])
    ->groupBy('p.id', 'p.date', 'w.name', 'p.notes', 'p.savedBy')
    ->orderByDesc('p.date')
    ->orderByDesc('p.id')
    ->paginate(50)
    ->withQueryString();
        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(DISTINCT p.id) as total_cards
            ')
            ->first();

        return view('reports.production-card-index', [
            'title' => 'Production Card List',
            'rows' => $rows,
            'start' => $start,
            'end' => $end,
            'warehouse' => $warehouse,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    public function show($id)
    {
        $rows = DB::connection('reports_mysql')->table('tbl_production_lines as itemProduct')
            ->join('tbl_productions', 'itemProduct.production_id', '=', 'tbl_productions.id')
            ->join('tbl_warehouses', 'tbl_productions.warehouse_id', '=', 'tbl_warehouses.id')
            ->join('tbl_items', 'itemProduct.item_id', '=', 'tbl_items.id')
            ->join('tbl_categories', 'tbl_items.category_id', '=', 'tbl_categories.id')
            ->join('tbl_production_lines as itemRecipe', 'itemProduct.id', '=', 'itemRecipe.parent_id')
            ->join('tbl_items as aa', 'itemRecipe.item_id', '=', 'aa.id')
            ->join('tbl_categories as bb', 'aa.category_id', '=', 'bb.id')
            ->where('tbl_productions.id', $id)
            ->select([
                'tbl_productions.id',
                'tbl_productions.date',
                'tbl_productions.savedBy',
                'tbl_warehouses.name as warehouse',
                'tbl_items.code as product_code',
                'tbl_items.name as product_name',
                'tbl_categories.name as category',
                'itemProduct.quantity as product_qty',
                'tbl_items.recipeUom as product_uom',
                'itemProduct.description as product_description',
                'aa.code as recipe_code',
                'aa.name as recipe_name',
                'bb.name as recipe_category',
                'itemRecipe.quantity as recipe_qty',
                'aa.recipeUom as recipe_uom',
                'itemRecipe.description as recipe_description',
                'tbl_productions.notes',
            ])
            ->orderBy('tbl_items.name')
            ->orderBy('aa.name')
            ->get();

        if ($rows->isEmpty()) {
            abort(404, 'Production card not found.');
        }

        $header = $rows->first();

        $products = $rows
            ->groupBy(function ($row) {
                return implode('|', [
                    $row->product_code,
                    $row->product_name,
                    $row->category,
                    $row->product_qty,
                    $row->product_uom,
                    $row->product_description,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'product_code' => $first->product_code,
                    'product_name' => $first->product_name,
                    'category' => $first->category,
                    'product_qty' => $first->product_qty,
                    'product_uom' => $first->product_uom,
                    'product_description' => $first->product_description,
                    'recipes' => $group->map(function ($row) {
                        return (object) [
                            'recipe_code' => $row->recipe_code,
                            'recipe_name' => $row->recipe_name,
                            'recipe_category' => $row->recipe_category,
                            'recipe_qty' => $row->recipe_qty,
                            'recipe_uom' => $row->recipe_uom,
                            'recipe_description' => $row->recipe_description,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return view('reports.production-card', [
            'title' => 'Production Card',
            'header' => $header,
            'products' => $products,
        ]);
    }
}