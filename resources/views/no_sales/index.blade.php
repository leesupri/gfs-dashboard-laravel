@extends('layouts.app')

@section('content')
@php
  $totalCols = 8 + ($hasService ? 1 : 0) + ($hasTax ? 1 : 0);

  if (!function_exists('nsProfitColor')) {
    function nsProfitColor(float $pct): string {
      if ($pct <   0) return 'text-red-500';
      if ($pct <  20) return 'text-yellow-500';
      return 'text-green-600';
    }
  }
@endphp

<div
  x-data="{ filtersOpen: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">No Sales Item Report</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('noSales.export', request()->query()) }}"
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
    <form method="GET" action="{{ route('noSales.index') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label for="ns-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="ns-start" type="date" name="start"
            value="{{ $filters['start'] ?? $from->toDateString() }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="ns-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="ns-end" type="date" name="end"
            value="{{ $filters['end'] ?? $to->toDateString() }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div class="flex items-end gap-2">
          <button type="submit"
            class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
          <a href="{{ route('noSales.index') }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="color:var(--text-secondary)">
            Reset
          </a>
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
        @foreach([
          ['Today',      ['start' => now()->toDateString(),                              'end' => now()->toDateString()]],
          ['Yesterday',  ['start' => now()->subDay()->toDateString(),                     'end' => now()->subDay()->toDateString()]],
          ['This Month', ['start' => now()->startOfMonth()->toDateString(),               'end' => now()->endOfMonth()->toDateString()]],
          ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),   'end' => now()->subMonth()->endOfMonth()->toDateString()]],
        ] as [$ql, $qp])
          <a href="{{ route('noSales.index', $qp) }}"
            class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
            style="color:var(--text-secondary)">{{ $ql }}</a>
        @endforeach
      </div>
    </form>
  </div>

  {{-- KPI cards --}}
  @php
    $kpis = [
      ['label' => 'Total Qty',  'val' => number_format($totals->quantity, 0),           'icon_bg' => 'bg-blue-100',    'icon_color' => 'text-blue-600',    'val_color' => '',                                               'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
      ['label' => 'Net Sales',  'val' => number_format($totals->netSales, 0, ',', '.'), 'icon_bg' => 'bg-green-100',   'icon_color' => 'text-green-600',   'val_color' => '',                                               'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 13h.01M13 13h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
      ['label' => 'Discount',   'val' => number_format($totals->disc,     0, ',', '.'), 'icon_bg' => 'bg-red-100',     'icon_color' => 'text-red-500',     'val_color' => 'text-red-500',                                   'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 014-4z'],
      ['label' => 'Cost',       'val' => number_format($totals->cost,     0, ',', '.'), 'icon_bg' => 'bg-amber-100',   'icon_color' => 'text-amber-600',   'val_color' => '',                                               'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
      ['label' => 'Profit',     'val' => number_format($totals->profit,   0, ',', '.'), 'icon_bg' => 'bg-emerald-100', 'icon_color' => 'text-emerald-600', 'val_color' => ($totals->profit >= 0 ? 'text-green-600' : 'text-red-500'), 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
      ['label' => 'Profit %',   'val' => number_format($totals->profitPct,1).'%',       'icon_bg' => 'bg-purple-100',  'icon_color' => 'text-purple-600',  'val_color' => nsProfitColor($totals->profitPct),                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];
    if ($hasService) {
      $kpis[] = ['label' => 'Service', 'val' => number_format($totals->service, 0, ',', '.'), 'icon_bg' => 'bg-sky-100',  'icon_color' => 'text-sky-600',  'val_color' => '', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'];
    }
    if ($hasTax) {
      $kpis[] = ['label' => 'Tax',     'val' => number_format($totals->tax,     0, ',', '.'), 'icon_bg' => 'bg-teal-100', 'icon_color' => 'text-teal-600', 'val_color' => '', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z'];
    }
  @endphp

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    @foreach($kpis as $k)
      <div class="gfs-card flex items-center gap-3 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $k['icon_bg'] }}">
          <svg class="h-5 w-5 {{ $k['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">{{ $k['label'] }}</p>
          <p class="mt-0.5 text-base font-bold leading-none {{ $k['val_color'] }}"
            @if(!$k['val_color']) style="color:var(--text-primary)" @endif>
            {{ $k['val'] }}
          </p>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Hierarchical table --}}
  <div class="gfs-card overflow-hidden">
    @if($grouped->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No no-sales items found for this period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 text-left w-64">Description</th>
              <th class="px-3 py-3 w-16">Qty</th>
              <th class="px-3 py-3 w-28">Net Sales</th>
              <th class="px-3 py-3 w-24">Discount</th>
              <th class="px-3 py-3 w-28">Cost</th>
              <th class="px-3 py-3 w-28">Profit</th>
              <th class="px-3 py-3 w-20">Profit %</th>
              @if($hasService)<th class="px-3 py-3 w-24">Service</th>@endif
              @if($hasTax)<th class="px-3 py-3 w-24">Tax</th>@endif
              <th class="px-3 py-3 w-28">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grouped as $deptName => $categories)
              @php
                $flat        = $categories->flatten();
                $deptQty     = $flat->sum('quantity');
                $deptNet     = $flat->sum('netSales');
                $deptDisc    = $flat->sum('disc');
                $deptCost    = $flat->sum('cost');
                $deptProfit  = $flat->sum('profit');
                $deptService = $flat->sum('service');
                $deptTax     = $flat->sum('tax');
                $deptTotal   = $flat->sum('total');
                $deptNetAdj  = $deptNet - $deptDisc;
                $deptPct     = $deptNetAdj > 0 ? ($deptProfit / $deptNetAdj * 100) : 0;
              @endphp

              {{-- Department header --}}
              <tr style="background:var(--sidebar-bg)">
                <td colspan="{{ $totalCols }}" class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-green-400"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-white">{{ $deptName }}</span>
                  </div>
                </td>
              </tr>

              @foreach($categories as $catName => $items)
                @php
                  $catQty     = $items->sum('quantity');
                  $catNet     = $items->sum('netSales');
                  $catDisc    = $items->sum('disc');
                  $catCost    = $items->sum('cost');
                  $catProfit  = $items->sum('profit');
                  $catService = $items->sum('service');
                  $catTax     = $items->sum('tax');
                  $catTotal   = $items->sum('total');
                  $catNetAdj  = $catNet - $catDisc;
                  $catPct     = $catNetAdj > 0 ? ($catProfit / $catNetAdj * 100) : 0;
                @endphp

                {{-- Category sub-header --}}
                <tr style="background:var(--content-bg); border-left:3px solid #22c55e">
                  <td class="py-2 font-semibold text-xs" style="padding-left:1.5rem; color:var(--text-secondary)">
                    {{ $catName }}
                  </td>
                  <td colspan="{{ $totalCols - 1 }}"></td>
                </tr>

                {{-- Item rows --}}
                @foreach($items as $item)
                  <tr class="transition-colors hover:bg-gray-50/60" style="border-bottom:1px solid var(--card-border)">
                    <td class="px-4 py-2.5 font-medium" style="color:var(--text-primary); padding-left:2rem">{{ $item->description }}</td>
                    <td class="px-3 py-2.5 text-right" style="color:var(--text-secondary)">{{ number_format($item->quantity, 0) }}</td>
                    <td class="px-3 py-2.5 text-right" style="color:var(--text-primary)">{{ number_format($item->netSales, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right {{ $item->disc > 0 ? 'text-red-500' : '' }}"
                      @if($item->disc <= 0) style="color:var(--text-muted)" @endif>
                      {{ $item->disc > 0 ? number_format($item->disc, 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-3 py-2.5 text-right" style="color:var(--text-secondary)">{{ number_format($item->cost, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-medium {{ $item->profit >= 0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($item->profit, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-xs font-semibold {{ nsProfitColor($item->profitPct) }}">{{ number_format($item->profitPct, 1) }}%</td>
                    @if($hasService)<td class="px-3 py-2.5 text-right" style="color:var(--text-secondary)">{{ number_format($item->service, 0, ',', '.') }}</td>@endif
                    @if($hasTax)<td class="px-3 py-2.5 text-right" style="color:var(--text-secondary)">{{ number_format($item->tax, 0, ',', '.') }}</td>@endif
                    <td class="px-3 py-2.5 text-right font-semibold" style="color:var(--text-primary)">{{ number_format($item->total, 0, ',', '.') }}</td>
                  </tr>
                @endforeach

                {{-- Category TOTAL --}}
                <tr style="background:rgba(34,197,94,0.05); border-top:1px solid rgba(34,197,94,0.15); border-bottom:1px solid rgba(34,197,94,0.15)">
                  <td class="py-2 text-xs font-semibold" style="padding-left:1.5rem; color:var(--text-secondary)">TOTAL {{ $catName }}</td>
                  <td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-primary)">{{ number_format($catQty, 0) }}</td>
                  <td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-primary)">{{ number_format($catNet, 0, ',', '.') }}</td>
                  <td class="px-3 py-2 text-right text-xs font-bold {{ $catDisc > 0 ? 'text-red-500' : '' }}"
                    @if($catDisc <= 0) style="color:var(--text-muted)" @endif>
                    {{ $catDisc > 0 ? number_format($catDisc, 0, ',', '.') : '—' }}
                  </td>
                  <td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-secondary)">{{ number_format($catCost, 0, ',', '.') }}</td>
                  <td class="px-3 py-2 text-right text-xs font-bold {{ $catProfit >= 0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($catProfit, 0, ',', '.') }}</td>
                  <td class="px-3 py-2 text-right text-xs font-bold {{ nsProfitColor($catPct) }}">{{ number_format($catPct, 1) }}%</td>
                  @if($hasService)<td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-secondary)">{{ number_format($catService, 0, ',', '.') }}</td>@endif
                  @if($hasTax)<td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-secondary)">{{ number_format($catTax, 0, ',', '.') }}</td>@endif
                  <td class="px-3 py-2 text-right text-xs font-bold" style="color:var(--text-primary)">{{ number_format($catTotal, 0, ',', '.') }}</td>
                </tr>
              @endforeach

              {{-- Department TOTAL --}}
              <tr style="background:#1a1f2e; border-top:1px solid rgba(255,255,255,0.08)">
                <td class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider" style="color:rgba(255,255,255,0.75)">TOTAL {{ $deptName }}</td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.9)">{{ number_format($deptQty, 0) }}</td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.9)">{{ number_format($deptNet, 0, ',', '.') }}</td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:{{ $deptDisc > 0 ? '#f87171' : 'rgba(255,255,255,0.3)' }}">
                  {{ $deptDisc > 0 ? number_format($deptDisc, 0, ',', '.') : '—' }}
                </td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($deptCost, 0, ',', '.') }}</td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:{{ $deptProfit >= 0 ? '#4ade80' : '#f87171' }}">{{ number_format($deptProfit, 0, ',', '.') }}</td>
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:{{ $deptPct >= 20 ? '#4ade80' : ($deptPct >= 0 ? '#fbbf24' : '#f87171') }}">{{ number_format($deptPct, 1) }}%</td>
                @if($hasService)<td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($deptService, 0, ',', '.') }}</td>@endif
                @if($hasTax)<td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($deptTax, 0, ',', '.') }}</td>@endif
                <td class="px-3 py-2.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.9)">{{ number_format($deptTotal, 0, ',', '.') }}</td>
              </tr>
            @endforeach

            {{-- Grand Total --}}
            <tr style="background:var(--sidebar-bg); border-top:3px solid rgba(34,197,94,0.4)">
              <td class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-green-400">Grand Total</td>
              <td class="px-3 py-3 text-right text-sm font-bold text-green-400">{{ number_format($totals->quantity, 0) }}</td>
              <td class="px-3 py-3 text-right text-sm font-bold text-white">{{ number_format($totals->netSales, 0, ',', '.') }}</td>
              <td class="px-3 py-3 text-right text-sm font-bold {{ $totals->disc > 0 ? 'text-red-400' : '' }}"
                style="{{ $totals->disc <= 0 ? 'color:rgba(255,255,255,0.3)' : '' }}">
                {{ $totals->disc > 0 ? number_format($totals->disc, 0, ',', '.') : '—' }}
              </td>
              <td class="px-3 py-3 text-right text-sm font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($totals->cost, 0, ',', '.') }}</td>
              <td class="px-3 py-3 text-right text-sm font-bold {{ $totals->profit >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($totals->profit, 0, ',', '.') }}</td>
              <td class="px-3 py-3 text-right text-sm font-bold" style="color:{{ $totals->profitPct >= 20 ? '#4ade80' : ($totals->profitPct >= 0 ? '#fbbf24' : '#f87171') }}">{{ number_format($totals->profitPct, 1) }}%</td>
              @if($hasService)<td class="px-3 py-3 text-right text-sm font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($totals->service, 0, ',', '.') }}</td>@endif
              @if($hasTax)<td class="px-3 py-3 text-right text-sm font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($totals->tax, 0, ',', '.') }}</td>@endif
              <td class="px-3 py-3 text-right text-sm font-bold text-green-400">{{ number_format($totals->total, 0, ',', '.') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection
