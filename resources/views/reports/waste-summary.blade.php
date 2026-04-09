@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-end">
        <h1 class="text-lg font-semibold">{{ $title }}</h1>

        <form method="GET" class="flex gap-2 items-end">
            <input type="date" name="start" value="{{ $start }}" class="border px-3 py-2 rounded">
            <input type="date" name="end" value="{{ $end }}" class="border px-3 py-2 rounded">

            <button class="bg-black text-white px-4 py-2 rounded">
                Apply
            </button>

            <a href="{{ route('reports.wasteSummary', array_merge(request()->query(), ['export'=>'csv'])) }}"
               class="bg-green-600 text-white px-4 py-2 rounded">
                Export
            </a>
        </form>
    </div>

    <!-- DATA -->
    @foreach($grouped as $group)
        <div class="border rounded-xl overflow-hidden">

            <!-- CATEGORY -->
            <div class="bg-gray-100 px-4 py-2 font-semibold">
                {{ $group['category'] }}
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2">Name</th>
                        <th class="text-left px-3 py-2">Code</th>
                        <th class="text-right px-3 py-2">Qty</th>
                        <th class="text-left px-3 py-2">UOM</th>
                        <th class="text-right px-3 py-2">Unit Cost</th>
                        <th class="text-right px-3 py-2">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($group['items'] as $row)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $row->name }}</td>
                            <td class="px-3 py-2">{{ $row->code }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->quantity,2) }}</td>
                            <td class="px-3 py-2">{{ $row->uom }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->unitCost,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->total,2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- CATEGORY TOTAL -->
            <div class="bg-gray-100 px-4 py-2 text-right font-bold">
                Category Total: {{ number_format($group['total'],2) }}
            </div>
        </div>
    @endforeach

    <!-- GRAND TOTAL -->
    <div class="bg-black text-white px-4 py-3 text-right font-bold text-lg">
        GRAND TOTAL: {{ number_format($grandTotal,2) }}
    </div>

</div>
@endsection