<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('staff_user_id')) {
            return redirect()->route('welcome');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = StaffUser::where('username', $validated['username'])->first();

        if (! $user || ! $user->is_active || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->withInput();
        }

        session([
            'staff_user_id' => $user->id,
        ]);

        $request->session()->regenerate();

        return redirect()->route('welcome');
    }

    public function showChangePassword()
{
    return view('settings.change-password', [
        'title' => 'Change Password',
    ]);
}

public function changePassword(Request $request)
{
    $validated = $request->validate([
        'current_password' => ['required', 'string'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ]);

    $staffUser = \App\Models\StaffUser::findOrFail(session('staff_user_id'));

    if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $staffUser->password)) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.',
        ]);
    }

    $staffUser->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
    $staffUser->save();

    return redirect()
        ->route('settings.changePassword')
        ->with('success', 'Password changed successfully.');
}

    public function logout(Request $request)
    {
        $request->session()->forget('staff_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}