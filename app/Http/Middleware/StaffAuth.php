<?php

namespace App\Http\Middleware;

use App\Models\StaffUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $staffUserId = session('staff_user_id');

        if (! $staffUserId) {
            return redirect()->route('login');
        }

        $staffUser = StaffUser::find($staffUserId);

        if (! $staffUser || ! $staffUser->is_active) {
            $request->session()->forget('staff_user_id');
            return redirect()->route('login');
        }

        app()->instance('currentStaffUser', $staffUser);
        view()->share('currentStaffUser', $staffUser);

        return $next($request);
    }
}