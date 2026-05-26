@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">

    <h1 class="text-xl font-bold mb-1">VOID REPORT</h1>
    <p class="text-sm text-gray-500 mb-4">
        From {{ $filters['start'] }} to {{ $filters['end'] }}
    </p>

    <form method="GET" action="{{ route('reports.void') }}" class="flex gap-2 items-end mb-6">
      <div>
        <label class="text-xs text-gray-600">Start</label>
        <input type="date" name="start" value="{{ $filters['start'] ?? now()->toDateString() }}" class="border rounded px-2 py-1">
      </div>

      <div>
        <label class="text-xs text-gray-600">End</label>
        <input type="date" name="end" value="{{ $filters['end'] ?? now()->toDateString() }}" class="border rounded px-2 py-1">
      </div>

      <button class="bg-black text-white rounded px-3 py-2 text-sm">Filter</button>
      <a href="{{ route('reports.void') }}" class="border rounded px-3 py-2 text-sm">Reset</a>
    </form>

    @forelse($groups as $employee => $group)

        <div class="bg-gray-100 font-semibold px-3 py-2 mt-6">
            {{ $employee }}
        </div>

        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-1">Invoice</th>
                    <th class="text-left p-1">Sale Date</th>
                    <th class="text-left p-1">Created At</th>
                    <th class="text-left p-1">Item</th>
                    <th class="text-right p-1">Qty</th>
                    <th class="text-right p-1">Unit Price</th>
                    <th class="text-left p-1">Void Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group['items'] as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-1">{{ str_pad($row->invoiceNo, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="p-1">{{ \Carbon\Carbon::parse($row->saleDate)->format('d/M/Y h:i A') }}</td>
                        <td class="p-1">{{ $row->createdAt ?? '-' }}</td>
                        <td class="p-1">{{ $row->itemName }}</td>
                        <td class="p-1 text-right">{{ number_format($row->quantity, 0) }}</td>
                        <td class="p-1 text-right">{{ number_format($row->unitPrice, 2) }}</td>
                        <td class="p-1">{{ $row->voidReason ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="font-semibold border-t">
                    <td colspan="4" class="p-1 text-right">TOTAL</td>
                    <td class="p-1 text-right">{{ number_format($group['total_qty'], 0) }}</td>
                    <td class="p-1 text-right">{{ number_format($group['total_value'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    @empty
        <p class="text-gray-500 mt-6">No void transactions found.</p>
    @endforelse

    <div class="mt-8 border-t pt-3 font-bold flex justify-end gap-8">
        <div>GRAND QTY: {{ number_format($grand['qty'], 0) }}</div>
        <div>GRAND VALUE: {{ number_format($grand['value'], 2) }}</div>
    </div>

</div>
@endsection