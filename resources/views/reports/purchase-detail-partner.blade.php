@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">Purchase detail grouped by partner.</p>
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
                <label class="block text-xs text-gray-500">Partner</label>
                <input type="text" name="partner" value="{{ $partner ?? '' }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
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
            <a href="{{ route('reports.purchaseDetail', request()->query()) }}"
   class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
    Back to Detail
</a>

            <a href="{{ route('reports.purchaseDetailPartner', array_merge(request()->query(), ['export' => 'csv'])) }}"
   class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
    Export CSV
</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Grand Total</div>
            <div class="font-semibold text-gray-900">
                Rp {{ number_format((float)($summary->grand_total ?? 0), 2, ',', '.') }}
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Lines</div>
            <div class="font-semibold text-gray-900">{{ number_format($summary->total_lines ?? 0) }}</div>
        </div>
    </div>

    @forelse($groupedRows as $group)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-100 px-4 py-3">
                <div class="font-semibold text-gray-900">
                    {{ $group['partner'] }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">Item Name</th>
                            <th class="px-4 py-3 text-left">Item Code</th>
                            <th class="px-4 py-3 text-left">GR No.</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-right">Purc. Qty</th>
                            <th class="px-4 py-3 text-left">Purc. UOM</th>
                            <th class="px-4 py-3 text-right">Conversion</th>
                            <th class="px-4 py-3 text-right">Quantity</th>
                            <th class="px-4 py-3 text-left">UOM</th>
                            <th class="px-4 py-3 text-right">Unit Cost</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left">Warehouse</th>
                            <th class="px-4 py-3 text-left">CreateBy</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($group['items'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $row->Category }}</td>
                                <td class="px-4 py-2">{{ $row->ItemName }}</td>
                                <td class="px-4 py-2">{{ $row->ItemCode }}</td>
                                <td class="px-4 py-2">{{ $row->id }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-right">{{ number_format((float) $row->purchaseQuantity, 2, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $row->purchaseUom }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float) $row->purchaseConversion, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float) $row->quantity, 2, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $row->uom }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float) $row->unitCost, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float) $row->total, 2, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $row->Warehouse }}</td>
                                <td class="px-4 py-2">{{ $row->CreateBy }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-300 bg-gray-50">
                            <td colspan="11" class="px-4 py-3 text-right font-semibold text-gray-900">
                                TOTAL {{ $group['partner'] }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">
                                {{ number_format((float) $group['subtotal'], 2, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
            No data found.
        </div>
    @endforelse

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="px-4 py-4 flex justify-end">
            <div class="text-right">
                <div class="text-sm text-gray-500">Grand Total</div>
                <div class="text-lg font-bold text-gray-900">
                    Rp {{ number_format((float)($summary->grand_total ?? 0), 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection