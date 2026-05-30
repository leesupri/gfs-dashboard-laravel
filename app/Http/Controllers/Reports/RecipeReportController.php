<?php

namespace App\Http\Controllers\Reports;

use App\Helpers\ExcelExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeReportController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sales = trim((string) $request->query('sales', ''));
        $purchased = trim((string) $request->query('purchased', ''));
        $stocked = trim((string) $request->query('stocked', ''));
        $hideZero = (string) $request->query('hide_zero', '0') === '1';

        $rows = $this->buildQuery($q, $sales, $purchased, $stocked, $hideZero)->get();

        $byRecipe = $rows->groupBy('recipeName');

        $grandTotal = $rows->sum('totalCost');

        return view('reports.recipe.index', [
            'title' => 'Recipe Report',
            'active' => 'reports-recipe',
            'rows' => $rows,
            'byRecipe' => $byRecipe,
            'grandTotal' => $grandTotal,
            'q' => $q,
            'sales' => $sales,
            'purchased' => $purchased,
            'stocked' => $stocked,
            'hideZero' => $hideZero,
        ]);
    }

    public function export(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $sales    = trim((string) $request->query('sales', ''));
        $purchased = trim((string) $request->query('purchased', ''));
        $stocked  = trim((string) $request->query('stocked', ''));
        $hideZero = (string) $request->query('hide_zero', '0') === '1';

        $rows     = $this->buildQuery($q, $sales, $purchased, $stocked, $hideZero)->get();
        $byRecipe = $rows->groupBy('recipeName');

        $headers = [
            'Recipe ID', 'Recipe Name', 'Production', 'UOM',
            'Expected Total', 'Actual Total', 'Expected / Unit', 'Actual / Unit',
            'Item Code', 'Item Name', 'Rec Qty', 'Recipe UOM', 'Inv Qty', 'Inv UOM',
            'Unit Cost', 'Avg Cost', 'Expected', 'Actual', 'Idx',
        ];

        $dataRows = [];
        foreach ($byRecipe as $recipeName => $items) {
            $first          = $items->first();
            $expectedTotal  = $items->sum('expectedTotal');
            $actualTotal    = $items->sum('actualTotal');
            $production     = (float) $first->production ?: 0;
            $expectedPerUnit = $production > 0 ? $expectedTotal / $production : 0;
            $actualPerUnit   = $production > 0 ? $actualTotal / $production : 0;

            foreach ($items as $r) {
                $dataRows[] = [
                    $r->recipeId,
                    $recipeName,
                    (float) $first->production,
                    $first->uom,
                    (float) $expectedTotal,
                    (float) $actualTotal,
                    (float) $expectedPerUnit,
                    (float) $actualPerUnit,
                    $r->itemCode,
                    $r->itemName,
                    (float) $r->RecQty,
                    $r->recipeUom,
                    (float) $r->InvQty,
                    $r->InvUom,
                    (float) $r->unitCost,
                    (float) $r->averageCost,
                    (float) $r->expectedTotal,
                    (float) $r->actualTotal,
                    $r->idx,
                ];
            }
        }

        return ExcelExport::download(
            'recipe_report_' . now()->format('Ymd_His') . '.xlsx',
            'Recipe Report',
            $headers,
            $dataRows
        );
    }

    private function buildQuery(string $q, string $sales, string $purchased, string $stocked, bool $hideZero)
    {
        $query = DB::connection('reports_mysql')->table('tbl_recipes as r')
            ->join('tbl_items as item', 'r.item_id', '=', 'item.id')
            ->join('tbl_items as recipe', 'r.recipe_item_id', '=', 'recipe.id')
            ->selectRaw("
                recipe.id AS recipeId,
                CASE WHEN recipe.sales = 1 THEN 'yes' ELSE 'no' END AS sales,
                CASE WHEN recipe.purchased = 1 THEN 'yes' ELSE 'no' END AS purchased,
                CASE WHEN recipe.stocked = 1 THEN 'yes' ELSE 'no' END AS stocked,
                recipe.name AS recipeName,
                recipe.production AS production,
                recipe.uom AS uom,
                item.id AS itemId,
                item.code AS itemCode,
                item.name AS itemName,
                item.averageCost AS averageCost,
                r.quantity AS RecQty,
                item.recipeUom AS recipeUom,
        (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0)) AS InvQty,
        item.uom AS InvUom,

        (item.purchasePrice / NULLIF(item.purchaseToInventoryConversion, 0)) AS unitCost,

        (
            (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0))
            * (item.purchasePrice / NULLIF(item.purchaseToInventoryConversion, 0))
        ) AS expectedTotal,

        (
            (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0))
            * COALESCE(item.averageCost, 0)
        ) AS actualTotal,

        r.idx AS idx
    ");
    
    

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('recipe.name', 'like', "%{$q}%")
                  ->orWhere('item.name', 'like', "%{$q}%");
            });
        }

        if ($sales !== '') {
            $query->where('recipe.sales', $sales === 'yes' ? 1 : 0);
        }

        if ($purchased !== '') {
            $query->where('recipe.purchased', $purchased === 'yes' ? 1 : 0);
        }

        if ($stocked !== '') {
            $query->where('recipe.stocked', $stocked === 'yes' ? 1 : 0);
        }

        if ($hideZero) {
            $query->whereRaw("
                COALESCE(
                    (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0))
                    * (item.purchasePrice / NULLIF(item.purchaseToInventoryConversion, 0)),
                    0
                ) <> 0
            ");
        }

        return $query
            ->orderBy('recipe.id')
            ->orderBy('r.idx');
    }
}