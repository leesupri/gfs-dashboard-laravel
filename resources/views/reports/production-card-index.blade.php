@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Browse production cards before opening the production detail.
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
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Production ID / item / notes"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.productionCard.index') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Date Range</div>
            <div class="font-semibold text-gray-900">
                @if($start && $end)
                    {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
                @else
                    All Dates
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Cards</div>
            <div class="font-semibold text-gray-900">{{ number_format($summary->total_cards ?? 0) }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        @if($rows->isEmpty())
            <div class="p-6 text-sm text-gray-500">
                No production cards found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Production ID</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Warehouse</th>
                            <th class="px-4 py-3 text-left">Saved By</th>
                            <th class="px-4 py-3 text-right">Products</th>
                            <th class="px-4 py-3 text-right">Lines</th>
                            <th class="px-4 py-3 text-left">Notes</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900">
                                    {{ $row->id }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $row->warehouse ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
    {{ $row->savedBy ?: '-' }}
</td>
                                <td class="px-4 py-2 text-right">
                                    {{ number_format($row->total_products ?? 0) }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ number_format($row->total_lines ?? 0) }}
                                </td>
                                <td class="px-4 py-2">
                                    <div class="max-w-xs truncate" title="{{ $row->notes }}">
                                        {{ $row->notes ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('reports.productionCard.show', $row->id) }}"
                                       class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                                        Open Card
                                    </a>
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