<?php

namespace App\Http\Controllers;

use App\Models\UserPageLog;
use App\Models\StaffUser;
use Illuminate\Http\Request;

class UserPageLogController extends Controller
{
    public function index(Request $request)
    {
        $start    = $request->get('start', now()->toDateString());
        $end      = $request->get('end', now()->toDateString());
        $userId   = $request->get('staff_user_id', '');
        $status   = $request->get('status', '');  // 'ok' | 'error' | ''
        $route    = trim((string) $request->get('route', ''));

        $from = \Carbon\Carbon::parse($start)->startOfDay();
        $to   = \Carbon\Carbon::parse($end)->endOfDay();

        $query = UserPageLog::with('staffUser')
            ->whereBetween('created_at', [$from, $to])
            ->when($userId !== '', fn($q) => $q->where('staff_user_id', $userId))
            ->when($route  !== '', fn($q) => $q->where('route_name', 'like', "%{$route}%"))
            ->when($status === 'ok',    fn($q) => $q->where('status_code', '<', 400))
            ->when($status === 'error', fn($q) => $q->where('status_code', '>=', 400))
            ->orderByDesc('created_at');

        $rows = $query->paginate(100)->withQueryString();

        // Summaries for KPIs
        $totalRows   = $rows->total();
        $errorCount  = UserPageLog::whereBetween('created_at', [$from, $to])
            ->when($userId !== '', fn($q) => $q->where('staff_user_id', $userId))
            ->where('status_code', '>=', 400)
            ->count();

        // Staff list for filter dropdown
        $staffList = StaffUser::orderBy('name')->get(['id', 'name']);

        return view('settings.page-logs', compact(
            'rows', 'start', 'end', 'userId', 'status', 'route',
            'totalRows', 'errorCount', 'staffList'
        ));
    }
}
