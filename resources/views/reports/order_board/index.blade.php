@php
function deptBadge($dept) {
    $d = strtoupper(trim($dept ?? ''));
    if (str_contains($d, 'BEVERAGE')) return ['label' => 'BEVERAGE', 'class' => 'bg-blue-100 text-blue-700'];
    if (str_contains($d, 'FOOD'))     return ['label' => 'FOOD',     'class' => 'bg-red-100 text-red-700'];
    if (str_contains($d, 'BAR'))      return ['label' => 'BAR',      'class' => 'bg-purple-100 text-purple-700'];
    return ['label' => $d ?: 'OTHER', 'class' => 'bg-gray-100 text-gray-600'];
}
@endphp

@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Per-bill order board — station, category, table, source and closing info.</p>
    </div>
    <div class="flex items-center gap-2">
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

  {{-- Filter panel --}}
  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
  <form method="GET" class="gfs-card p-5">
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

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

      <div>
        <label for="f-invoice" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Invoice #</label>
        <input id="f-invoice" type="text" name="invoice" value="{{ $invoice }}" placeholder="e.g. 10042"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

      <div>
        <label for="f-table" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Table</label>
        <input id="f-table" type="text" name="table" value="{{ $table }}" placeholder="Table name"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

      <div>
        <label for="f-station" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Station</label>
        <input id="f-station" type="text" name="station" value="{{ $station }}" placeholder="e.g. POS-01"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

      <div>
        <label for="f-dept" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Department</label>
        <input id="f-dept" type="text" name="department" value="{{ $department }}" placeholder="e.g. FOOD"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

      <div>
        <label for="f-cat" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Category</label>
        <input id="f-cat" type="text" name="category" value="{{ $category }}" placeholder="e.g. MAIN COURSE"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

      <div>
        <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
        <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Item, user, customer…"
          class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
          style="border-color:var(--card-border); color:var(--text-primary)">
      </div>

    </div>

    <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
      <a href="{{ route('reports.orderBoard') }}"
        class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
        style="border-color:var(--card-border); color:var(--text-secondary)">Clear</a>
      <button type="submit"
        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
        Apply
      </button>
    </div>
  </form>
  </div>

  {{-- Results --}}
  @if($byBill->isEmpty())
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-16 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100">
        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium" style="color:var(--text-primary)">No orders found</p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Try adjusting your date range or filters.</p>
      </div>
    </div>
  @else
    <div class="space-y-5">
      @foreach($byBill as $bill)
        <div class="gfs-card overflow-hidden">

          {{-- Bill header --}}
          <div class="px-5 py-4" style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

              {{-- Left: identity + meta --}}
              <div class="min-w-0 space-y-3">

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-base font-semibold" style="color:var(--text-primary)">Invoice #{{ $bill->invoice_id }}</span>
                  @if($bill->salesType)
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                      {{ $bill->salesType }}
                    </span>
                  @endif
                  <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    {{ $bill->tableName ?: 'No table' }}
                  </span>
                  <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                    {{ $bill->item_count }} {{ $bill->item_count == 1 ? 'item' : 'items' }}
                  </span>
                </div>

                {{-- Meta key-value grid --}}
                @php
                  $meta = [
                    'Opened'        => $bill->date        ? \Carbon\Carbon::parse($bill->date)->format('d/m/Y H:i')        : '-',
                    'Order station' => $bill->order_station  ?: '-',
                    'Ordered by'    => $bill->ordered_by      ?: '-',
                    'Ordered at'    => $bill->ordered_at   ? \Carbon\Carbon::parse($bill->ordered_at)->format('d/m/Y H:i')  : '-',
                    'Closed by'     => $bill->closed_by       ?: '-',
                    'Closed at'     => $bill->closed_time  ? \Carbon\Carbon::parse($bill->closed_time)->format('d/m/Y H:i') : '-',
                    'Close station' => $bill->close_station   ?: '-',
                    'Customer'      => $bill->customer         ?: '-',
                  ];
                @endphp
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 sm:grid-cols-3 xl:grid-cols-4">
                  @foreach($meta as $label => $value)
                    <div>
                      <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">{{ $label }}</p>
                      <p class="mt-0.5 text-xs" style="color:var(--text-secondary)">{{ $value }}</p>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- Right: financial summary --}}
              <div class="flex shrink-0 flex-col gap-2 lg:items-end lg:text-right">
                <div>
                  <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Net Total</p>
                  <p class="mt-0.5 text-2xl font-semibold text-green-600">Rp {{ number_format($bill->net, 0, ',', '.') }}</p>
                </div>
                <div class="flex items-center gap-4 lg:justify-end">
                  <div class="lg:text-right">
                    <p class="text-[10px]" style="color:var(--text-muted)">Gross</p>
                    <p class="text-sm font-medium" style="color:var(--text-secondary)">Rp {{ number_format($bill->gross, 0, ',', '.') }}</p>
                  </div>
                  @if($bill->discount > 0)
                    <div class="lg:text-right">
                      <p class="text-[10px]" style="color:var(--text-muted)">Discount</p>
                      <p class="text-sm font-medium text-red-500">Rp {{ number_format($bill->discount, 0, ',', '.') }}</p>
                    </div>
                  @endif
                </div>
              </div>

            </div>
          </div>

          {{-- Items table --}}
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm" style="min-width:860px">
              <thead>
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide"
                  style="color:var(--text-muted); background:var(--content-bg); border-bottom:1px solid var(--card-border)">
                  <th class="px-4 py-3 w-32">Created</th>
                  <th class="px-4 py-3">Item</th>
                  <th class="px-4 py-3 text-right w-14">Qty</th>
                  <th class="px-4 py-3 text-right w-28">Unit Price</th>
                  <th class="px-4 py-3 text-right w-28">Amount</th>
                  <th class="px-4 py-3 w-36">Category</th>
                  <th class="px-4 py-3 w-32">Department</th>
                  <th class="px-4 py-3 w-28">User</th>
                  <th class="px-4 py-3 w-36">Source</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($bill->lines as $line)
                  @php $dept = deptBadge($line->department); @endphp
                  <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-2.5 whitespace-nowrap text-xs" style="color:var(--text-secondary)">
                      {{ $line->created ? \Carbon\Carbon::parse($line->created)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-2.5 font-medium" style="color:var(--text-primary)">
                      {{ $line->description ?: '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-sm" style="color:var(--text-secondary)">
                      {{ number_format((float)$line->quantity, 0) }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-sm" style="color:var(--text-secondary)">
                      Rp {{ number_format((float)$line->unitPrice, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-semibold text-sm" style="color:var(--text-primary)">
                      Rp {{ number_format(((float)$line->quantity * (float)$line->unitPrice) - (float)($line->discountAmount ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-xs" style="color:var(--text-secondary)">
                      {{ $line->category ?: '-' }}
                    </td>
                    <td class="px-4 py-2.5">
                      <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $dept['class'] }}">
                        {{ $dept['label'] }}
                      </span>
                    </td>
                    <td class="px-4 py-2.5 text-xs" style="color:var(--text-secondary)">
                      {{ $line->employee ?: '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-xs" style="color:var(--text-secondary)">
                      {{ $line->createdAtHo ?: '-' }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
