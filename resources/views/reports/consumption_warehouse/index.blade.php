@extends('layouts.app')

@section('content')
  <div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-4">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-lg font-semibold">{{ $title }}</h1>
        <p class="text-xs text-gray-500">Filter by date range</p>
      </div>

      <div class="flex items-center gap-2">
        <a
          class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          href="{{ route('reports.consumptionWarehouse.export', request()->query()) }}"
        >
          Export CSV
        </a>
        <button type="button" @click="filtersOpen = !filtersOpen"
          class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
          :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
          </svg>
          <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
        </button>
      </div>
    </div>

    <div
      x-show="filtersOpen" x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
    >
      <form class="gfs-card p-5" method="GET" action="{{ route('reports.consumptionWarehouse') }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search item/warehouse</label>
            <input id="f-q" type="text" name="q" placeholder="Search item/warehouse" value="{{ $q }}"
              class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
              style="border-color:var(--card-border); color:var(--text-primary)">
          </div>

          <div>
            <label for="f-start" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Start</label>
            <input id="f-start" type="date" name="start" value="{{ $start }}"
              class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
              style="border-color:var(--card-border); color:var(--text-primary)">
          </div>

          <div>
            <label for="f-end" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">End</label>
            <input id="f-end" type="date" name="end" value="{{ $end }}"
              class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
              style="border-color:var(--card-border); color:var(--text-primary)">
          </div>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
          <a href="{{ route('reports.consumptionWarehouse') }}"
            class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="border-color:var(--card-border); color:var(--text-secondary)">
            Clear
          </a>
          <button type="submit"
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
        </div>
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
