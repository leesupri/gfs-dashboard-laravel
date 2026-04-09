@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Transfer detail report grouped by category and item.</p>
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
                <label class="block text-xs text-gray-500">From</label>
                <input type="text" name="from_warehouse" value="{{ $fromWarehouse }}" placeholder="From warehouse"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">To</label>
                <input type="text" name="to_warehouse" value="{{ $toWarehouse }}" placeholder="To warehouse"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Category</label>
                <input type="text" name="category" value="{{ $category }}" placeholder="Category"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Item</label>
                <input type="text" name="item" value="{{ $item }}" placeholder="Item"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Transfer ID</label>
                <input type="text" name="transfer_id" value="{{ $transferId }}" placeholder="Transfer ID"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Search"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.transferDetail') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>

            <a href="{{ route('reports.transferDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Export CSV
            </a>
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