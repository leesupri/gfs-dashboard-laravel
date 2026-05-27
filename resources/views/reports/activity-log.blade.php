@php
function activityBadge($text) {
    $t = strtoupper($text ?? '');

    if (str_contains($t, 'LOGIN')) return 'bg-green-100 text-green-700';
    if (str_contains($t, 'LOGOUT')) return 'bg-gray-100 text-gray-700';
    if (str_contains($t, 'VOID')) return 'bg-red-100 text-red-700';
    if (str_contains($t, 'PAYMENT')) return 'bg-blue-100 text-blue-700';
    if (str_contains($t, 'CLOSE')) return 'bg-purple-100 text-purple-700';

    return 'bg-yellow-100 text-yellow-700';
}
@endphp
@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Activity log by date, description, sales id, invoice id, and employee.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="filtersOpen = !filtersOpen"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
                :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
            </button>
        </div>
    </div>

    <div
        x-show="filtersOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >
        <form method="GET" class="gfs-card p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="f-start" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Start</label>
                    <input id="f-start" type="date" name="start" value="{{ $start }}"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-end" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">End</label>
                    <input id="f-end" type="date" name="end" value="{{ $end }}"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-employee" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Employee</label>
                    <input id="f-employee" type="text" name="employee" value="{{ $employee }}" placeholder="Employee name"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-invoice" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Invoice</label>
                    <input id="f-invoice" type="text" name="invoice" value="{{ $invoice }}" placeholder="Invoice ID"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
                    <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Description / Sales ID"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
                <a href="{{ route('reports.activityLog') }}"
                   class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
                   style="border-color:var(--card-border); color:var(--text-secondary)">Clear</a>
                <button type="submit"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">From</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">To</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Logs</div>
            <div class="font-semibold text-gray-900">
                {{ number_format($summary->total_logs ?? 0) }}
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        @if($rows->isEmpty())
            <div class="p-6 text-sm text-gray-500">
                No activity log data found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Sales ID</th>
                            <th class="px-4 py-3 text-left">Invoice ID</th>
                            <th class="px-4 py-3 text-left">Employee</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-gray-900">
    <div>{{ $row->description ?: '-' }}</div>
    <span class="inline-flex mt-1 rounded-full px-2 py-0.5 text-xs font-medium {{ activityBadge($row->description) }}">
        Activity
    </span>
</td>
                                <td class="px-4 py-2">
                                    {{ $row->salesId ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $row->invoice_id ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $row->employee_name ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
