<?php

namespace App\Http\Controllers\Reports;

use App\Helpers\ExcelExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeBoardController extends Controller
{
    public function index(Request $request)
    {
        $q           = trim((string) $request->query('q', ''));
        $sales       = trim((string) $request->query('sales', ''));
        $purchased   = trim((string) $request->query('purchased', ''));
        $stocked     = trim((string) $request->query('stocked', ''));
        $active      = trim((string) $request->query('active', ''));
        $category    = trim((string) $request->query('category', ''));
        $hasConversi = (string) $request->query('has_conversi', '0') === '1';
        $currentPage = max(1, (int) $request->query('page', 1));
        $perPage     = 20;

        // ── Categories for filter dropdown ────────────────────────────────
        $categories = DB::connection('reports_mysql')
            ->table('tbl_categories')
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        // ── Main query ────────────────────────────────────────────────────
        $rows = $this->buildQuery($q, $sales, $purchased, $stocked, $active, $category, $hasConversi)->get();

        $allByRecipe = $rows->groupBy('recipeId')->sortBy('recipeName');

        // ── Paginate the grouped collection ───────────────────────────────
        $total    = $allByRecipe->count();
        $byRecipe = $allByRecipe->forPage($currentPage, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $byRecipe,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        // ── Scope all secondary queries to THIS page's recipes only ───────
        $pageRows = $rows->filter(
            fn ($row) => $byRecipe->has($row->recipeId)
        );

        // ── Conversi sub-recipes ──────────────────────────────────────────
        $conversiIds = $pageRows->filter(
            fn ($row) =>
                $row->itemStocked   === 'yes' &&
                $row->itemSales     === 'no'  &&
                $row->itemPurchased === 'no'
        )->pluck('itemId')->unique();

        $conversiDetails = collect();

        if ($conversiIds->isNotEmpty()) {
            $conversiRows = DB::connection('reports_mysql')
                ->table('tbl_recipes as r')
                ->join('tbl_items as item',   'r.item_id',        '=', 'item.id')
                ->join('tbl_items as recipe', 'r.recipe_item_id', '=', 'recipe.id')
                ->whereIn('r.recipe_item_id', $conversiIds)
                ->selectRaw("
                    r.recipe_item_id                                                   AS conversiId,
                    recipe.name                                                        AS conversiName,
                    recipe.production                                                  AS conversiProduction,
                    recipe.uom                                                         AS conversiUom,
                    item.code                                                          AS itemCode,
                    item.name                                                          AS itemName,
                    r.quantity                                                         AS RecQty,
                    item.recipeUom                                                     AS recipeUom,
                    (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0))         AS InvQty,
                    item.uom                                                           AS InvUom,
                    CASE WHEN item.active = 1 THEN 'yes' ELSE 'no' END                AS itemActive,
                    r.idx                                                              AS idx
                ")
                ->orderBy('r.recipe_item_id')
                ->orderBy('r.idx')
                ->get();

            $conversiDetails = $conversiRows->groupBy('conversiId');
        }

        // ── Modifier pages (sales items on this page only) ────────────────
        $salesRecipeIds = $pageRows
            ->filter(fn ($row) => $row->sales === 'yes')
            ->pluck('recipeId')
            ->unique();

        [$modifierData, $modifierPages, $modifierItems] =
            $this->buildModifierData($salesRecipeIds);

        // ── Combo sub-menus (combo ingredients on this page only) ─────────
        $comboIngredientIds = $pageRows->filter(
            fn ($row) => $row->itemCombo === 'yes' && $row->itemSales === 'yes'
        )->pluck('itemId')->unique();

        [$comboData, $comboPages, $comboItems] =
            $this->buildModifierData($comboIngredientIds);

        return view('reports.recipe-board.index', [
            'title'           => 'Recipe Board',
            'active'          => 'reports-recipe-board',
            'byRecipe'        => $byRecipe,
            'paginator'       => $paginator,
            'total'           => $total,
            'categories'      => $categories,
            'conversiDetails' => $conversiDetails,
            'modifierData'    => $modifierData,
            'modifierPages'   => $modifierPages,
            'modifierItems'   => $modifierItems,
            'comboData'       => $comboData,
            'comboPages'      => $comboPages,
            'comboItems'      => $comboItems,
            'q'               => $q,
            'sales'           => $sales,
            'purchased'       => $purchased,
            'stocked'         => $stocked,
            'activeFilter'    => $active,
            'categoryFilter'  => $category,
            'hasConversi'     => $hasConversi,
        ]);
    }

    public function export(Request $request)
    {
        $q           = trim((string) $request->query('q', ''));
        $sales       = trim((string) $request->query('sales', ''));
        $purchased   = trim((string) $request->query('purchased', ''));
        $stocked     = trim((string) $request->query('stocked', ''));
        $active      = trim((string) $request->query('active', ''));
        $category    = trim((string) $request->query('category', ''));
        $hasConversi = (string) $request->query('has_conversi', '0') === '1';

        $rows     = $this->buildQuery($q, $sales, $purchased, $stocked, $active, $category, $hasConversi)->get();
        $byRecipe = $rows->groupBy('recipeId')->sortBy('recipeName');

        $headers = [
            'Recipe ID', 'Recipe Name', 'Category', 'Active', 'Sales', 'Purchased', 'Stocked',
            'Production', 'Production UOM', 'Idx',
            'Item Code', 'Item Name', 'Item Active', 'Is Conversi',
            'Recipe Qty', 'Recipe UOM', 'Inv Qty', 'Inv UOM',
        ];

        $dataRows = [];
        foreach ($byRecipe as $items) {
            $first = $items->first();
            foreach ($items as $r) {
                $isConversi = ($r->itemStocked === 'yes' && $r->itemSales === 'no' && $r->itemPurchased === 'no')
                    ? 'yes' : 'no';

                $dataRows[] = [
                    $r->recipeId,
                    $r->recipeName,
                    $r->categoryName ?? '',
                    $r->isActive,
                    $r->sales,
                    $r->purchased,
                    $r->stocked,
                    (float) $first->production,
                    $first->uom,
                    $r->idx,
                    $r->itemCode,
                    $r->itemName,
                    $r->itemActive,
                    $isConversi,
                    (float) $r->RecQty,
                    $r->recipeUom,
                    (float) $r->InvQty,
                    $r->InvUom,
                ];
            }
        }

        return ExcelExport::download(
            'recipe_board_' . now()->format('Ymd_His') . '.xlsx',
            'Recipe Board',
            $headers,
            $dataRows
        );
    }

    private function buildQuery(
        string $q,
        string $sales,
        string $purchased,
        string $stocked,
        string $active,
        string $category,
        bool   $hasConversi
    ) {
        $query = DB::connection('reports_mysql')
            ->table('tbl_recipes as r')
            ->join('tbl_items as item',        'r.item_id',        '=', 'item.id')
            ->join('tbl_items as recipe',      'r.recipe_item_id', '=', 'recipe.id')
            ->leftJoin('tbl_categories as cat', 'recipe.category_id', '=', 'cat.id')
            ->selectRaw("
                recipe.id                                                              AS recipeId,
                recipe.name                                                            AS recipeName,
                recipe.production                                                      AS production,
                recipe.uom                                                             AS uom,
                CASE WHEN recipe.active    = 1 THEN 'yes' ELSE 'no' END               AS isActive,
                CASE WHEN recipe.sales     = 1 THEN 'yes' ELSE 'no' END               AS sales,
                CASE WHEN recipe.purchased = 1 THEN 'yes' ELSE 'no' END               AS purchased,
                CASE WHEN recipe.stocked   = 1 THEN 'yes' ELSE 'no' END               AS stocked,
                recipe.category_id                                                     AS categoryId,
                cat.name                                                               AS categoryName,
                cat.code                                                               AS categoryCode,
                item.id                                                                AS itemId,
                item.code                                                              AS itemCode,
                item.name                                                              AS itemName,
                CASE WHEN item.active    = 1 THEN 'yes' ELSE 'no' END                 AS itemActive,
                CASE WHEN item.sales     = 1 THEN 'yes' ELSE 'no' END                 AS itemSales,
                CASE WHEN item.purchased = 1 THEN 'yes' ELSE 'no' END                 AS itemPurchased,
                CASE WHEN item.stocked   = 1 THEN 'yes' ELSE 'no' END                 AS itemStocked,
                CASE WHEN item.combo     = 1 THEN 'yes' ELSE 'no' END                 AS itemCombo,
                r.quantity                                                             AS RecQty,
                item.recipeUom                                                         AS recipeUom,
                (r.quantity / NULLIF(item.inventoryToRecipeConversion, 0))             AS InvQty,
                item.uom                                                               AS InvUom,
                r.idx                                                                  AS idx
            ");

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('recipe.name', 'like', "%{$q}%")
                  ->orWhere('item.name',   'like', "%{$q}%")
                  ->orWhere('item.code',   'like', "%{$q}%")
                  ->orWhere('cat.name',    'like', "%{$q}%");
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

        if ($active !== '') {
            $query->where('recipe.active', $active === 'yes' ? 1 : 0);
        }

        if ($category !== '') {
            $query->where('recipe.category_id', $category);
        }

        if ($hasConversi) {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('tbl_recipes as r2')
                    ->join('tbl_items as ci', 'r2.item_id', '=', 'ci.id')
                    ->whereColumn('r2.recipe_item_id', 'recipe.id')
                    ->where('ci.stocked',   1)
                    ->where('ci.sales',     0)
                    ->where('ci.purchased', 0);
            });
        }

        return $query
            ->orderBy('recipe.id')
            ->orderBy('r.idx');
    }

    /**
     * buildModifierData()
     *
     * Iterative BFS — discovers modifier pages and their items at any depth.
     * Each level's items are checked for their own modifier1_id…modifier4_id
     * and queued into the next batch until no new pages are found.
     *
     * Returns:
     *   $modifierData  — collect() keyed by recipeId, value = array of top-level slots
     *                    each slot: ['slot' => n, 'pageId' => id]
     *   $modifierPages — collect() keyed by page id, value = page object
     *   $modifierItems — collect() keyed by page_id, value = collection of items
     *                    each item includes modifier1_id…modifier4_id for nested detection
     */
    private function buildModifierData(\Illuminate\Support\Collection $salesRecipeIds): array
    {
        $modifierData  = collect();
        $modifierPages = collect();
        $modifierItems = collect();

        if ($salesRecipeIds->isEmpty()) {
            return [$modifierData, $modifierPages, $modifierItems];
        }

        // ── Step 1: top-level modifier page IDs per recipe item ───────────
        $modifierLinks = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->whereIn('id', $salesRecipeIds)
            ->whereRaw('(modifier1_id IS NOT NULL
                      OR modifier2_id IS NOT NULL
                      OR modifier3_id IS NOT NULL
                      OR modifier4_id IS NOT NULL)')
            ->select(['id', 'modifier1_id', 'modifier2_id', 'modifier3_id', 'modifier4_id'])
            ->get()
            ->keyBy('id');

        if ($modifierLinks->isEmpty()) {
            return [$modifierData, $modifierPages, $modifierItems];
        }

        // ── Step 2: BFS — collect ALL pages + items at every depth ────────
        $visited = [];
        $queue   = $modifierLinks->flatMap(fn ($item) => [
            $item->modifier1_id,
            $item->modifier2_id,
            $item->modifier3_id,
            $item->modifier4_id,
        ])->filter()->unique()->values()->all();

        while (! empty($queue)) {

            // Only process pages we haven't seen yet
            $batch = array_values(array_unique(
                array_diff($queue, $visited)
            ));

            if (empty($batch)) {
                break;
            }

            $visited = array_merge($visited, $batch);
            $queue   = [];

            // Fetch page details for this batch
            $batchPages = DB::connection('reports_mysql')
                ->table('tbl_pages')
                ->whereIn('id', $batch)
                ->selectRaw("
                    id,
                    name,
                    CASE WHEN forcedModifier = 1 THEN 'yes' ELSE 'no' END AS isForced,
                    CASE WHEN active         = 1 THEN 'yes' ELSE 'no' END AS isActive
                ")
                ->get();

            foreach ($batchPages as $page) {
                $modifierPages[$page->id] = $page;
            }

            // Fetch items inside these pages
            // Include modifier columns so the blade can detect nested modifiers
            $batchItems = DB::connection('reports_mysql')
                ->table('tbl_items')
                ->whereIn('page_id', $batch)
                ->selectRaw("
                    id,
                    page_id,
                    code,
                    name,
                    position,
                    modifier1_id,
                    modifier2_id,
                    modifier3_id,
                    modifier4_id,
                    CASE WHEN active = 1 THEN 'yes' ELSE 'no' END AS isActive
                ")
                ->orderBy('page_id')
                ->orderBy('position')
                ->get();

            foreach ($batchItems->groupBy('page_id') as $pageId => $items) {
                $modifierItems[$pageId] = $items;
            }

            // Queue any new modifier page IDs found on this level's items
            foreach ($batchItems as $item) {
                foreach ([1, 2, 3, 4] as $n) {
                    $nestedId = $item->{"modifier{$n}_id"};
                    if ($nestedId && ! in_array($nestedId, $visited)) {
                        $queue[] = $nestedId;
                    }
                }
            }
        }

        // ── Step 3: build top-level slot index per recipe item ────────────
        foreach ($modifierLinks as $itemId => $link) {
            $slots = [];
            foreach ([1, 2, 3, 4] as $n) {
                $pageId = $link->{"modifier{$n}_id"};
                if ($pageId && isset($modifierPages[$pageId])) {
                    $slots[] = ['slot' => $n, 'pageId' => $pageId];
                }
            }
            if (! empty($slots)) {
                $modifierData[$itemId] = $slots;
            }
        }

        return [$modifierData, $modifierPages, $modifierItems];
    }
}