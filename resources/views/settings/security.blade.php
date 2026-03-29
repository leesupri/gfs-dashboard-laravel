@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @foreach($staffUsers as $staff)
        @php
            $grantedRoutes = $staff->permissions->where('can_view', true)->pluck('route_name')->toArray();
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="font-semibold text-gray-900">{{ $staff->name }}</div>
                <div class="text-sm text-gray-500">{{ $staff->username }} • {{ $staff->title ?: 'No title' }}</div>
            </div>

            <form method="POST" action="{{ route('settings.security.update', $staff) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
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

                <div class="mt-6">
                    <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                        Save Permissions
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection