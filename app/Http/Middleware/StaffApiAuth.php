<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class StaffApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $staffUser = $accessToken->tokenable;

        if (!$staffUser || !$staffUser->is_active) {
            return response()->json(['success' => false, 'message' => 'Account inactive'], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        app()->instance('currentStaffUser', $staffUser);
        $request->attributes->set('currentAccessToken', $accessToken);

        return $next($request);
    }
}
