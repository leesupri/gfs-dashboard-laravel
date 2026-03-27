@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Purchase detail by invoice line.</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500">Start</label>
                <input type="date" name="start" value="{{ $start }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">End</label>
                <input type="date" name="end" value="{{ $end }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Category</label>
                <input type="text" name="category" value="{{ $category }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Apply</button>

            <a href="{{ route('reports.purchaseDetail') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>

            <a href="{{ route('reports.purchaseSummary', request()->query()) }}"
               class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Back to Summary
            </a>

            <a href="{{ route('reports.purchaseDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Export CSV
            </a>
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