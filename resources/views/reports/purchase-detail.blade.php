@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Purchase detail by invoice line.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.purchaseSummary', request()->query()) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
               style="border-color:var(--card-border); color:var(--text-secondary)">
                Back to Summary
            </a>
            <a href="{{ route('reports.purchaseDetailPartner', request()->query()) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                Group By Partner
            </a>
            <a href="{{ route('reports.purchaseDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
               style="border-color:var(--card-border); color:var(--text-secondary)">
                Export CSV
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
                    <label for="f-category" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Category</label>
                    <input id="f-category" type="text" name="category" value="{{ $category }}"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>
                <div>
                    <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
                    <input id="f-q" type="text" name="q" value="{{ $q }}"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
                <a href="{{ route('reports.purchaseDetail') }}"
                   class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
                   style="border-color:var(--card-border); color:var(--text-secondary)">Clear</a>
                <button type="submit"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Grand Total</div>
            <div class="font-semibold text-gray-900">
                Rp {{ number_format((float)($summary->grand_total ?? 0), 2, '.', ',') }}
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Lines</div>
            <div class="font-semibold text-gray-900">{{ number_format($summary->total_lines ?? 0) }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Item Name</th>
                        <th class="px-4 py-3 text-left">Item Code</th>
                        <th class="px-4 py-3 text-left">Invoice ID</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-right">Purchase Qty</th>
                        <th class="px-4 py-3 text-left">Purchase UOM</th>
                        <th class="px-4 py-3 text-right">Conversion</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-left">UOM</th>
                        <th class="px-4 py-3 text-right">Unit Cost</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Warehouse</th>
                        <th class="px-4 py-3 text-left">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $row->Category }}</td>
                            <td class="px-4 py-2">{{ $row->ItemName }}</td>
                            <td class="px-4 py-2">{{ $row->ItemCode }}</td>
                            <td class="px-4 py-2">{{ $row->id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$row->purchaseQuantity, 2, '.', ',') }}</td>
                            <td class="px-4 py-2">{{ $row->purchaseUom }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$row->purchaseConversion, 2, '.', ',') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$row->quantity, 2, '.', ',') }}</td>
                            <td class="px-4 py-2">{{ $row->uom }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$row->unitCost, 2, '.', ',') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$row->total, 2, '.', ',') }}</td>
                            <td class="px-4 py-2">{{ $row->Partner }}</td>
                            <td class="px-4 py-2">{{ $row->Warehouse }}</td>
                            <td class="px-4 py-2">{{ $row->CreateBy }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="px-4 py-6 text-sm text-gray-500">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-3">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
