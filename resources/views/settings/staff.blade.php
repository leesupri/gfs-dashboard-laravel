@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Create Staff</h2>
        </div>

        <form method="POST" action="{{ route('settings.staff.store') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" placeholder="Manager / Purchasing / Admin">
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                    Create Staff
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @foreach($staffUsers as $staff)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $staff->name }}</div>
                        <div class="text-sm text-gray-500">{{ $staff->username }} • {{ $staff->title ?: 'No title' }}</div>
                    </div>
                    <div>
                        @if($staff->is_active)
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>
                        @else
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Inactive</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.staff.update', $staff) }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ $staff->name }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" value="{{ $staff->username }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" value="{{ $staff->title }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $staff->is_active ? 'checked' : '' }} class="rounded border-gray-300">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" placeholder="Leave blank to keep current">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                            Save Changes
                        </button>
                </form>

                        <form method="POST" action="{{ route('settings.staff.destroy', $staff) }}" onsubmit="return confirm('Delete this staff account?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
            </div>
        @endforeach
    </div>
</div>
@endsection