@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Header row --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-lg font-semibold">{{ $title }}</h1>
      <p class="text-xs text-gray-500">Filter by date / invoice / item / warehouse</p>
    </div>

    <div class="flex flex-wrap items-end gap-2">
      <a
        href="{{ route('reports.consumptionDetailInvoice.export', request()->query()) }}"
        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Export CSV
      </a>

      <form method="GET" class="flex flex-wrap gap-2 items-end">
        <input type="date" name="start" value="{{ $start }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="date" name="end" value="{{ $end }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">

        <input type="text" name="invoice" placeholder="Invoice #" value="{{ $invoice }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="text" name="item" placeholder="Item" value="{{ $item }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="text" name="warehouse" placeholder="Warehouse" value="{{ $warehouse }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">

        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
          Apply
        </button>

        <a
          href="{{ route('reports.consumptionDetailInvoice') }}"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Clear
        </a>
      </form>
    </div>
  </div>

  {{-- Content --}}
  @forelse($byInvoice as $invoiceId => $items)
    @php
      $invoiceTotal = $items->sum('totalCost');
      $date = optional($items->first())->date;
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="border-b px-4 py-3 flex items-center justify-between bg-gray-50">
       @if(!empty($invoiceId))
      <a
        href="{{ route('sales.receipt', ['invoice_id' => $invoiceId]) }}"
        target="_blank"
        class="hover:underline"
      >
        Invoice #{{ $invoiceId }}
      </a>
    @else
      <span class="text-gray-400">Invoice #N/A</span>
    @endif

    <span class="ml-2 text-xs text-gray-500">
      {{ optional($date)->format('d M Y') }}
    </span>
  

        <div class="font-semibold text-gray-900">
          Total: {{ number_format($invoiceTotal, 2) }}
        </div>
      </div>

      @foreach($items->groupBy('resultDescription') as $desc => $lines)
        @php $sub = $lines->sum('totalCost'); @endphp

        <div class="px-4 py-2 flex justify-between items-center bg-gray-100 border-t text-sm font-semibold">
          <div>
            {{ $desc }}
            <span class="text-xs text-gray-500">
              ({{ $lines->first()->resultQuantity }})
            </span>
          </div>
          <div>{{ number_format($sub, 2) }}</div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-600">
              <tr>
                <th class="px-4 py-2 text-left">Item</th>
                <th class="px-4 py-2 text-right">Qty</th>
                <th class="px-4 py-2 text-left">UOM</th>
                <th class="px-4 py-2 text-right">Total Cost</th>
                <th class="px-4 py-2 text-left">Warehouse</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($lines as $r)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-2">{{ $r->item }}</td>
                  <td class="px-4 py-2 text-right">{{ number_format($r->quantity, 2) }}</td>
                  <td class="px-4 py-2">{{ $r->uom }}</td>
                  <td class="px-4 py-2 text-right">{{ number_format($r->totalCost, 2) }}</td>
                  <td class="px-4 py-2">{{ $r->warehouse }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach
    </div>

  @empty
    <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
      No data for selected filters.
    </div>
  @endforelse

  {{-- Grand total --}}
  <div class="flex justify-end">
    <div class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold shadow-sm">
      Grand Total: {{ number_format($grandTotal, 2) }}
    </div>
  </div>

</div>
@endsection
