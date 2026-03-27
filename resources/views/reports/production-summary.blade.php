@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Production summary by category, item, UOM, and warehouse.
            </p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500">Start</label>
                <input type="date" name="start" value="{{ $start }}"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">End</label>
                <input type="date" name="end" value="{{ $end }}"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Category</label>
                <input type="text" name="category" value="{{ $category }}" placeholder="Category"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Warehouse</label>
                <input type="text" name="warehouse" value="{{ $warehouse }}" placeholder="Warehouse"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Item / code / uom"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.productionSummary') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>

            <a href="{{ route('reports.productionSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Export CSV
            </a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Date Range</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
                -
                {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Lines</div>
            <div class="font-semibold text-gray-900">
                {{ number_format($summary->total_lines ?? 0) }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Quantity</div>
            <div class="font-semibold text-gray-900">
                {{ number_format((float)($summary->total_quantity ?? 0), 2, '.', ',') }}
            </div>
        </div>
    </div>

    @if($rows->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
            No production data found.
        </div>
    @else
        @php
            $grouped = collect($rows->items())->groupBy('category');
        @endphp

        @foreach($grouped as $groupCategory => $items)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="font-semibold text-gray-900">
                        {{ $groupCategory ?: 'Uncategorized' }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left">Item Name</th>
                                <th class="px-4 py-3 text-left">Item Code</th>
                                <th class="px-4 py-3 text-right">Quantity</th>
                                <th class="px-4 py-3 text-left">UOM</th>
                                <th class="px-4 py-3 text-left">Warehouse</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">
                                        {{ $row->item_name ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $row->item_code ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format((float)$row->quantity, 2, '.', ',') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $row->uom ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $row->warehouse ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="px-4 py-3">
                {{ $rows->links() }}
            </div>
        </div>
    @endif
</div>
@endsection