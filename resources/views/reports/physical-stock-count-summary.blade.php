@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Physical stock count summary grouped by warehouse and category.
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
                <label class="block text-xs text-gray-500">Warehouse</label>
                <input type="text" name="warehouse" value="{{ $warehouse }}" placeholder="Warehouse"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Category</label>
                <input type="text" name="category" value="{{ $category }}" placeholder="Category"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Item / code"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.physicalStockCountSummary') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>

            <a href="{{ route('reports.physicalStockCountSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Export CSV
            </a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Date Range</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
                -
                {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Grand Diff Cost</div>
            <div class="font-semibold text-gray-900">
                {{ number_format((float)($summary->grand_diff_cost ?? 0), 2, ',', '.') }}
            </div>
        </div>
    </div>

    @forelse($groupedRows as $warehouseGroup)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-100 px-4 py-3">
                <div class="font-semibold text-gray-900">{{ $warehouseGroup['warehouse'] }}</div>
            </div>

            @foreach($warehouseGroup['categories'] as $categoryGroup)
                <div class="border-b border-gray-200">
                    <div class="bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700">
                        {{ $categoryGroup['category'] }}
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left">Item Name</th>
                                    <th class="px-4 py-3 text-left">Item Code</th>
                                    <th class="px-4 py-3 text-left">UOM</th>
                                    <th class="px-4 py-3 text-right">Calculated</th>
                                    <th class="px-4 py-3 text-right">Actual</th>
                                    <th class="px-4 py-3 text-right">Diff</th>
                                    <th class="px-4 py-3 text-right">Variance</th>
                                    <th class="px-4 py-3 text-right">Diff Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($categoryGroup['items'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ $row->item_name }}</td>
                                        <td class="px-4 py-2">{{ $row->item_code }}</td>
                                        <td class="px-4 py-2">{{ $row->uom }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->calculated, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->actual, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->diff, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->variances * 100, 2, ',', '.') }}%</td>
                                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->diff_cost, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-300 bg-gray-50">
                                    <td colspan="7" class="px-4 py-3 text-right font-semibold text-gray-900">
                                        TOTAL {{ $categoryGroup['category'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">
                                        {{ number_format((float)$categoryGroup['subtotal_diff_cost'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="bg-gray-100 px-4 py-3 flex justify-end">
                <div class="text-right">
                    <div class="text-xs text-gray-500">Warehouse Total</div>
                    <div class="text-base font-bold text-gray-900">
                        {{ number_format((float)$warehouseGroup['subtotal_diff_cost'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
            No physical stock count data found.
        </div>
    @endforelse
</div>
@endsection