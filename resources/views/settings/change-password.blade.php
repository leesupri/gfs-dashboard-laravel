@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Change My Password</h2>
        </div>

        <form method="POST" action="{{ route('settings.changePassword.update') }}" class="space-y-4 p-6">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" name="current_password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                @error('current_password')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection