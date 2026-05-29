@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        Stock movements between warehouses, grouped by category and item
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.transferDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export CSV
      </a>
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('reports.transferDetail') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Row 1: date + warehouse route --}}
        <div>
          <label for="f-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From Date</label>
          <input id="f-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To Date</label>
          <input id="f-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-from" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From Warehouse</label>
          <input id="f-from" type="text" name="from_warehouse" value="{{ $fromWarehouse }}" placeholder="Origin warehouse…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-to" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To Warehouse</label>
          <input id="f-to" type="text" name="to_warehouse" value="{{ $toWarehouse }}" placeholder="Destination warehouse…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        {{-- Row 2: item filters + transfer ID + search --}}
        <div>
          <label for="f-category" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <input id="f-category" type="text" name="category" value="{{ $category }}" placeholder="Category name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-item" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Item</label>
          <input id="f-item" type="text" name="item" value="{{ $item }}" placeholder="Item name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-transfer-id" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Transfer ID</label>
          <input id="f-transfer-id" type="text" name="transfer_id" value="{{ $transferId }}" placeholder="Transfer #"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Item / warehouse / description…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
        <div class="flex flex-wrap gap-2">
          <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
          @foreach([
            ['Today',      ['start' => now()->toDateString(),                             'end' => now()->toDateString()]],
            ['Yesterday',  ['start' => now()->subDay()->toDateString(),                    'end' => now()->subDay()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),              'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),  'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.transferDetail', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.transferDetail') }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="color:var(--text-secondary)">Clear</a>
          <button type="submit"
            class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- KPI strip --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Period</p>
        <p class="mt-0.5 text-xs font-bold leading-snug" style="color:var(--text-primary)">
          {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Transfers</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($summary->total_transfers ?? 0) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Lines</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($summary->total_lines ?? 0) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 13h.01M13 13h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Qty</p>
        <p class="mt-0.5 text-base font-bold leading-none tabular-nums" style="color:var(--text-primary)">
          {{ number_format((float)($summary->total_quantity ?? 0), 2, ',', '.') }}
        </p>
      </div>
    </div>
  </div>

  {{-- Truncation warning --}}
  @if(!empty($truncated))
    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
      <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <div>
        <p class="text-sm font-semibold text-amber-800">Result limit reached (2,000 rows)</p>
        <p class="mt-0.5 text-xs text-amber-700">
          Please narrow the date range, warehouse, category, or item filter to see complete data.
        </p>
      </div>
    </div>
  @endif

  {{-- Category groups --}}
  @forelse($groupedRows as $categoryGroup)
    @php
      $catQty      = (float)$categoryGroup['total_qty'];
      $catItemCount = count($categoryGroup['items']);
    @endphp

    <div class="gfs-card overflow-hidden">

      {{-- Category header --}}
      <div class="flex items-center justify-between gap-4 px-5 py-4"
        style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
            style="background:var(--sidebar-bg)">
            <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 014-4z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $categoryGroup['category'] }}</p>
            <p class="text-xs" style="color:var(--text-muted)">{{ $catItemCount }} item(s)</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Qty</p>
          <p class="text-base font-bold tabular-nums text-green-600">{{ number_format($catQty, 2, ',', '.') }}</p>
        </div>
      </div>

      {{-- Item groups --}}
      @foreach($categoryGroup['items'] as $itemGroup)
        @php $itemQty = (float)$itemGroup['total_qty']; @endphp

        {{-- Item sub-header --}}
        <div class="flex items-center justify-between px-5 py-2.5"
          style="background:var(--content-bg); border-left:3px solid #22c55e; border-bottom:1px solid var(--card-border)">
          <div>
            <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $itemGroup['item_name'] }}</p>
            <p class="text-[10px]" style="color:var(--text-muted)">
              <span class="font-mono">{{ $itemGroup['item_code'] ?: '—' }}</span>
              @if($itemGroup['uom'])
                <span class="mx-1">·</span>
                <span class="rounded-full bg-gray-100 px-1.5 py-0.5 font-medium">{{ $itemGroup['uom'] }}</span>
              @endif
            </p>
          </div>
          <span class="text-xs font-bold tabular-nums text-green-600">
            Qty: {{ number_format($itemQty, 2, ',', '.') }}
          </span>
        </div>

        {{-- Transfer rows --}}
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                <th class="px-4 py-2 text-left w-24">ID</th>
                <th class="px-4 py-2 text-left w-32">Date</th>
                <th class="px-4 py-2 w-20">Qty</th>
                <th class="px-4 py-2 text-left">Route</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-left w-32">Created By</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color:var(--card-border)">
              @foreach($itemGroup['rows'] as $row)
                <tr class="transition-colors hover:bg-gray-50/60">

                  {{-- Transfer ID --}}
                  <td class="px-4 py-2.5">
                    <span class="font-mono text-xs font-semibold" style="color:var(--text-primary)">#{{ $row->transfer_id }}</span>
                  </td>

                  {{-- Date --}}
                  <td class="px-4 py-2.5 whitespace-nowrap">
                    @if($row->date)
                      <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                        {{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}
                      </p>
                      <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                        {{ \Carbon\Carbon::parse($row->date)->format('H:i') }}
                      </p>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>

                  {{-- Quantity --}}
                  <td class="px-4 py-2.5 text-right">
                    <span class="text-sm tabular-nums font-semibold" style="color:var(--text-primary)">
                      {{ number_format((float)$row->quantity, 2, ',', '.') }}
                    </span>
                  </td>

                  {{-- Route: From → To --}}
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 whitespace-nowrap">
                        {{ $row->from_warehouse ?: '—' }}
                      </span>
                      <svg class="h-3 w-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                      </svg>
                      <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 whitespace-nowrap">
                        {{ $row->to_warehouse ?: '—' }}
                      </span>
                    </div>
                  </td>

                  {{-- Description --}}
                  <td class="px-4 py-2.5 max-w-xs">
                    @if($row->description)
                      <p class="truncate text-xs" style="color:var(--text-secondary)" title="{{ $row->description }}">
                        {{ $row->description }}
                      </p>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>

                  {{-- Created By --}}
                  <td class="px-4 py-2.5">
                    @if($row->created_by)
                      <div class="flex items-center gap-1.5">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white"
                          style="background:var(--sidebar-bg)">
                          {{ strtoupper(substr($row->created_by, 0, 1)) }}
                        </div>
                        <span class="text-xs" style="color:var(--text-secondary)">{{ $row->created_by }}</span>
                      </div>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>

                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach

      {{-- Category footer --}}
      <div class="flex items-center justify-between border-t px-5 py-3"
        style="border-color:var(--card-border); background:var(--content-bg)">
        <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-secondary)">
          Total — {{ $categoryGroup['category'] }}
        </span>
        <span class="text-sm font-bold tabular-nums text-green-600">
          {{ number_format($catQty, 2, ',', '.') }}
        </span>
      </div>

    </div>
  @empty
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No transfer data found for the selected filters.</p>
      <a href="{{ route('reports.transferDetail') }}"
        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
        Clear filters
      </a>
    </div>
  @endforelse

</div>
@endsection
