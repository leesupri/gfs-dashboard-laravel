@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- LEFT PANEL --}}
        <div class="xl:col-span-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-4">
                    <h2 class="text-base font-semibold text-gray-900">Staff List</h2>
                    <p class="mt-1 text-sm text-gray-500">Select a staff to manage route permissions.</p>

                    <form method="GET" action="{{ route('settings.security') }}" class="mt-4 space-y-3">
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
                    @forelse($staffUsers as $staff)
                        @php
                            $isSelected = $selectedStaff && $selectedStaff->id === $staff->id;
                            $permissionCount = $staff->permissions->where('can_view', true)->count();
                        @endphp

                        <a href="{{ route('settings.security', ['staff' => $staff->id, 'search' => $search, 'status' => $status]) }}"
                           class="block rounded-xl border px-4 py-3 transition {{ $isSelected ? 'border-gray-900 bg-gray-900 text-white shadow-md ring-1 ring-gray-900' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold">{{ $staff->name }}</div>
                                    <div class="truncate text-xs {{ $isSelected ? 'text-gray-200' : 'text-gray-500' }}">
                                        {{ $staff->username }} • {{ $staff->title ?: 'No title' }}
                                    </div>
                                    <div class="mt-1 text-[11px] {{ $isSelected ? 'text-gray-200' : 'text-gray-500' }}">
                                        {{ $permissionCount }} route(s) allowed
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
                @if($selectedStaff)
                    @php
                        $grantedRoutes = $selectedStaff->permissions->where('can_view', true)->pluck('route_name')->toArray();
                    @endphp

                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-900">Security Detail</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Manage page access for <span class="font-medium">{{ $selectedStaff->name }}</span>.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('settings.security.update', $selectedStaff) }}" class="p-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach($availableRoutes as $routeName => $label)
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50">
                                    <input
                                        type="checkbox"
                                        name="routes[]"
                                        value="{{ $routeName }}"
                                        {{ in_array($routeName, $grantedRoutes, true) ? 'checked' : '' }}
                                        class="rounded border-gray-300"
                                    >
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $label }}</div>
                                        <div class="text-xs text-gray-500">{{ $routeName }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                                Save Permissions
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-10 text-center">
                        <div class="text-lg font-semibold text-gray-900">No Staff Selected</div>
                        <p class="mt-2 text-sm text-gray-500">
                            Select a staff from the list to manage page permissions.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection