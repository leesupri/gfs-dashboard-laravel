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
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.purchaseDetail', request()->query()) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Open Detail
      </a>
      <a href="{{ route('reports.purchaseSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
    <form method="GET" action="{{ route('reports.purchaseSummary') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <a href="{{ route('reports.purchaseSummary', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.purchaseSummary') }}"
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
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Grand Total</p>
        <p class="mt-0.5 text-base font-bold leading-none text-green-400">
          {{ number_format((float)($summary->grand_total ?? 0), 0, ',', '.') }}
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
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Rows</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($rows->total()) }}</p>
      </div>
    </div>
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
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">This Page</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format($rows->count()) }}<span class="text-xs font-normal" style="color:var(--text-muted)"> / {{ $rows->perPage() }}</span>
        </p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No purchase data found for the selected period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 text-left w-28">Code</th>
              <th class="px-4 py-3 text-left">Item</th>
              <th class="px-4 py-3 w-28">Quantity</th>
              <th class="px-4 py-3 w-20 text-left">UOM</th>
              <th class="px-4 py-3 w-36">Total Cost</th>
            </tr>
          </thead>
          <tbody>
            @php $prevCategory = null; $catTotal = 0; $catQty = 0; @endphp

            @foreach($rows as $row)
              @php $isNewCat = $row->category !== $prevCategory; @endphp

              @if($isNewCat)
                {{-- Category header --}}
                <tr style="background:var(--sidebar-bg)">
                  <td colspan="5" class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      <span class="h-2 w-2 rounded-full bg-green-400"></span>
                      <span class="text-xs font-bold uppercase tracking-widest text-white">
                        {{ $row->category ?: 'Uncategorized' }}
                      </span>
                    </div>
                  </td>
                </tr>
                @php $prevCategory = $row->category; @endphp
              @endif

              <tr class="transition-colors hover:bg-gray-50/60" style="border-bottom:1px solid var(--card-border)">
                <td class="px-4 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $row->code ?: '—' }}</td>
                <td class="px-4 py-2.5 font-medium" style="color:var(--text-primary)">{{ $row->name ?: '—' }}</td>
                <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                  {{ number_format((float)$row->quantity, 2, ',', '.') }}
                </td>
                <td class="px-4 py-2.5">
                  <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                    {{ $row->uom ?: '—' }}
                  </span>
                </td>
                <td class="px-4 py-2.5 text-right tabular-nums font-semibold" style="color:var(--text-primary)">
                  {{ number_format((float)$row->totalCost, 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($rows->hasPages())
        <div class="border-t px-5 py-4" style="border-color:var(--card-border); background:var(--content-bg)">
          {{ $rows->links() }}
        </div>
      @endif
    @endif
  </div>

</div>
@endsection

