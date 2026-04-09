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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- LEFT PANEL --}}
        <div class="xl:col-span-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Staff List</h2>
                            <p class="mt-1 text-sm text-gray-500">Select a staff to view or edit details.</p>
                        </div>

                        <a href="{{ route('settings.staff', ['new' => 1]) }}"
                           class="rounded-xl bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            + New
                        </a>
                    </div>

                    <form method="GET" action="{{ route('settings.staff') }}" class="mt-4 space-y-3">
                        <div>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search name / username / title"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm"
                            >
                        </div>

                        <div class="flex gap-2">
                            <select name="status" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>

                            <button type="submit"
                                    class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-3 space-y-2">
                    <a href="{{ route('settings.staff', ['new' => 1, 'search' => $search, 'status' => $status]) }}"
                       class="block rounded-xl border px-4 py-3 transition {{ $isCreateMode ? 'border-gray-900 bg-gray-900 text-white shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                        <div class="font-medium">Create New Staff</div>
                        <div class="text-xs {{ $isCreateMode ? 'text-gray-200' : 'text-gray-500' }}">
                            Add a new staff account
                        </div>
                    </a>

                    @forelse($staffUsers as $staff)
                        @php
                            $isSelected = !$isCreateMode && $selectedStaff && $selectedStaff->id === $staff->id;
                        @endphp

                        <a href="{{ route('settings.staff', ['staff' => $staff->id, 'search' => $search, 'status' => $status]) }}"
                           class="block rounded-xl border px-4 py-3 transition {{ $isSelected ? 'border-gray-900 bg-gray-900 text-white shadow-md ring-1 ring-gray-900' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold">{{ $staff->name }}</div>
                                    <div class="truncate text-xs {{ $isSelected ? 'text-gray-200' : 'text-gray-500' }}">
                                        {{ $staff->username }} • {{ $staff->title ?: 'No title' }}
                                    </div>
                                </div>

                                <div>
                                    @if($staff->is_active)
                                        <span class="rounded-full px-2 py-1 text-[10px] font-medium {{ $isSelected ? 'bg-green-500 text-white' : 'bg-green-100 text-green-700' }}">
                                            Active
                                        </span>
                                    @else
                                        <span class="rounded-full px-2 py-1 text-[10px] font-medium {{ $isSelected ? 'bg-red-500 text-white' : 'bg-red-100 text-red-700' }}">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                            No staff found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="xl:col-span-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                @if($isCreateMode)
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-900">Create Staff</h2>
                        <p class="mt-1 text-sm text-gray-500">Enter the details for a new staff account.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.staff.store') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                        @csrf

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" value="{{ old('username') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" placeholder="Manager / Purchasing / Admin">
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                                Create Staff
                            </button>
                        </div>
                    </form>
                @elseif($selectedStaff)
                    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Staff Detail</h2>
                            <p class="mt-1 text-sm text-gray-500">Update account information and password.</p>
                        </div>

                        <div>
                            @if($selectedStaff->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.staff.update', $selectedStaff) }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $selectedStaff->name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" value="{{ old('username', $selectedStaff->username) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" value="{{ old('title', $selectedStaff->title) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $selectedStaff->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm" placeholder="Leave blank to keep current">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3">
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                                Save Changes
                            </button>
                    </form>

                            <form method="POST" action="{{ route('settings.staff.destroy', $selectedStaff) }}" onsubmit="return confirm('Delete this staff account?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                @else
                    <div class="p-10 text-center">
                        <div class="text-lg font-semibold text-gray-900">No Staff Selected</div>
                        <p class="mt-2 text-sm text-gray-500">
                            Select a staff from the list or create a new one.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection