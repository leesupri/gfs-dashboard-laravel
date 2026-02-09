@extends('layouts.app')

@section('content')
  <div class="space-y-4">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-lg font-semibold">{{ $title }}</h1>
        <p class="text-xs text-gray-500">Filter by date range</p>
      </div>

      <form class="flex flex-wrap items-end gap-2" method="GET" action="{{ route('reports.consumptionWarehouse') }}">
        <div>
          <label class="block text-xs text-gray-500">Search item/warehouse</label>
          <input type="text" name="q" placeholder="Search item/warehouse" value="{{ $q }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        </div>
        
        
        <div>
          <label class="block text-xs text-gray-500">Start</label>
          <input type="date" name="start" value="{{ $start }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500">End</label>
          <input type="date" name="end" value="{{ $end }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        </div>


        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
          Apply
        </button>
        <a href="{{ route('reports.consumptionWarehouse') }}"
     class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
    Clear
  </a>

        <a
          class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          href="{{ route('reports.consumptionWarehouse.export', request()->query()) }}"
        >
          Export CSV
        </a>
      </form>
    </div>

    @if($byWarehouse->isEmpty())
      <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
        No data for selected dates.
      </div>
    @else

      @foreach($byWarehouse as $warehouseName => $items)
        @php $subTotal = $items->sum('totalCost'); @endphp

        <div class="rounded-xl border border-gray-200 bg-white">
          <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <div class="text-sm font-semibold">{{ $warehouseName }}</div>
            <div class="text-sm font-semibold">Subtotal: {{ number_format($subTotal, 2) }}</div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-xs text-gray-600">
                <tr>
                  <th class="px-4 py-2 text-left">Item</th>
                  <th class="px-4 py-2 text-right">Quantity</th>
                  <th class="px-4 py-2 text-left">UOM</th>
                  <th class="px-4 py-2 text-right">Total Cost</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($items as $r)
                  <tr>
                    <td class="px-4 py-2">{{ $r->item }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)$r->quantity, 2) }}</td>
                    <td class="px-4 py-2">{{ $r->uom }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)$r->totalCost, 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endforeach

      <div class="flex justify-end">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold">
          Grand Total: {{ number_format($grandTotal, 2) }}
        </div>
      </div>

    @endif
  </div>
@endsection
