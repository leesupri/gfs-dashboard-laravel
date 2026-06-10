<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Models\StockCountLine;
use App\Services\UomConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCountApiController extends Controller
{
    public function __construct(protected UomConversionService $uomConversion)
    {
    }

    public function index(Request $request)
    {
        $staffUser = app('currentStaffUser');

        $query = StockCount::with(['creator:id,name', 'lines'])
            ->withCount('lines');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('count_type')) {
            $query->where('count_type', $request->count_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('count_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('count_date', '<=', $request->date_to);
        }

        if (!$staffUser->hasAccess('stock.approve')) {
            $query->where('created_by', $staffUser->id);
        }

        $query->orderByDesc('count_date')->orderByDesc('created_at');

        $paginator = $query->paginate(20);

        return response()->json([
            'success'      => true,
            'data'         => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ]);
    }

    public function show(int $id)
    {
        $staffUser = app('currentStaffUser');

        $count = StockCount::with(['lines', 'creator:id,name', 'approver:id,name'])->find($id);

        if (!$count) {
            return response()->json(['success' => false, 'message' => 'Stock count not found'], 404);
        }

        if ($count->created_by !== $staffUser->id && !$staffUser->hasAccess('stock.approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json(['success' => true, 'data' => $count]);
    }

    public function store(Request $request)
    {
        $staffUser = app('currentStaffUser');

        $request->validate([
            'warehouse_id' => ['required', 'integer'],
            'count_type'   => ['required', 'in:daily,monthly'],
            'count_date'   => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $warehouse = DB::connection('reports_mysql')
            ->table('tbl_warehouses')
            ->where('id', $request->warehouse_id)
            ->select('id', 'name')
            ->first();

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 422);
        }

        $count = StockCount::create([
            'warehouse_id'   => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'count_type'     => $request->count_type,
            'count_date'     => $request->count_date,
            'notes'          => $request->notes,
            'created_by'     => $staffUser->id,
            'status'         => 'draft',
        ]);

        return response()->json(['success' => true, 'data' => $count], 201);
    }

    public function update(Request $request, int $id)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Only draft counts can be edited'], 422);
        }

        $request->validate([
            'notes'      => ['nullable', 'string', 'max:500'],
            'count_date' => ['nullable', 'date'],
            'count_type' => ['nullable', 'in:daily,monthly'],
        ]);

        $count->update($request->only(['notes', 'count_date', 'count_type']));

        return response()->json(['success' => true, 'data' => $count]);
    }

    public function destroy(int $id)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Only draft counts can be deleted'], 422);
        }

        $count->delete();

        return response()->json(['success' => true, 'message' => 'Count deleted']);
    }

    public function submit(int $id)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Count is not in draft status'], 422);
        }

        if ($count->lines()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Cannot submit an empty count. Add items first.'], 422);
        }

        $count->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $count]);
    }

    public function upsertLines(Request $request, int $id)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Count is not editable'], 422);
        }

        $request->validate([
            'lines'               => ['required', 'array', 'min:1'],
            'lines.*.item_id'     => ['required', 'integer'],
            'lines.*.qty_entered' => ['required', 'numeric', 'min:0'],
            'lines.*.uom_entered' => ['required', 'string', 'max:50'],
            'lines.*.notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $itemIds = collect($request->lines)->pluck('item_id')->unique()->values();

        $items = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->whereIn('id', $itemIds)
            ->select('id', 'code', 'name', 'uom', 'recipeUom', 'purchaseUom', 'inventoryToRecipeConversion', 'purchaseToInventoryConversion')
            ->get()
            ->keyBy('id');

        $warnings = [];

        foreach ($request->lines as $line) {
            $item = $items->get($line['item_id']);

            if (!$item) {
                $warnings[] = "Item id {$line['item_id']} not found — skipped.";
                continue;
            }

            $qtyBase = $this->uomConversion->convertToBase((float) $line['qty_entered'], $line['uom_entered'], $item);

            StockCountLine::updateOrCreate(
                ['stock_count_id' => $id, 'item_id' => $line['item_id']],
                [
                    'item_code'       => $item->code,
                    'item_name'       => $item->name,
                    'item_uom'        => $item->uom,
                    'qty_entered'     => $line['qty_entered'],
                    'uom_entered'     => $line['uom_entered'],
                    'qty_in_base_uom' => $qtyBase,
                    'notes'           => $line['notes'] ?? null,
                ]
            );
        }

        return response()->json([
            'success'  => true,
            'data'     => $count->fresh('lines')->lines,
            'warnings' => $warnings,
        ]);
    }

    public function updateLine(Request $request, int $id, int $lineId)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Count is not editable'], 422);
        }

        $line = StockCountLine::where('stock_count_id', $id)->where('id', $lineId)->first();

        if (!$line) {
            return response()->json(['success' => false, 'message' => 'Line not found'], 404);
        }

        $request->validate([
            'qty_entered' => ['required', 'numeric', 'min:0'],
            'uom_entered' => ['required', 'string', 'max:50'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $item = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->where('id', $line->item_id)
            ->select('id', 'code', 'name', 'uom', 'recipeUom', 'purchaseUom', 'inventoryToRecipeConversion', 'purchaseToInventoryConversion')
            ->first();

        $qtyBase = $item
            ? $this->uomConversion->convertToBase((float) $request->qty_entered, $request->uom_entered, $item)
            : (float) $request->qty_entered;

        $line->update([
            'qty_entered'     => $request->qty_entered,
            'uom_entered'     => $request->uom_entered,
            'qty_in_base_uom' => $qtyBase,
            'notes'           => $request->notes,
        ]);

        return response()->json(['success' => true, 'data' => $line]);
    }

    public function destroyLine(int $id, int $lineId)
    {
        $staffUser = app('currentStaffUser');

        $count = $this->findOwned($id, $staffUser);

        if ($count instanceof \Illuminate\Http\JsonResponse) {
            return $count;
        }

        if (!$count->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Count is not editable'], 422);
        }

        $line = StockCountLine::where('stock_count_id', $id)->where('id', $lineId)->first();

        if (!$line) {
            return response()->json(['success' => false, 'message' => 'Line not found'], 404);
        }

        $line->delete();

        return response()->json(['success' => true]);
    }

    private function findOwned(int $id, $staffUser)
    {
        $count = StockCount::find($id);

        if (!$count) {
            return response()->json(['success' => false, 'message' => 'Stock count not found'], 404);
        }

        if ($count->created_by !== $staffUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return $count;
    }
}
