@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Daily Category Sales</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.dailyCategory', array_merge(request()->query(), ['export' => 'csv'])) }}"
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

  {{-- Filters --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('reports.dailyCategory') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
            <a href="{{ route('reports.dailyCategory', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex gap-2">
          <a href="{{ route('reports.dailyCategory') }}"
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

  {{-- KPIs --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Total Amount</p>
        <p class="mt-0.5 text-base font-bold text-green-400 tabular-nums">{{ number_format($totalAmount, 0, ',', '.') }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Qty</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($totalQty, 0) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Categories</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums" style="color:var(--text-primary)">{{ $rows->count() }}</p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <p class="text-sm font-medium" style="color:var(--text-muted)">No data for the selected period.</p>
      </div>
    @else
      @php $grandAmount = $rows->sum('amount'); @endphp
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-5 py-3 text-left">Category</th>
              <th class="px-4 py-3 w-28">Qty</th>
              <th class="px-4 py-3 w-36">Amount</th>
              <th class="px-4 py-3 w-24">Share</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              @php $pct = $grandAmount > 0 ? ($row->amount / $grandAmount * 100) : 0; @endphp
              <tr class="transition-colors hover:bg-gray-50/60">
                <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ $row->category }}</td>
                <td class="px-4 py-3 text-right tabular-nums" style="color:var(--text-secondary)">{{ number_format($row->qty, 0) }}</td>
                <td class="px-4 py-3 text-right tabular-nums font-semibold" style="color:var(--text-primary)">{{ number_format($row->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
                      <div class="h-full rounded-full bg-green-500" style="width:{{ number_format($pct, 1) }}%"></div>
                    </div>
                    <span class="text-[10px] tabular-nums font-medium" style="color:var(--text-muted)">{{ number_format($pct, 1) }}%</span>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:var(--sidebar-bg); border-top:2px solid rgba(34,197,94,0.3)">
              <td class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-green-400">Total</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-white">{{ number_format($totalQty, 0) }}</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-green-400">{{ number_format($totalAmount, 0, ',', '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection
