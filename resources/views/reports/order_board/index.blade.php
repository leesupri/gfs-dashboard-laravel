@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Per bill order board with station, category, table, order source, and closing info.</p>
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
                <label class="block text-xs text-gray-500">Invoice</label>
                <input type="text" name="invoice" value="{{ $invoice }}" placeholder="Invoice #"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Table</label>
                <input type="text" name="table" value="{{ $table }}" placeholder="Table"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Station</label>
                <input type="text" name="station" value="{{ $station }}" placeholder="createdAtHo / closedAt"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Department</label>
                <input type="text" name="department" value="{{ $department }}" placeholder="Department"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Category</label>
                <input type="text" name="category" value="{{ $category }}" placeholder="Category"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Item / user / customer"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.orderBoard') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        </form>
    </div>

    @if($byBill->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
            No order data found.
        </div>
    @else
        @foreach($byBill as $bill)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-base font-semibold text-gray-900">Invoice #{{ $bill->invoice_id }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {{ $bill->salesType ?: '-' }}
                                </span>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                    Table: {{ $bill->tableName ?: '-' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-1 text-sm text-gray-600 sm:grid-cols-2 xl:grid-cols-3">
                                <div><span class="font-medium text-gray-800">Order date:</span> {{ $bill->date ? \Carbon\Carbon::parse($bill->date)->format('d/m/Y H:i:s') : '-' }}</div>
                                <div><span class="font-medium text-gray-800">Ordered by:</span> {{ $bill->ordered_by ?: '-' }}</div>
                                <div><span class="font-medium text-gray-800">Ordered at:</span> {{ $bill->ordered_at ? \Carbon\Carbon::parse($bill->ordered_at)->format('d/m/Y H:i:s') : '-' }}</div>
                                <div><span class="font-medium text-gray-800">Order station:</span> {{ $bill->order_station ?: '-' }}</div>
                                <div><span class="font-medium text-gray-800">Closed by:</span> {{ $bill->closed_by ?: '-' }}</div>
                                <div><span class="font-medium text-gray-800">Closed time:</span> {{ $bill->closed_time ? \Carbon\Carbon::parse($bill->closed_time)->format('d/m/Y H:i:s') : '-' }}</div>
                                <div><span class="font-medium text-gray-800">Close station:</span> {{ $bill->close_station ?: '-' }}</div>
                                <div><span class="font-medium text-gray-800">Customer:</span> {{ $bill->customer ?: '-' }}</div>
                                <div><span class="font-medium text-gray-800">Items:</span> {{ $bill->item_count }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-sm min-w-[280px]">
                            <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-center">
                                <div class="text-xs text-gray-500">Gross</div>
                                <div class="font-semibold text-gray-900">Rp {{ number_format($bill->gross, 0, ',', '.') }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-center">
                                <div class="text-xs text-gray-500">Discount</div>
                                <div class="font-semibold text-gray-900">Rp {{ number_format($bill->discount, 0, ',', '.') }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-center">
                                <div class="text-xs text-gray-500">Net</div>
                                <div class="font-semibold text-gray-900">Rp {{ number_format($bill->net, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left">Created</th>
                                <th class="px-4 py-3 text-left">Item</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Price</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-left">Category</th>
                                <th class="px-4 py-3 text-left">Department / Station</th>
                                <th class="px-4 py-3 text-left">User</th>
                                <th class="px-4 py-3 text-left">Source Device</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($bill->lines as $line)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $line->created ? \Carbon\Carbon::parse($line->created)->format('d/m/Y H:i:s') : '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-900 font-medium">
                                        {{ $line->description ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format((float)$line->quantity, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        Rp {{ number_format((float)$line->unitPrice, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-medium">
                                        Rp {{ number_format(((float)$line->quantity * (float)$line->unitPrice) - (float)($line->discountAmount ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $line->category ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <div>{{ $line->department ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $line->employee ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $line->createdAtHo ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection