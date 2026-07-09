<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Models\StockCountLine;
use App\Services\UomConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCountApiController extends Controller
{
    public function __construct(private readonly UomConversionService $uomService) {}

    /** List stock counts belonging to the authenticated staff member. */
    public function index(Request $request): JsonResponse
    {
        $staffUser = app('currentStaffUser');

        $query = StockCount::with('lines')
            ->where('created_by', $staffUser->id)
            ->orderByDesc('count_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $counts = $query->paginate(20);

        return response()->json($counts);
    }

    /** Create a new draft stock count. */
    public function store(Request $request): JsonResponse
    {
        $staffUser = app('currentStaffUser');

        $data = $request->validate([
            'warehouse_id'   => 'required|integer',
            'warehouse_name' => 'required|string|max:100',
            'count_date'     => 'required|date',
            'count_type'     => 'required|in:daily,monthly',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $count = StockCount::create([
            ...$data,
            'status'     => 'draft',
            'created_by' => $staffUser->id,
        ]);

        return response()->json(['data' => $count->fresh()], 201);
    }

    /** Get a single stock count with its lines. */
    public function show(int $id): JsonResponse
    {
        $staffUser = app('currentStaffUser');

        $count = StockCount::with('lines')
            ->where('id', $id)
            ->where('created_by', $staffUser->id)
            ->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(['data' => $count]);
    }

    /** Add or replace a line on a draft count. */
    public function addLine(Request $request, int $id): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $count = StockCount::where('id', $id)->where('created_by', $staffUser->id)->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }
        if (! $count->isDraft()) {
            return response()->json(['message' => 'Count is not in draft status.'], 422);
        }

        $data = $request->validate([
            'item_id'     => 'required|integer',
            'item_code'   => 'nullable|string|max:50',
            'item_name'   => 'required|string|max:200',
            'item_uom'    => 'required|string|max:20',
            'qty_entered' => 'required|numeric|min:0',
            'uom_entered' => 'required|string|max:20',
            'notes'       => 'nullable|string|max:500',
        ]);

        // Calculate base UOM qty using the item record from POS
        $item = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->where('id', $data['item_id'])
            ->first();

        $qtyInBase = $item
            ? $this->uomService->convertToBase((float) $data['qty_entered'], $data['uom_entered'], $item)
            : (float) $data['qty_entered'];

        $line = StockCountLine::updateOrCreate(
            ['stock_count_id' => $count->id, 'item_id' => $data['item_id']],
            [
                'item_code'       => $data['item_code'] ?? null,
                'item_name'       => $data['item_name'],
                'item_uom'        => $data['item_uom'],
                'qty_entered'     => $data['qty_entered'],
                'uom_entered'     => $data['uom_entered'],
                'qty_in_base_uom' => $qtyInBase,
                'notes'           => $data['notes'] ?? null,
            ]
        );

        return response()->json(['data' => $line], 201);
    }

    /** Update a specific line on a draft count. */
    public function updateLine(Request $request, int $id, int $lineId): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $count = StockCount::where('id', $id)->where('created_by', $staffUser->id)->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }
        if (! $count->isDraft()) {
            return response()->json(['message' => 'Count is not in draft status.'], 422);
        }

        $line = StockCountLine::where('id', $lineId)->where('stock_count_id', $id)->first();
        if (! $line) {
            return response()->json(['message' => 'Line not found.'], 404);
        }

        $data = $request->validate([
            'qty_entered' => 'required|numeric|min:0',
            'uom_entered' => 'required|string|max:20',
            'notes'       => 'nullable|string|max:500',
        ]);

        $item = DB::connection('reports_mysql')
            ->table('tbl_items')
            ->where('id', $line->item_id)
            ->first();

        $qtyInBase = $item
            ? $this->uomService->convertToBase((float) $data['qty_entered'], $data['uom_entered'], $item)
            : (float) $data['qty_entered'];

        $line->update([
            'qty_entered'     => $data['qty_entered'],
            'uom_entered'     => $data['uom_entered'],
            'qty_in_base_uom' => $qtyInBase,
            'notes'           => $data['notes'] ?? $line->notes,
        ]);

        return response()->json(['data' => $line->fresh()]);
    }

    /** Remove a line from a draft count. */
    public function removeLine(int $id, int $lineId): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $count = StockCount::where('id', $id)->where('created_by', $staffUser->id)->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }
        if (! $count->isDraft()) {
            return response()->json(['message' => 'Count is not in draft status.'], 422);
        }

        $line = StockCountLine::where('id', $lineId)->where('stock_count_id', $id)->first();
        if (! $line) {
            return response()->json(['message' => 'Line not found.'], 404);
        }

        $line->delete();

        return response()->json(['message' => 'Line removed.']);
    }

    /** Submit a draft count for approval. */
    public function submit(int $id): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $count = StockCount::with('lines')->where('id', $id)->where('created_by', $staffUser->id)->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }
        if (! $count->isDraft()) {
            return response()->json(['message' => 'Count is not in draft status.'], 422);
        }
        if ($count->lines->isEmpty()) {
            return response()->json(['message' => 'Cannot submit a count with no lines.'], 422);
        }

        $count->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['data' => $count->fresh()]);
    }

    /** Delete a draft count (cannot delete submitted/approved). */
    public function destroy(int $id): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $count = StockCount::where('id', $id)->where('created_by', $staffUser->id)->first();

        if (! $count) {
            return response()->json(['message' => 'Not found.'], 404);
        }
        if (! $count->isDraft()) {
            return response()->json(['message' => 'Only draft counts can be deleted.'], 422);
        }

        $count->lines()->delete();
        $count->delete();

        return response()->json(['message' => 'Count deleted.']);
    }
}
