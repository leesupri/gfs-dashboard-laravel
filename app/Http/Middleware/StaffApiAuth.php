<?php

namespace App\Http\Middleware;

use App\Models\StaffUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $staffUser = StaffUser::with('permissions')
            ->where('api_token', $bearerToken)
            ->where('is_active', true)
            ->first();

        if (! $staffUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        app()->instance('currentStaffUser', $staffUser);

        return $next($request);
    }
}
