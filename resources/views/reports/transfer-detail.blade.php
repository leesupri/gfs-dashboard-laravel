@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Transfer detail report grouped by category and item.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.transferDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
                    <label for="f-from" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">From</label>
                    <input id="f-from" type="text" name="from_warehouse" value="{{ $fromWarehouse }}" placeholder="From warehouse"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-to" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">To</label>
                    <input id="f-to" type="text" name="to_warehouse" value="{{ $toWarehouse }}" placeholder="To warehouse"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-category" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Category</label>
                    <input id="f-category" type="text" name="category" value="{{ $category }}" placeholder="Category"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-item" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Item</label>
                    <input id="f-item" type="text" name="item" value="{{ $item }}" placeholder="Item"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-transfer-id" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Transfer ID</label>
                    <input id="f-transfer-id" type="text" name="transfer_id" value="{{ $transferId }}" placeholder="Transfer ID"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>

                <div>
                    <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
                    <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Search"
                        class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                        style="border-color:var(--card-border); color:var(--text-primary)">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
                <a href="{{ route('reports.transferDetail') }}"
                   class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
                   style="border-color:var(--card-border); color:var(--text-secondary)">Clear</a>
                <button type="submit"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Transfers</div>
            <div class="font-semibold text-gray-900">{{ number_format($summary->total_transfers ?? 0) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Lines</div>
            <div class="font-semibold text-gray-900">{{ number_format($summary->total_lines ?? 0) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Quantity</div>
            <div class="font-semibold text-gray-900">{{ number_format((float)($summary->total_quantity ?? 0), 2, ',', '.') }}</div>
        </div>
    </div>

    @forelse($groupedRows as $categoryGroup)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-100 px-4 py-3">
                <div class="font-semibold text-gray-900">{{ $categoryGroup['category'] }}</div>
            </div>

            @foreach($categoryGroup['items'] as $itemGroup)
                <div class="border-b border-gray-200">
                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-800">{{ $itemGroup['item_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $itemGroup['item_code'] ?: '-' }} • {{ $itemGroup['uom'] ?: '-' }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">
                            Qty: {{ number_format((float)$itemGroup['total_qty'], 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left">Transfer ID</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-right">Quantity</th>
                                    <th class="px-4 py-3 text-left">UOM</th>
                                    <th class="px-4 py-3 text-left">From</th>
                                    <th class="px-4 py-3 text-left">To</th>
                                    <th class="px-4 py-3 text-left">Description</th>
                                    <th class="px-4 py-3 text-left">Created By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($itemGroup['rows'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ $row->transfer_id }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i:s') : '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->quantity, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2">{{ $row->uom }}</td>
                                        <td class="px-4 py-2">{{ $row->from_warehouse }}</td>
                                        <td class="px-4 py-2">{{ $row->to_warehouse }}</td>
                                        <td class="px-4 py-2">{{ $row->description ?: '-' }}</td>
                                        <td class="px-4 py-2">{{ $row->created_by ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="bg-gray-100 px-4 py-3 flex justify-end">
                <div class="text-right">
                    <div class="text-xs text-gray-500">Category Total Qty</div>
                    <div class="text-base font-bold text-gray-900">
                        {{ number_format((float)$categoryGroup['total_qty'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
            No transfer data found.
        </div>
    @endforelse
</div>
@endsection
