@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Production output grouped by category, item, and warehouse</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.productionSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
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
    <form method="GET" action="{{ route('reports.productionSummary') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
          <label for="f-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="f-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="f-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-category" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <input id="f-category" type="text" name="category" value="{{ $category }}" placeholder="Category name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-warehouse" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Warehouse</label>
          <input id="f-warehouse" type="text" name="warehouse" value="{{ $warehouse }}" placeholder="Warehouse…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Item / code / UOM…"
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
            <a href="{{ route('reports.productionSummary', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.productionSummary') }}"
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
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Lines</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($summary->total_lines ?? 0) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Quantity</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format((float)($summary->total_quantity ?? 0), 2, ',', '.') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">This Page</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format($rows->count()) }}
          <span class="text-xs font-normal" style="color:var(--text-muted)">/ {{ number_format($rows->perPage()) }}</span>
        </p>
      </div>
    </div>
  </div>

  {{-- Content --}}
  @if($rows->isEmpty())
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No production data found for the selected period.</p>
      <a href="{{ route('reports.productionSummary') }}"
        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
        Clear filters
      </a>
    </div>

  @else
    @php $grouped = collect($rows->items())->groupBy('category'); @endphp

    @foreach($grouped as $groupCategory => $items)
      @php
        $catQty      = $items->sum('quantity');
        $warehouseCount = $items->pluck('warehouse')->filter()->unique()->count();
      @endphp

      <div class="gfs-card overflow-hidden">

        {{-- Category header --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4"
          style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
              style="background:var(--sidebar-bg)">
              <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $groupCategory ?: 'Uncategorized' }}</p>
              <p class="text-xs" style="color:var(--text-muted)">
                {{ $items->count() }} item(s)
                @if($warehouseCount > 0)
                  <span class="ml-1.5 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 font-medium" style="color:var(--text-muted)">
                    {{ $warehouseCount }} warehouse(s)
                  </span>
                @endif
              </p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Qty</p>
            <p class="text-base font-bold text-green-600 tabular-nums">{{ number_format($catQty, 2, ',', '.') }}</p>
          </div>
        </div>

        {{-- Items table --}}
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                <th class="px-5 py-2.5 text-left">Item</th>
                <th class="px-4 py-2.5 w-28 text-left">Code</th>
                <th class="px-4 py-2.5 w-28">Quantity</th>
                <th class="px-4 py-2.5 w-20 text-left">UOM</th>
                <th class="px-4 py-2.5 w-40 text-left">Warehouse</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color:var(--card-border)">
              @foreach($items as $row)
                <tr class="transition-colors hover:bg-gray-50/60">
                  <td class="px-5 py-2.5 font-medium" style="color:var(--text-primary)">{{ $row->item_name ?: '—' }}</td>
                  <td class="px-4 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $row->item_code ?: '—' }}</td>
                  <td class="px-4 py-2.5 text-right font-semibold tabular-nums" style="color:var(--text-primary)">
                    {{ number_format((float)$row->quantity, 2, ',', '.') }}
                  </td>
                  <td class="px-4 py-2.5">
                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                      {{ $row->uom ?: '—' }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-xs" style="color:var(--text-secondary)">{{ $row->warehouse ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:rgba(34,197,94,0.04); border-top:1px solid rgba(34,197,94,0.15)">
                <td colspan="2" class="px-5 py-2.5 text-xs font-semibold" style="color:var(--text-secondary)">
                  Subtotal — {{ $groupCategory ?: 'Uncategorized' }}
                </td>
                <td class="px-4 py-2.5 text-right font-bold tabular-nums text-green-600">
                  {{ number_format($catQty, 2, ',', '.') }}
                </td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    @endforeach

    {{-- Pagination --}}
    @if($rows->hasPages())
      <div class="gfs-card px-5 py-4" style="background:var(--content-bg)">
        {{ $rows->links() }}
      </div>
    @endif

  @endif

</div>
@endsection

