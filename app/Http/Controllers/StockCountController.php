<?php

namespace App\Http\Controllers;

use App\Models\StaffAuditLog;
use App\Models\StockCount;
use App\Services\UomConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCountController extends Controller
{
    public function __construct(protected UomConversionService $uomConversion)
    {
    }

    public function index(Request $request)
    {
        $query = StockCount::with(['creator:id,name', 'approver:id,name'])
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

        if ($request->filled('search') || $request->filled('q')) {
            $search = $request->input('search', $request->input('q'));
            $query->where(function ($qr) use ($search) {
                $qr->where('warehouse_name', 'like', "%{$search}%")
                   ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $counts = $query->orderByDesc('count_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $warehouses = DB::connection('reports_mysql')
            ->table('tbl_warehouses')
            ->select('id', 'name', 'code')
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $stats = [
            'total'     => StockCount::count(),
            'draft'     => StockCount::draft()->count(),
            'submitted' => StockCount::submitted()->count(),
            'approved'  => StockCount::approved()->count(),
        ];

        return view('stock.index', [
            'title'      => 'Stock Counts',
            'counts'     => $counts,
            'warehouses' => $warehouses,
            'stats'      => $stats,
            'filters'    => $request->only(['warehouse_id', 'count_type', 'status', 'date_from', 'date_to', 'search', 'q']),
        ]);
    }

    public function show(int $id)
    {
        $count = StockCount::with(['lines', 'creator:id,name', 'approver:id,name'])->findOrFail($id);

        return view('stock.show', [
            'title' => 'Stock Count #' . $count->id,
            'count' => $count,
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $staffUser = app('currentStaffUser');

        if (!$staffUser->hasAccess('stock.approve')) {
            abort(403, 'You do not have permission to approve stock counts.');
        }

        $count = StockCount::findOrFail($id);

        if (!$count->canApprove()) {
            return redirect()->back()->with('error', 'Only submitted counts can be approved.');
        }

        $count->update([
            'status'      => 'approved',
            'approved_by' => $staffUser->id,
            'approved_at' => now(),
        ]);

        StaffAuditLog::create([
            'actor_staff_user_id'  => $staffUser->id,
            'target_staff_user_id' => null,
            'action'               => 'stock_count.approve',
            'description'          => "Approved stock count #{$count->id} ({$count->warehouse_name}, {$count->count_date->format('Y-m-d')}).",
        ]);

        return redirect()->back()->with('success', 'Stock count approved.');
    }

    public function reject(Request $request, int $id)
    {
        $staffUser = app('currentStaffUser');

        if (!$staffUser->hasAccess('stock.approve')) {
            abort(403, 'You do not have permission to reject stock counts.');
        }

        $request->validate([
            'rejected_reason' => ['required', 'string', 'max:500'],
        ]);

        $count = StockCount::findOrFail($id);

        if (!$count->canApprove()) {
            return redirect()->back()->with('error', 'Only submitted counts can be rejected.');
        }

        $count->update([
            'status'          => 'rejected',
            'rejected_reason' => $request->rejected_reason,
        ]);

        StaffAuditLog::create([
            'actor_staff_user_id'  => $staffUser->id,
            'target_staff_user_id' => null,
            'action'               => 'stock_count.reject',
            'description'          => "Rejected stock count #{$count->id} ({$count->warehouse_name}, {$count->count_date->format('Y-m-d')}): {$request->rejected_reason}",
        ]);

        return redirect()->back()->with('success', 'Stock count rejected.');
    }
}
