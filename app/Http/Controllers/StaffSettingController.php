<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffSettingController extends Controller
{
    public function index(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $status = trim((string) $request->get('status', 'all'));

    $staffUsers = \App\Models\StaffUser::query()
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        })
        ->when($status === 'active', function ($query) {
            $query->where('is_active', true);
        })
        ->when($status === 'inactive', function ($query) {
            $query->where('is_active', false);
        })
        ->orderBy('name')
        ->get();

    $selectedStaff = null;

    if ($request->filled('staff')) {
        $selectedStaff = $staffUsers->firstWhere('id', (int) $request->get('staff'));

        if (!$selectedStaff) {
            $selectedStaff = \App\Models\StaffUser::find((int) $request->get('staff'));
        }
    }

    return view('settings.staff', [
        'title' => 'Staff Settings',
        'staffUsers' => $staffUsers,
        'selectedStaff' => $selectedStaff,
        'isCreateMode' => $request->boolean('new') || !$selectedStaff,
        'search' => $search,
        'status' => $status,
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'username'               => ['required', 'string', 'max:255', 'unique:staff_users,username'],
            'email'                  => ['nullable', 'email', 'max:150', 'unique:staff_users,email'],
            'title'                  => ['nullable', 'string', 'max:255'],
            'password'               => ['required', 'string', 'min:8', 'confirmed'],
            'is_active'              => ['nullable', 'boolean'],
            'restrict_today_report'  => ['nullable', 'boolean'],
        ]);

        StaffUser::create([
            'name'                  => $validated['name'],
            'username'              => $validated['username'],
            'email'                 => $validated['email'] ?? null,
            'title'                 => $validated['title'] ?? null,
            'password'              => Hash::make($validated['password']),
            'is_active'             => $request->boolean('is_active', true),
            'restrict_today_report' => $request->boolean('restrict_today_report'),
        ]);

        return redirect()
            ->route('settings.staff')
            ->with('success', 'Staff created successfully.');
    }

    public function update(Request $request, StaffUser $staffUser)
    {
        $validated = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'username'               => [
                'required', 'string', 'max:255',
                Rule::unique('staff_users', 'username')->ignore($staffUser->id),
            ],
            'email'                  => [
                'nullable', 'email', 'max:150',
                Rule::unique('staff_users', 'email')->ignore($staffUser->id),
            ],
            'title'                  => ['nullable', 'string', 'max:255'],
            'password'               => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active'              => ['nullable', 'boolean'],
            'restrict_today_report'  => ['nullable', 'boolean'],
        ]);

        $staffUser->name                 = $validated['name'];
        $staffUser->username             = $validated['username'];
        $staffUser->email                = $validated['email'] ?? null;
        $staffUser->title                = $validated['title'] ?? null;
        $staffUser->is_active            = $request->boolean('is_active');
        $staffUser->restrict_today_report = $request->boolean('restrict_today_report');

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
