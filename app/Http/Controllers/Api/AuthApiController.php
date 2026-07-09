<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $staffUser = StaffUser::where('username', $request->username)
            ->where('is_active', true)
            ->first();

        if (! $staffUser || ! Hash::check($request->password, $staffUser->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = Str::random(64);
        $staffUser->update(['api_token' => $token]);

        return response()->json([
            'token' => $token,
            'staff_user' => [
                'id'    => $staffUser->id,
                'name'  => $staffUser->name,
                'title' => $staffUser->title,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $staffUser = app('currentStaffUser');
        $staffUser->update(['api_token' => null]);

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $staffUser = app('currentStaffUser');

        return response()->json([
            'id'    => $staffUser->id,
            'name'  => $staffUser->name,
            'title' => $staffUser->title,
        ]);
    }
}
