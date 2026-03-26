@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Market list by category, UOM, recipe UOM, and average cost.
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
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Item / code / barcode / UOM"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.marketList') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
            <a href="{{ route('reports.marketList', array_merge(request()->query(), ['export' => 'csv'])) }}"
   class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
    Export CSV
</a>
        </form>
        
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Date Range</div>
            <div class="font-semibold text-gray-900">
    @if($start && $end)
        {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
        -
        {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
    @else
        All Dates
    @endif
</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Items</div>
            <div class="font-semibold text-gray-900">
                {{ number_format($summary->total_items ?? 0) }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Average Cost</div>
            <div class="font-semibold text-gray-900">
                Rp {{ number_format((float)($summary->average_cost_mean ?? 0), 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        @if($rows->isEmpty())
            <div class="p-6 text-sm text-gray-500">
                No market list data found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">Item Code</th>
                            <th class="px-4 py-3 text-left">Item</th>
                            <th class="px-4 py-3 text-left">Purchase UOM</th>
                            <th class="px-4 py-3 text-left">Inventory UOM</th>
                            <th class="px-4 py-3 text-left">Recipe UOM</th>
                            <th class="px-4 py-3 text-right">Pur→Inv</th>
                            <th class="px-4 py-3 text-right">Inv→Recipe</th>
                            <th class="px-4 py-3 text-right">Purchase Price</th>
                            <th class="px-4 py-3 text-right">Avg Cost</th>
                            <th class="px-4 py-3 text-left">Saved</th>
                            <th class="px-4 py-3 text-left">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <div class="font-medium text-gray-900">{{ $row->category ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->category_code ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $row->item_code ?: '-' }}</td>
                                <td class="px-4 py-2">
                                    <div class="font-medium text-gray-900">{{ $row->item_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Barcode: {{ $row->barcode ?: '-' }} | PLU: {{ $row->plu ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2">{{ $row->purchase_uom ?: '-' }}</td>
                                <td class="px-4 py-2">{{ $row->inventory_uom ?: '-' }}</td>
                                <td class="px-4 py-2">{{ $row->recipe_uom ?: '-' }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float)$row->purchase_to_inventory_conversion, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format((float)$row->inventory_to_recipe_conversion, 2) }}</td>
                                <td class="px-4 py-2 text-right">Rp {{ number_format((float)$row->purchase_price, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">Rp {{ number_format((float)$row->average_cost, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->saved ? \Carbon\Carbon::parse($row->saved)->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->updated ? \Carbon\Carbon::parse($row->updated)->format('d/m/Y H:i:s') : '-' }}
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