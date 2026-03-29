<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffSettingController extends Controller
{
    public function index()
    {
        $staffUsers = StaffUser::orderBy('name')->get();

        return view('settings.staff', [
            'title' => 'Staff Settings',
            'staffUsers' => $staffUsers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:staff_users,username'],
            'title' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        StaffUser::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'title' => $validated['title'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('settings.staff')
            ->with('success', 'Staff created successfully.');
    }

    public function update(Request $request, StaffUser $staffUser)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_users', 'username')->ignore($staffUser->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $staffUser->name = $validated['name'];
        $staffUser->username = $validated['username'];
        $staffUser->title = $validated['title'] ?? null;
        $staffUser->is_active = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $staffUser->password = Hash::make($validated['password']);
        }

        $staffUser->save();

        return redirect()
            ->route('settings.staff')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(StaffUser $staffUser)
    {
        $currentStaffId = session('staff_user_id');

        if ((int) $currentStaffId === (int) $staffUser->id) {
            return redirect()
                ->route('settings.staff')
                ->with('error', 'You cannot delete your own account.');
        }

        $staffUser->permissions()->delete();
        $staffUser->delete();

        return redirect()
            ->route('settings.staff')
            ->with('success', 'Staff deleted successfully.');
    }
}