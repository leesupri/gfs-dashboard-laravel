<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search      = trim((string) $request->get('q', ''));
        $category    = trim((string) $request->get('category', ''));
        $warehouseId = $request->get('warehouse_id', '');

        $query = DB::connection('reports_mysql')
            ->table('tbl_items as i')
            ->leftJoin('tbl_categories as c', 'c.id', '=', 'i.category_id')
            ->where(function ($q) {
                $q->where('i.stocked', 1)->orWhere('i.purchased', 1);
            })
            ->whereRaw('CAST(i.active AS UNSIGNED) = 1')
            ->select([
                'i.id',
                'i.code',
                'i.name',
                'i.uom',
                'i.purchaseUom as purchase_uom',
                'i.recipeUom as recipe_uom',
                'i.purchaseToInventoryConversion as purchase_conversion',
                'i.inventoryToRecipeConversion as recipe_conversion',
                'c.name as category',
            ])
            ->orderBy('c.name')
            ->orderBy('i.name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('i.name', 'like', "%{$search}%")
                  ->orWhere('i.code', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->where('c.name', 'like', "%{$category}%");
        }

        $rows = $query->get();

        return response()->json(['data' => $rows]);
    }

    public function show(int $id): JsonResponse
    {
        $item = DB::connection('reports_mysql')
            ->table('tbl_items as i')
            ->leftJoin('tbl_categories as c', 'c.id', '=', 'i.category_id')
            ->where('i.id', $id)
            ->select([
                'i.id',
                'i.code',
                'i.name',
                'i.uom',
                'i.purchaseUom as purchase_uom',
                'i.recipeUom as recipe_uom',
                'i.purchaseToInventoryConversion as purchase_conversion',
                'i.inventoryToRecipeConversion as recipe_conversion',
                'c.name as category',
            ])
            ->first();

        if (! $item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        return response()->json(['data' => $item]);
    }
}
