<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UomConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemApiController extends Controller
{
    public function __construct(protected UomConversionService $uomConversion)
    {
    }

    public function index(Request $request)
    {
        $query = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->leftJoin('tbl_categories as cat', 'tbl_items.category_id', '=', 'cat.id')
            ->select(
                'tbl_items.id',
                'tbl_items.code',
                'tbl_items.name',
                'tbl_items.uom',
                'tbl_items.recipeUom',
                'tbl_items.purchaseUom',
                'tbl_items.inventoryToRecipeConversion',
                'tbl_items.purchaseToInventoryConversion',
                'tbl_items.active',
                'tbl_items.category_id',
                'tbl_items.averageCost',
                'tbl_items.parLevel',
                'tbl_items.warningLevel',
                'cat.name as categoryName'
            )
            ->where('tbl_items.active', 1);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('tbl_items.name', 'like', "%{$search}%")
                  ->orWhere('tbl_items.code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('tbl_items.category_id', $request->category_id);
        }

        $query->orderBy('tbl_items.name');

        $paginator = $query->paginate(50);

        $items = collect($paginator->items())->map(function ($item) {
            $item->uom_options = $this->uomConversion->getUomOptions($item);
            return $item;
        });

        return response()->json([
            'success'      => true,
            'data'         => $items,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ]);
    }

    public function show(int $id)
    {
        $item = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->leftJoin('tbl_categories as cat', 'tbl_items.category_id', '=', 'cat.id')
            ->select(
                'tbl_items.id',
                'tbl_items.code',
                'tbl_items.name',
                'tbl_items.uom',
                'tbl_items.recipeUom',
                'tbl_items.purchaseUom',
                'tbl_items.inventoryToRecipeConversion',
                'tbl_items.purchaseToInventoryConversion',
                'tbl_items.active',
                'tbl_items.category_id',
                'tbl_items.averageCost',
                'tbl_items.parLevel',
                'tbl_items.warningLevel',
                'cat.name as categoryName'
            )
            ->where('tbl_items.id', $id)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->uom_options = $this->uomConversion->getUomOptions($item);

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }
}
