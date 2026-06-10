<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username'    => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'platform'    => ['nullable', 'in:android,ios'],
        ]);

        $user = StaffUser::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Account inactive'], 403);
        }

        $deviceName = $request->input('device_name', 'mobile');

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        if ($request->filled('platform')) {
            DeviceToken::updateOrCreate(
                ['staff_user_id' => $user->id, 'platform' => $request->platform],
                ['token' => '']
            );
        }

        return response()->json([
            'success'    => true,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'        => $user->id,
                'name'      => $user->name,
                'username'  => $user->username,
                'title'     => $user->title,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        $accessToken?->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $staffUser = app('currentStaffUser');

        return response()->json([
            'success'     => true,
            'user'        => [
                'id'        => $staffUser->id,
                'name'      => $staffUser->name,
                'username'  => $staffUser->username,
                'title'     => $staffUser->title,
                'is_active' => $staffUser->is_active,
            ],
            'permissions' => $staffUser->permissions()->pluck('route_name'),
        ]);
    }
}
