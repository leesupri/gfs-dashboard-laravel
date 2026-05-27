@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ (request('start') || request('end')) ? 'true' : 'false' }} }" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-lg font-semibold">{{ $title }}</h1>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.wasteSummary', array_merge(request()->query(), ['export'=>'csv'])) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
               style="border-color:var(--card-border); color:var(--text-secondary)">
                Export
            </a>
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
        <form method="GET" class="flex gap-2 items-end border rounded-xl p-4 bg-white">
            <div>
                <label for="f-start" class="text-xs text-gray-500 block">Start</label>
                <input id="f-start" type="date" name="start" value="{{ $start }}" class="border px-3 py-2 rounded">
            </div>
            <div>
                <label for="f-end" class="text-xs text-gray-500 block">End</label>
                <input id="f-end" type="date" name="end" value="{{ $end }}" class="border px-3 py-2 rounded">
            </div>

            <button class="bg-black text-white px-4 py-2 rounded">
                Apply
            </button>
        </form>
    </div>

    <!-- DATA -->
    @foreach($grouped as $group)
        <div class="border rounded-xl overflow-hidden">

            <!-- CATEGORY -->
            <div class="bg-gray-100 px-4 py-2 font-semibold">
                {{ $group['category'] }}
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2">Name</th>
                        <th class="text-left px-3 py-2">Code</th>
                        <th class="text-right px-3 py-2">Qty</th>
                        <th class="text-left px-3 py-2">UOM</th>
                        <th class="text-right px-3 py-2">Unit Cost</th>
                        <th class="text-right px-3 py-2">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($group['items'] as $row)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $row->name }}</td>
                            <td class="px-3 py-2">{{ $row->code }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->quantity,2) }}</td>
                            <td class="px-3 py-2">{{ $row->uom }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->unitCost,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->total,2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- CATEGORY TOTAL -->
            <div class="bg-gray-100 px-4 py-2 text-right font-bold">
                Category Total: {{ number_format($group['total'],2) }}
            </div>
        </div>
    @endforeach

    <!-- GRAND TOTAL -->
    <div class="bg-black text-white px-4 py-3 text-right font-bold text-lg">
        GRAND TOTAL: {{ number_format($grandTotal,2) }}
    </div>

</div>
@endsection
