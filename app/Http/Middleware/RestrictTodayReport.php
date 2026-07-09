<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RestrictTodayReport
{
    public function handle(Request $request, Closure $next): Response
    {
        $staffUser = app()->has('currentStaffUser') ? app('currentStaffUser') : null;
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $start     = (string) ($request->get('start') ?? $today);
        $end       = (string) ($request->get('end')   ?? $today);

        // Short-circuit: only query the DB when the flag is set and today is in range
        $shouldRestrict = $staffUser
            && $staffUser->restrict_today_report
            && $start <= $today
            && $end >= $today
            && !DB::connection('reports_mysql')
                ->table('tbl_daily_procedures')
                ->whereDate('date', $today)
                ->whereNotNull('closedBy_id')
                ->exists();

        if (!$shouldRestrict) {
            return $next($request);
        }

        // Clamp date range to yesterday so the user can still filter other dates
        $params        = $request->query();
        $params['end'] = $yesterday;
        if ($start >= $today) {
            $params['start'] = $yesterday;
        }

        return redirect($request->url() . '?' . http_build_query($params))
            ->with('warning', "Today's data is not available until end-of-day closing is completed in POS.");
    }
}
